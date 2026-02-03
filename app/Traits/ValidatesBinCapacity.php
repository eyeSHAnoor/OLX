<?php

// app/Traits/ValidatesBinCapacity.php
namespace App\Traits;

use App\Models\Asn;
use App\Models\WarehousePosition;
use Illuminate\Validation\ValidationException;

trait ValidatesBinCapacity
{
    protected function validateBinCapacity($asnId, $binId, $qty, $excludeId = null)
    {
        $asn = Asn::relatedRecords()->find($asnId);
        if (!$asn) {
            throw ValidationException::withMessages(['asn_id' => ['ASN does not exist']]);
        }

        $bin = WarehousePosition::with('putaways')->find($binId);
        if (!$bin || $bin->warehouse_id !== $asn->id) {
            throw ValidationException::withMessages(['warehouse_position_id' => ['Bin does not exist in this warehouse']]);
        }

        $totalQty = $bin->putaways
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->sum('qty');

        if ($totalQty + $qty > $bin->capacity) {
            throw ValidationException::withMessages(['qty' => ['Bin does not have sufficient capacity']]);
        }
    }
}
