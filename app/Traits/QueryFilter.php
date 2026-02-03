<?php

namespace App\Traits;

use Carbon\Carbon;

trait QueryFilter
{
    public function scopeQueryFilter($query, $columns, $search)
    {
        $value = is_array($search) ? implode(',', $search) : $search;

        $query->where(function ($q) use ($columns, $value) {
            foreach ($columns as $key => $column) {
                if (str_contains($column, '.')) {
                    // Handle relations: e.g., 'category.name'
                    [$relation, $field] = explode('.', $column, 2);

                    $q->orWhereHas($relation, function ($q2) use ($field, $value) {
                        $q2->where($field, 'LIKE', "%{$value}%");
                    });
                } else {
                    // Handle direct fields
                    if ($key === 0) {
                        $q->where($column, 'LIKE', "%{$value}%");
                    } else {
                        $q->orWhere($column, 'LIKE', "%{$value}%");
                    }
                }
            }
        });

        return $query;
    }

    function scopeDateFilter($query, $column = 'created_at')
    {
        $query->when(request()['start_date'] ?? null, function ($query, $date) use ($column) {
            $query->whereDate($column, '>=',  Carbon::parse(trim($date))->format('Y-m-d'));
        })
            ->when(request()['end_date'] ?? null, function ($query, $date) use ($column) {
                $query->whereDate($column, '<=',  Carbon::parse(trim($date))->format('Y-m-d'));
            });

        // if (!request()->date) {
        //     return $query;
        // }
        // $date      = explode('-', request()->date);


        // $startDate = Carbon::parse(trim($date[0]))->format('Y-m-d');
        // $endDate = Carbon::parse(trim(@$date[1]))->format('Y-m-d');

        // request()->merge(['start_date' => $startDate, 'end_date' => $endDate]);

        // request()->validate([
        //     'start_date' => 'required|date_format:Y-m-d',
        //     'end_date'   => 'nullable|date_format:Y-m-d',
        // ]);

        // return  $query
        //     ->whereDate($column, '>=', $startDate)
        //     ->whereDate($column, '<=', $endDate ?? $startDate);
    }
}
