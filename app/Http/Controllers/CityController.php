<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Region;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CityController extends Controller
{
    /**
     * Display a paginated list of cities.
     */
    public function index()
    {
        $columns = [
            'name',
            'country',
            'created_at',
        ];

        // Global search helper (same as BrandController)
        $globalSearch = getGlobalSearchFilter([...$columns]);

        $cities = QueryBuilder::for(City::class)
            ->with(['regions'])               // eager load regions
            ->withCount('regions')            // count of regions per city
            ->defaultSort('-created_at')
            ->allowedSorts($columns)
            ->allowedFilters([
                $globalSearch,
                AllowedFilter::exact('country'), // optional: filter by country
            ])
            ->paginate(getPaginate())
            ->withQueryString();

        return Inertia::render('city/Index', [
            'cities' => $cities,
        ]);
    }

    /**
     * Show the form for creating a new city.
     */
    public function create()
    {
        return Inertia::render('city/Create');
    }

    /**
     * Store a newly created city in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'lat'     => 'nullable|numeric',
            'lng'     => 'nullable|numeric',
            'regions' => 'nullable|array',
            'regions.*.name' => 'required|string|max:255|distinct',
        ]);

        DB::transaction(function () use ($validated) {
            $city = City::create([
                'name'    => $validated['name'],
                'country' => $validated['country'],
                'lat'     => $validated['lat'] ?? null,
                'lng'     => $validated['lng'] ?? null,
            ]);

            // Create regions if provided
            if (!empty($validated['regions'])) {
                $regions = collect($validated['regions'])->map(function ($region) use ($city) {
                    return [
                        'city_id' => $city->id,
                        'name'    => $region['name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();

                Region::insert($regions);
            }
        });

        return redirect()->back()->with('success', 'City created successfully.');
    }

    /**
     * Display a specific city with its regions.
     */
    public function show(City $city)
    {
        $city->load('regions');
        return response()->json($city);
    }

    /**
     * Show the form for editing the specified city.
     */
    public function edit(City $city)
    {
        $city->load('regions');
        return Inertia::render('city/Edit', [
            'city' => $city,
        ]);
    }

    /**
     * Update the specified city in storage.
     */
    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'lat'     => 'nullable|numeric',
            'lng'     => 'nullable|numeric',
            'regions' => 'nullable|array',
            'regions.*.id'   => 'nullable|exists:regions,id',
            'regions.*.name' => 'required|string|max:255|distinct',
        ]);

        DB::transaction(function () use ($validated, $city) {
            // Update city basic info
            $city->update([
                'name'    => $validated['name'],
                'country' => $validated['country'],
                'lat'     => $validated['lat'] ?? null,
                'lng'     => $validated['lng'] ?? null,
            ]);

            // Sync regions (create, update, delete)
            if (isset($validated['regions'])) {
                $this->syncRegions($city, $validated['regions']);
            } else {
                // If no regions provided, delete all existing regions
                $city->regions()->delete();
            }
        });

        return redirect()->back()->with('success', 'City updated successfully.');
    }

    /**
     * Remove the specified city and its regions.
     */
    public function destroy(City $city)
    {
        DB::transaction(function () use ($city) {
            // Delete associated regions first
            $city->regions()->delete();
            // Delete the city
            $city->delete();
        });

        return redirect()->back()->with('success', 'City deleted successfully.');
    }

    /**
     * Get regions for a specific city (API endpoint).
     */
    public function getRegions(City $city)
    {
        return response()->json([
            'regions' => $city->regions()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store additional regions for a city.
     */
    public function storeRegions(Request $request, City $city)
    {
        $validated = $request->validate([
            'regions' => 'required|array',
            'regions.*.name' => 'required|string|max:255|distinct',
        ]);

        DB::transaction(function () use ($validated, $city) {
            $regions = collect($validated['regions'])->map(function ($region) use ($city) {
                return [
                    'city_id' => $city->id,
                    'name'    => $region['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            Region::insert($regions);
        });

        return redirect()->back()->with('success', 'Regions added successfully.');
    }

    /**
     * Update a specific region.
     */
    public function updateRegion(Request $request, Region $region)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:regions,name,' . $region->id,
        ]);

        $region->update($validated);

        return redirect()->back()->with('success', 'Region updated successfully.');
    }

    /**
     * Delete a specific region.
     */
    public function destroyRegion(Region $region)
    {
        $region->delete();

        return redirect()->back()->with('success', 'Region deleted successfully.');
    }

    /**
     * Sync regions for a city (used internally by update).
     *
     * @param City $city
     * @param array $regions
     * @return void
     */
    private function syncRegions(City $city, array $regions): void
    {
        $existingRegionIds = $city->regions()->pluck('id')->toArray();
        $updatedRegionIds = [];

        foreach ($regions as $region) {
            if (isset($region['id']) && in_array($region['id'], $existingRegionIds)) {
                // Update existing region
                Region::where('id', $region['id'])->update([
                    'name' => $region['name'],
                    'updated_at' => now(),
                ]);
                $updatedRegionIds[] = $region['id'];
            } elseif (!isset($region['id'])) {
                // Create new region
                $newRegion = $city->regions()->create([
                    'name' => $region['name'],
                ]);
                $updatedRegionIds[] = $newRegion->id;
            }
        }

        // Delete regions that are no longer present
        $regionsToDelete = array_diff($existingRegionIds, $updatedRegionIds);
        if (!empty($regionsToDelete)) {
            Region::whereIn('id', $regionsToDelete)->delete();
        }
    }
}