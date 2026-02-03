<?php

namespace App\Services\Stores;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class WooCommerceAdapter implements PlatformAdapterInterface
{
    protected Store $store;
    protected $creds;

    public function __construct(Store $store)
    {
        $this->store = $store;
        $this->creds = $store->credentials;
    }

    protected function client()
    {
        $baseUrl = rtrim($this->store->store_url, '/');
        return Http::withBasicAuth(
            $this->creds['consumer_key'],
            $this->creds['consumer_secret']
        )->baseUrl($baseUrl . '/wp-json/wc/v3/');
    }

    /* Orders Management */
    public function fetchOrders(array $params = []): array
    {
        $params = array_merge([
            'per_page' => 50,
            'status' => 'any',
            'orderby' => 'date',
            'order' => 'desc'
        ], $params);

        $resp = $this->client()->get('orders', $params);

        if ($resp->failed()) {
            throw new \Exception('WooCommerce fetch failed: ' . $resp->body());
        }

        return $resp->json();
    }

    public function normalizeOrder(array $o): array
    {
        $items = [];
        foreach ($o['line_items'] as $li) {
            $productId = $li['variation_id'] ?: $li['product_id'];

            $weight = null;
            $length = null;
            $width = null;
            $height = null;

            try {
                $productResp = $this->client()->get("products/{$productId}");


                if ($productResp->successful()) {
                    $product = $productResp->json();
                    $weight = Arr::get($product, 'weight');
                    $length = Arr::get($product, 'dimensions.length');
                    $width = Arr::get($product, 'dimensions.width');
                    $height = Arr::get($product, 'dimensions.height');
                }
            } catch (\Exception $e) {
                // fail silently, just leave nulls
            }

            $items[] = [
                'platform_item_id' => (string)$li['id'],
                'sku' => $li['sku'] ?? null,
                'product_name' => $li['name'],
                'quantity' => (int)$li['quantity'],
                'unit_price' => (float)$li['price'],
                'total_price' => (float)$li['total'],

                // Added weight & dimensions
                'weight' => $weight,
                'length' => $length,
                'width' => $width,
                'height' => $height,
            ];
        }

        $shipping = $o['shipping'] ?? null;
        $shipping_address = $shipping ? [
            'first_name' => $shipping['first_name'] ?? null,
            'last_name' => $shipping['last_name'] ?? null,
            'address_1' => $shipping['address_1'] ?? null,
            'address_2' => $shipping['address_2'] ?? null,
            'city' => $shipping['city'] ?? null,
            'state' => $shipping['state'] ?? null,
            'postcode' => $shipping['postcode'] ?? null,
            'country' => $shipping['country'] ?? null,
        ] : null;

        $mapStatus = [
            'pending'    => 'pending',
            'processing' => 'confirmed',   // payment received, preparing order
            'on-hold'    => 'pending',     // waiting for payment
            'completed'  => 'completed',
            'cancelled'  => 'cancelled',
            'refunded'   => 'cancelled',
            'failed'     => 'cancelled',
        ];

        $paymentStatusMap = [
            'pending'    => 'pending',
            'on-hold'    => 'pending',
            'processing' => 'paid',
            'completed'  => 'paid',
            'refunded'   => 'cancelled',
            'failed'     => 'cancelled',
            'cancelled'  => 'cancelled',
        ];

        return [
            'platform_order_id' => (string)$o['id'],
            'platform' => 'woocommerce',
            'order_date' => $o['date_created'] ?? null,
            'platform_created_at' => $o['date_created'] ?? null,
            'platform_updated_at' => $o['date_modified'] ?? null,
            'order_status' => $mapStatus[$o['status']] ?? 'pending',
            'payment_status' => $paymentStatusMap[$o['payment_method']] ?? 'pending',
            'currency' => $o['currency'] ?? $this->store->currency,
            'total_amount' => (float)$o['total'] ?? 0,
            'shipping_fee' => (float)$o['shipping_total'] ?? 0,
            'tax_amount' => (float)$o['total_tax'] ?? 0,
            'paid_amount' => (float)$o['total'] ?? 0,
            'recipient_name' => trim(($shipping['first_name'] ?? '') . ' ' . ($shipping['last_name'] ?? '')),
            'recipient_phone' => $o['billing']['phone'] ?? null,
            'shipping_address' => $shipping_address,
            'shipping_country' => $shipping['country'] ?? null,
            'shipping_postal_code' => $shipping['postcode'] ?? null,
            'items' => $items,
            'raw' => $o,
        ];
    }
    /* end: Orders Management */

    /* Products Management */
    public function fetchProducts(array $params = []): array
    {
        $resp = $this->client()->get('products');

        if ($resp->failed()) {
            throw new \Exception('WooCommerce products fetch failed: ' . $resp->body());
        }

        return $resp->json();
    }

    public function normalizeProduct(array $p): array
    {
        // If variable product, fetch its variants
        if (($p['type'] ?? '') === 'variable') {
            $variantIds = $p['variations'] ?? [];
            $variants = [];

            foreach ($variantIds as $variantId) {
                $response = $this->client()->get("products/{$p['id']}/variations/{$variantId}");

                if ($response->ok()) {
                    $variant = $response->json();
                    if (!empty($variant['id'])) {
                        $variants[] = $variant;
                    }
                }
            }

            $p['variations'] = $variants;
        } else {
            unset($p['variations']);
        }

        // Map categories
        $categories = [];
        foreach ($p['categories'] ?? [] as $cat) {
            $categories[] = [
                'platform_category_id' => $cat['id'],
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'type' => 'primary',
            ];
        }

        $productData = [
            'platform_product_id' => $p['id'] ?? null,
            'sku' => $p['sku'] ?? null,
            'product_name' => $p['name'] ?? null,
            'description' => $p['description'] ?? null,
            'merchant_id' => $this->store->merchant_id,
            'status' => $p['status'] ?? null,
            'barcode' => $this->extractMeta($p['meta_data'] ?? [], 'barcode'),
            'brand' => $this->extractMeta($p['meta_data'] ?? [], 'brand'),
            'categories' => $categories, // ✅ include categories
            'platform_mapping' => [
                'permalink' => $p['permalink'] ?? null,
                'type' => $p['type'] ?? 'simple',
            ],
        ];

        $variants = $this->normalizeVariants($p);

        $images = array_map(fn($img) => [
            'url' => $img['src'] ?? null,
            'altText' => $img['alt'] ?? null,
        ], $p['images'] ?? []);

        return [
            'product' => $productData,
            'variants' => $variants,
            'images' => $images,
            'main_image' => $images[0]['url'] ?? null,
        ];
    }


    private function normalizeVariants(array $p): array
    {
        if (($p['type'] ?? 'simple') === 'simple') {
            return [[
                'sku' => $p['sku'] ?? null,
                'barcode' => $this->extractMeta($p['meta_data'] ?? [], 'barcode'),
                'regular_price' => (float)($p['regular_price'] ?? $p['price'] ?? 0),
                'sale_price' => isset($p['sale_price']) ? (float)$p['sale_price'] : null,
                'weight' => isset($p['weight']) ? (float)$p['weight'] : null,
                'length' => isset($p['dimensions']['length']) ? (float)$p['dimensions']['length'] : null,
                'width' => isset($p['dimensions']['width']) ? (float)$p['dimensions']['width'] : null,
                'height' => isset($p['dimensions']['height']) ? (float)$p['dimensions']['height'] : null,
                'available_stock' => $p['stock_quantity'] ?? 0,
                'reserved_stock' => 0,
                'incoming_stock' => 0,
                'safety_stock' => 0,
            ]];
        }

        if (($p['type'] ?? '') === 'variable' && !empty($p['variations'])) {
            return array_map(function ($v) {
                return [
                    'sku' => $v['sku'] ?? null,
                    'barcode' => $this->extractMeta($v['meta_data'] ?? [], 'barcode'),
                    'regular_price' => (float)($v['regular_price'] ?? $v['price'] ?? 0),
                    'sale_price' => isset($v['sale_price']) ? (float)$v['sale_price'] : null,
                    'weight' => isset($v['weight']) ? (float)$v['weight'] : null,
                    'length' => isset($v['dimensions']['length']) ? (float)$v['dimensions']['length'] : null,
                    'width' => isset($v['dimensions']['width']) ? (float)$v['dimensions']['width'] : null,
                    'height' => isset($v['dimensions']['height']) ? (float)$v['dimensions']['height'] : null,
                    'available_stock' => $v['stock_quantity'] ?? 0,
                    'reserved_stock' => 0,
                    'incoming_stock' => 0,
                    'safety_stock' => 0,
                ];
            }, $p['variations']);
        }

        return [];
    }

    private function extractMeta(array $metaData, string $key)
    {
        foreach ($metaData as $meta) {
            if (($meta['key'] ?? null) === $key) {
                return $meta['value'] ?? null;
            }
        }
        return null;
    }


    public function upsertProduct(array $payload): array
    {
        // Build base product payload for WooCommerce
        $wcProduct = [
            'name' => $payload['product_name'] ?? null,
            'description' => $payload['description'] ?? null,
            'status' => $payload['status'] ?? 'draft',
            'weight' => isset($payload['weight']) ? (string)$payload['weight'] : null,
            'manage_stock' => true,
            'categories' => array_map(fn($cat) => ['id' => $cat['id']], $payload['categories'] ?? []),
            'tags' => array_map(fn($t) => ['name' => (string)$t], $payload['tags'] ?? []),
            // meta_data for brand / barcode
            'meta_data' => array_values(array_filter([
                isset($payload['brand']) ? ['key' => 'brand', 'value' => $payload['brand']] : null,
                isset($payload['barcode']) ? ['key' => 'barcode', 'value' => $payload['barcode']] : null,
            ])),
            // dimensions expected by WC as strings
            'dimensions' => [
                'length' => isset($payload['length']) ? (string)$payload['length'] : null,
                'width' => isset($payload['width']) ? (string)$payload['width'] : null,
                'height' => isset($payload['height']) ? (string)$payload['height'] : null,
            ],
            // images - include existing ones from payload.images (upload handled separately)
            'images' => array_map(fn($i) => [
                'id' => $i['id'] ?? '',
                'src' => $i['src'] ?? '',
                'alt' => $i['alt'] ?? '',
            ], $payload['images'] ?? []),
        ];

        // Variants handling: We'll populate __variants for later creation/updating in updateProduct()
        if (!empty($payload['variants']) && count($payload['variants']) > 1) {
            // Variable product
            $wcProduct['type'] = 'variable';

            // Build attributes for product (Woo requires attributes object before creating variations)
            $attributes = [];
            foreach ($payload['variants'] as $variant) {
                if (!empty($variant['attributes'])) {
                    foreach ($variant['attributes'] as $attr) {
                        $name = $attr['name'] ?? null;
                        $option = $attr['option'] ?? null;
                        if (!$name) continue;
                        $attributes[$name]['name'] = $name;
                        $attributes[$name]['variation'] = true;
                        $attributes[$name]['visible'] = true;
                        $attributes[$name]['options'][] = $option;
                    }
                }
            }
            // dedupe options
            foreach ($attributes as &$a) {
                $a['options'] = array_values(array_unique($a['options']));
            }
            $wcProduct['attributes'] = array_values($attributes);

            // Build __variants payload for create/update operations (used in updateProduct)
            $wcProduct['__variants'] = array_map(function ($v) {
                // Keep variant_id if supplied (means update)
                $payload = [
                    'sku' => $v['sku'] ?? null,
                    'regular_price' => isset($v['regular_price']) ? (string)$v['regular_price'] : (isset($v['price']) ? (string)$v['price'] : null),
                    'sale_price' => isset($v['sale_price']) && $v['sale_price'] !== 0 ? (string)$v['sale_price'] : null,
                    'stock_quantity' => isset($v['stock_quantity']) ? (int)$v['stock_quantity'] : 0,
                    'manage_stock' => true,
                    // attributes for variations: array of ['name' => ..., 'option' => ...]
                    'attributes' => array_map(fn($a) => ['name' => $a['name'], 'option' => $a['option']], $v['attributes'] ?? []),
                ];
                if (!empty($v['variant_id'])) {
                    $payload['id'] = $v['variant_id'];
                }
                return $payload;
            }, $payload['variants']);
        } else {
            // Simple product - single variant treated as product level fields
            $wcProduct['type'] = 'simple';
            $variant = $payload['variants'][0] ?? null;
            if ($variant) {
                if (!empty($variant['sku'])) {
                    $wcProduct['sku'] = $variant['sku'];
                }
                if (isset($variant['regular_price'])) {
                    $wcProduct['regular_price'] = (string)$variant['regular_price'];
                } elseif (isset($variant['price'])) {
                    $wcProduct['regular_price'] = (string)$variant['price'];
                }
                if (isset($variant['sale_price']) && $variant['sale_price'] !== 0) {
                    $wcProduct['sale_price'] = (string)$variant['sale_price'];
                }
                $wcProduct['stock_quantity'] = isset($variant['stock_quantity']) ? (int)$variant['stock_quantity'] : ($payload['available_stock'] ?? 0);
            } else {
                // fallback
                $wcProduct['stock_quantity'] = $payload['available_stock'] ?? 0;
                if (isset($payload['regular_price'])) {
                    $wcProduct['regular_price'] = (string)$payload['regular_price'];
                }
            }
        }

        return $wcProduct;
    }

    public function updateProduct(array $data)
    {
        set_time_limit(300); // 5 minutes

        $platProductId = $data['platform_product_id'] ?? null;
        if (!$platProductId) {
            throw new \InvalidArgumentException('platform_product_id is required to update a product.');
        }

        // Step 1: convert unified payload to Woo payload
        $wcPayload = $this->upsertProduct($data);

        // Step 2: update the main product
        $response = $this->client()->put("products/{$platProductId}", $wcPayload);
        $this->throwAsValidationIfPossible($response);

        if ($response->failed()) {
            throw new \Exception('WooCommerce product update failed: ' . $response->body());
        }

        $product = $response->json();

        // If product is variable, sync variants
        if (($wcPayload['type'] ?? '') === 'variable') {
            // Fetch existing variations for this product (get their IDs)
            $existingVariantIds = $this->fetchAllVariationIds($platProductId);

            $incomingVariantIds = [];
            foreach ($wcPayload['__variants'] as $variantPayload) {
                // If payload contains id → update existing variation
                if (!empty($variantPayload['id'])) {
                    $vid = $variantPayload['id'];
                    $incomingVariantIds[] = $vid;

                    // Prepare update payload: remove id key
                    $upd = $variantPayload;
                    unset($upd['id']);
                    $resp = $this->client()->put("products/{$platProductId}/variations/{$vid}", $upd);
                    $this->throwAsValidationIfPossible($resp);
                    if ($resp->failed()) {
                        // continue but log/throw as needed
                        throw new \Exception('Failed updating variation ' . $vid . ': ' . $resp->body());
                    }
                } else {
                    // create new variation
                    $resp = $this->client()->post("products/{$platProductId}/variations", $variantPayload);
                    $this->throwAsValidationIfPossible($resp);
                    if ($resp->failed()) {
                        throw new \Exception('Failed creating variation: ' . $resp->body());
                    }
                    $created = $resp->json();
                    if (!empty($created['id'])) {
                        $incomingVariantIds[] = $created['id'];
                    }
                }
            }

            // Delete any existing variations that are not present in incomingVariantIds
            $toDelete = array_diff($existingVariantIds, $incomingVariantIds);
            foreach ($toDelete as $delId) {
                $resp = $this->client()->delete("products/{$platProductId}/variations/{$delId}", ['force' => true]);
                // ignore failures here or handle as needed
                if ($resp->failed()) {
                    // optionally log
                }
            }
        } else {
            // For simple product: ensure product-level sku/price/stock are updated already by product PUT
            // But if your payload provided a variant (with id) and you still need to update a specific variant record,
            // handle here (not commonly necessary for simple product).
        }

        // Return the updated product JSON
        $p = $this->client()->get("products/{$platProductId}")->json();
        $normalized = $this->normalizeProduct($p);

        // Save or update in DB
        Product::updateOrCreate(
            [
                'platform_product_id' => $normalized['platform_product_id'],
                'merchant_id' => $this->store->merchant_id,
                'platform' => $this->store->platform,
                'store_id' => $this->store->id,
            ],
            $normalized
        );

    }

    /**
     * Fetches all variation IDs for a product (handles pagination)
     */
    protected function fetchAllVariationIds($productId): array
    {
        $ids = [];
        $page = 1;
        do {
            $resp = $this->client()->get("products/{$productId}/variations", [
                'per_page' => 100,
                'page' => $page,
            ]);
            if ($resp->failed()) {
                break;
            }
            $vars = $resp->json();
            if (empty($vars)) break;
            foreach ($vars as $v) {
                if (!empty($v['id'])) $ids[] = $v['id'];
            }
            $page++;
        } while (count($vars) === 100);

        return $ids;
    }

    protected function uploadImage(UploadedFile $file): array
    {
        $baseUrl = rtrim($this->store->store_url, '/');

        $response = $this->client()
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->withHeaders([
                'Content-Disposition' => 'attachment; filename="' . $file->getClientOriginalName() . '"',
            ])
            ->post(rtrim($this->store->store_url, '/') . '/wp-json/wp/v2/media');

        if ($response->failed()) {
            throw new \Exception('Image upload failed: ' . $response->body());
//            $this->throwAsValidationIfPossible($response);
        }

        $media = $response->json();

        return [
            'id' => $media['id'] ?? null,
            'src' => $media['source_url'] ?? null,
        ];
    }

    /* end: Products Management */

    protected function throwAsValidationIfPossible($resp): void
    {
        $j = $resp->json();
        if (isset($j['code']) && $j['code'] === 'rest_invalid_param' && isset($j['data']['params'])) {
            $errors = [];
            foreach ($j['data']['params'] as $field => $errorMessage) {
                $errors[$field] = [$errorMessage];
            }
            throw ValidationException::withMessages($errors);
        }

        if (isset($j['code']) && $j['code'] === 'rest_cannot_create') {
            $errors['message'] = [$j['message']];
            throw ValidationException::withMessages($errors);
        }
    }
}
