<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use App\Data\GiftData;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GiftController extends Controller
{
    /**
     * Display a listing of gifts.
     */
    public function index()
    {
        $columns = [
            'name',
            'quantity',
            'is_active',
            'created_at',
        ];

        $globalSearch = getGlobalSearchFilter([...$columns, 'description']);

        $gifts = QueryBuilder::for(Gift::class)
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('is_active'),
            ])
            ->paginate(getPaginate())
            ->withQueryString();

        return Inertia::render('gift-campaigns/gifts/Index', [
            'gifts' => GiftData::collect($gifts),
        ]);
    }

    /**
     * Show form to create a new gift.
     */
    public function create()
    {
        return Inertia::render('gift-campaigns/gifts/RecordForm');
    }

    /**
     * Store a newly created gift.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:gifts,name',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $giftData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'quantity' => $validated['quantity'],
                'is_active' => $validated['is_active'] ?? true,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $giftData['image'] = $request->file('image')->store('gifts', 'public');
            }

            Gift::create($giftData);
        });

        return redirect()
            ->route('gifts.index')
            ->with('success', 'Gift created successfully.');
    }

    /**
     * Show form to edit a gift.
     */
    public function edit(Gift $gift)
    {
        return Inertia::render('gift-campaigns/gifts/RecordForm', [
            'gift' => GiftData::from($gift),
        ]);
    }

    /**
     * Update the specified gift.
     */
    public function update(Request $request, Gift $gift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:gifts,name,' . $gift->id,
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'remove_image' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $request, $gift) {
            $giftData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'quantity' => $validated['quantity'],
                'is_active' => $validated['is_active'] ?? true,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($gift->image) {
                    \Storage::disk('public')->delete($gift->image);
                }
                $giftData['image'] = $request->file('image')->store('gifts', 'public');
            } elseif ($request->boolean('remove_image')) {
                // Remove image if requested
                if ($gift->image) {
                    \Storage::disk('public')->delete($gift->image);
                }
                $giftData['image'] = null;
            }

            $gift->update($giftData);
        });

        return redirect()
            ->route('gifts.index')
            ->with('success', 'Gift updated successfully.');
    }

    /**
     * Remove the specified gift.
     */
    public function destroy(Gift $gift)
    {
        // Check if gift is assigned in any campaign
        $hasAssignments = $gift->assignments()->exists();
        
        if ($hasAssignments) {
            return redirect()->back()->with('error', 
                'Cannot delete gift that has been assigned to users. Please remove all assignments first.');
        }

        DB::transaction(function () use ($gift) {
            // Delete image if exists
            if ($gift->image) {
                \Storage::disk('public')->delete($gift->image);
            }
            
            // Delete campaign gift allocations
            $gift->campaignGifts()->delete();
            
            // Delete the gift
            $gift->delete();
        });

        return redirect()
            ->route('gifts.index')
            ->with('success', 'Gift deleted successfully.');
    }

    /**
     * Toggle gift active status.
     */
    public function toggleStatus(Gift $gift)
    {
        $gift->update([
            'is_active' => !$gift->is_active
        ]);

        return redirect()->back()->with('success', 
            'Gift status updated to ' . ($gift->is_active ? 'active' : 'inactive') . '.');
    }
}