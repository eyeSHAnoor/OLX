<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait HasManySync
{
    /**
     * Sync a hasMany relationship with given data.
     *
     * @param string $relation Relation name
     * @param array $items Array of attributes (with optional `id`)
     * @param array $fillable Fields to update/create
     * @param string $key Column used for matching existing records
     */
    public function syncHasMany(string $relation, array $items, array $fillable, string $key = 'id'): void
    {
        $relationQuery = $this->$relation();

        // IDs in request (only those with existing IDs)
        $incomingIds = collect($items)
            ->pluck($key)
            ->filter()
            ->toArray();

//        dd($key, $incomingIds, $this->$relation()->whereNotIn($key, $incomingIds)->get());

        // delete records that are not in incoming
        if (!empty($incomingIds)) {
            $relationQuery->whereNotIn($key, $incomingIds)->delete();
        } else {
            // if no IDs provided, delete all
            $relationQuery->delete();
        }

        foreach ($items as $i) {
//            dd($i, $key,  $i[$key], $this->$relation()->where($key, $i[$key])->first());

            if (!empty($i[$key])) {
                // update existing
                $this->$relation()->where($key, $i[$key])->update(Arr::only($i, $fillable));
            } else {
                // create new
                $this->$relation()->create(Arr::only($i, $fillable));
            }
        }
    }
}
