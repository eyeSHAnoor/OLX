<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class InboundOrderData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $order_number,
        public ?string $supplier_name,
        public ?string $supplier_invoice_no = null,
        public ?string $supplier_code = null,
        public ?string $document_type = null,
        public ?string $invoice_date = null,
        public ?string $payment_method = null,
        public ?string $bank_account = null,
        public ?string $currency = null,
        public ?float $total_amount = null,
        public ?float $tax_amount = null,
        public ?string $notes = null,
        public ?string $supplier_tax_id = null,
        public ?string $supplier_address = null,
        public ?string $due_date = null,
        public ?string $expected_date = null,
        public ?bool $verified = false,
        public ?string $status = null,
        public ?int $items_count = null,
        /** @var OrderItemData[]|null */
        public ?array $items = null,
        public ?int $created_by = null,
        public ?int $updated_by = null,
        public ?string $order_date = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
    }
}
