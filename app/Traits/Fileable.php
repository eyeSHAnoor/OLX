<?php

namespace App\Traits;

use App\Models\File;
use ReflectionClass;
use Illuminate\Support\Facades\Log;

trait Fileable
{
    protected static function bootFileable()
    {
        static::deleting(function ($model) {
            $model->files->each->delete();
        });
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function file()
    {
        return $this->morphOne(File::class, 'fileable')
            ->select(['id', 'file_location', 'fileable_id', 'fileable_type']);
    }

    public function addFiles($input = 'images', $fileName = null, $collection = 'files', $meta = [])
    {
        // Case 1: Manually passed file
        if ($input instanceof \Illuminate\Http\UploadedFile) {
            if (!$input->isValid()) {
                return;
            }

            try {
                $originalName = pathinfo($input->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $input->getClientOriginalExtension();
                $datetime = now()->format('Ymd_His');
                $filename = "{$originalName}_{$datetime}.{$extension}";

                $file = new File([
                    'file_name' => $fileName ?? $originalName,
                    'file_location' => $input->storeAs(
                        (new \ReflectionClass($this))->getShortName() . "/files",
                        $filename,
                        'public'
                    ),
                    'collection' => $collection,
                    'meta' => $meta,
                ]);

                $this->files()->save($file);
            } catch (\Exception $e) {
                \Log::error('File upload failed: ' . $e->getMessage());
                throw new \Exception('Failed to upload file');
            }

            return;
        }

        // Case 2: Request-based file upload (original behavior)
        if (request()->hasFile($input)) {
            $files = request()->file($input);

            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $index => $requestFile) {
                if (!$requestFile || !$requestFile->isValid()) {
                    continue;
                }

                try {
                    $originalName = pathinfo($requestFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $requestFile->getClientOriginalExtension();
                    $datetime = now()->format('Ymd_His');
                    $filename = "{$originalName}_{$datetime}.{$extension}";

                    $file = new File([
                        'file_name' => $fileName ?? $originalName,
                        'file_location' => $requestFile->storeAs(
                            (new \ReflectionClass($this))->getShortName() . "/$input",
                            $filename,
                            'public'
                        ),
                        'collection' => $collection,
                        'meta' => $meta,
                    ]);

                    $this->files()->save($file);
                } catch (\Exception $e) {
                    \Log::error('File upload failed: ' . $e->getMessage());
                    throw new \Exception('Failed to upload file at index ' . $index);
                }
            }
        }
    }

    public function syncFiles(
        string $collection = 'files',
        string $idsInput = 'files',
        string $uploadInput = 'images_upload',
        ?string $deleteFlag = 'is_images_deleted'
    ) {
        // 1. If deletion flag provided
        if (request()->boolean($deleteFlag)) {
            $this->files()->where('collection', $collection)->get()->each->delete();
        }

        // 2. Sync with incoming IDs
        $incomingIds = collect(request()->input($idsInput, []))->pluck('id')->filter()->toArray();

        if (!empty($incomingIds)) {
            $this->files()->where('collection', $collection)
                ->whereNotIn('id', $incomingIds)
                ->delete();
        } else {
            // if no IDs provided, delete all for this collection
            $this->files()->where('collection', $collection)->delete();
        }

        // 3. Handle uploads
        if (request()->hasFile($uploadInput)) {
            // Delete old collection files
            $this->files()->where('collection', $collection)->get()->each->delete();

            // Add new ones
            $this->addFiles(input: $uploadInput, collection: $collection);
        }
    }


}
