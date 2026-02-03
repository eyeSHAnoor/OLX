<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait RelatedRecord
{
	/**
	 * Boot the trait/model.
	 */
	protected static function bootRelatedRecord()
	{
		// parent::boot();

		// static::creating(function ($model) {
		// if (auth()->check()) {
		//   $model->user_id = request()['user_id'] ?? auth()->user()->id;
		// }
		// });

		//    if (auth()->check()) {
		//      static::addGlobalScope('related_records', function (Builder $builder) {
		//        $user = auth()->user();
		//
		//        if ($user->is_super_admin)
		//          return $builder;
		//
		//        $relatedColumn =  self::getTableName() . '.user_id';
		//
		//        return $builder
		//          ->where($relatedColumn, $user->id);
		//      });
		//    }

	}

	public static function getTableName()
	{
		return (new self())->getTable();
	}

	public function scopeRelatedRecords($builder, $column = 'merchant_id')
	{
		$user = auth()->user();

		if ($user->is_super_admin)
			return $builder;

		return $builder->where($column, $user->id);
	}
}
