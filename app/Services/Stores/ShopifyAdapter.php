<?php

namespace App\Services\Stores;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShopifyAdapter implements PlatformAdapterInterface
{
    protected Store $store;
    protected $creds;
    protected string $apiVersion = '2024-07'; // bump to '2025-07' if needed

    public function __construct(Store $store)
    {
        $this->store = $store;
        $this->creds = $store->credentials;
    }


    protected function base(): string
    {
        return 'https://' . rtrim($this->store->store_url, '/');
    }

    protected function headers(): array
    {
        return [
            'X-Shopify-Access-Token' => Arr::get($this->creds, 'access_token'),
            'Content-Type' => 'application/json',
        ];
    }

    protected function gql(string $query, array $variables = [])
    {
        $resp = Http::withHeaders($this->headers())
            ->post($this->base() . "/admin/api/{$this->apiVersion}/graphql.json", [
                'query' => $query,
                'variables' => $variables,
            ]);

        $json = $resp->json();

        // Convert GraphQL errors to Laravel validation errors if possible
        $this->throwShopifyValidationIfPossible($json, $resp->status());

        if ($resp->failed() || isset($json['errors'])) {
            throw new \Exception('Shopify GraphQL failed: ' . json_encode($json['errors'] ?? $json));
        }

        return $json['data'] ?? [];
    }

    protected function toGid(string $type, $id): string
    {
        $id = (string)$id;
        return str_starts_with($id, 'gid://shopify/') ? $id : "gid://shopify/{$type}/{$id}";
    }

    /* Orders Management */
    public function fetchOrders(array $params = []): array
    {
        $base = "https://" . rtrim($this->store->store_url, '/');
        $token = Arr::get($this->creds, 'access_token');

        $graphqlQuery = '
        query getOrders($first: Int!) {
            orders(first: $first) {
                edges {
                    node {
                        id
                        legacyResourceId
                        name
                        createdAt
                        updatedAt
                        cancelledAt
                        cancelReason
                        displayFulfillmentStatus
                        displayFinancialStatus
                        totalPriceSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        totalShippingPriceSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        totalTaxSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        currentTotalPriceSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }

                        # --- Add customer info ---
                        customer {
                          id
                          email
                          firstName
                          lastName
                          phone
                        }
                        # --- Add shipping info ---
                        billingAddress {
                          firstName
                          lastName
                          address1
                          address2
                          city
                          province
                          country
                          zip
                          phone
                        }
                        shippingAddress {
                          firstName
                          lastName
                          address1
                          address2
                          city
                          province
                          country
                          zip
                          phone
                        }
                        displayAddress {
                          firstName
                          lastName
                          address1
                          address2
                          city
                          province
                          country
                          countryCode
                          zip
                          phone
                        }
                       lineItems(first: 50) {
                          edges {
                            node {
                              id
                              title
                              quantity
                              variant {
                                id
                                sku
                                price
                                inventoryItem {
                                  measurement {
                                    weight {
                                      value
                                      unit
                                    }
                                  }
                                }
                              }
                            }
                          }
                        }

                    }
                }
                pageInfo {
                    hasNextPage
                    endCursor
                }
            }
        }
    ';

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $token,
            'Content-Type' => 'application/json',
        ])->post("$base/admin/api/2024-07/graphql.json", [
            'query' => $graphqlQuery,
            'variables' => ['first' => $params['limit'] ?? 50]
        ]);

        $responseData = $response->json();
//        dd($response->json());

        if ($response->failed() || isset($responseData['errors'])) {
            throw new \Exception('Shopify fetch failed: ' . json_decode($responseData['errors'] ?? $response->body()));
        }

        $orders = [];
        if (isset($responseData['data']['orders']['edges'])) {
            foreach ($responseData['data']['orders']['edges'] as $edge) {
                $orderNode = $edge['node'];

                // Process line items from GraphQL response
                $lineItems = [];
                if (isset($orderNode['lineItems']['edges'])) {
                    foreach ($orderNode['lineItems']['edges'] as $lineItemEdge) {
                        $lineItems[] = $lineItemEdge['node'];
                    }
                }
                $orderNode['line_items'] = $lineItems; // Add for normalizeOrder compatibility

                $orders[] = $orderNode;
            }
        }
        return $orders;
    }

    public function normalizeOrder(array $o): array
    {
//        dd($o);
        // Extract line items (handle both GraphQL and REST format)
        $lineItems = $o['line_items'] ?? [];

        $items = [];
        foreach ($lineItems as $li) {
            $variant = $li['variant'] ?? [];

            $items[] = [
                'platform_item_id' => is_array($li['id'] ?? null) ? json_encode($li['id']) : (string)($li['id'] ?? ''),
                'sku' => $variant['sku'] ?? ($li['sku'] ?? null),
                'product_name' => $li['title'] ?? ($li['name'] ?? null),
                'quantity' => (int)($li['quantity'] ?? 1),
                'unit_price' => (float)($variant['price'] ?? ($li['price'] ?? ($li['originalUnitPriceSet']['shopMoney']['amount'] ?? 0))),
                'total_price' => (float)($variant['price'] ?? ($li['price'] ?? ($li['originalUnitPriceSet']['shopMoney']['amount'] ?? 0))) * (int)($li['quantity'] ?? 1),

                // Add weight & dimensions
                'weight' => Arr::get($variant, 'inventoryItem.measurement.weight.value'),
                'weight_unit' => Arr::get($variant, 'inventoryItem.measurement.weight.unit'),
                //dimension not supported in shopify order line items
//                'length' => $variant['length'] ?? null,
//                'width' => $variant['width'] ?? null,
//                'height' => $variant['height'] ?? null,
            ];
        }

        // Extract financial data (handle GraphQL format)
        $totalAmount = $o['totalPriceSet']['shopMoney']['amount'] ?? ($o['total_price'] ?? 0);
        $shippingFee = $o['totalShippingPriceSet']['shopMoney']['amount'] ?? ($o['total_shipping_price_set']['shop_money']['amount'] ?? 0);
        $taxAmount = $o['totalTaxSet']['shopMoney']['amount'] ?? ($o['total_tax'] ?? 0);
        $paidAmount = $o['currentTotalPriceSet']['shopMoney']['amount'] ?? ($o['current_total_price'] ?? $totalAmount);
        $currency = $o['totalPriceSet']['shopMoney']['currencyCode'] ?? ($o['currency'] ?? $this->store->currency);
        $customer = $o['customer'];
        $shipping = $o['shippingAddress'] ?? $o['billingAddress'] ?? $o['displayAddress'] ?? null;

        // FIX: Handle GraphQL ID format properly
        $platformOrderId = $o['legacyResourceId'] ?? $o['id'];
        if (is_array($platformOrderId)) {
            $platformOrderId = json_encode($platformOrderId);
        } else {
            // Extract numeric ID from GraphQL format: "gid://shopify/Order/123456"
            if (strpos($platformOrderId, 'gid://shopify/') === 0) {
                $platformOrderId = basename($platformOrderId);
            }
        }
        $mapStatus = function ($order) {
            if ($order['cancelledAt']) return 'cancelled';
            if (strtolower($order['displayFulfillmentStatus']) === 'fulfilled') return 'completed';
            if (strtolower($order['displayFulfillmentStatus']) === 'shipped') return 'shipped';
            if (strtolower($order['displayFulfillmentStatus']) === 'in_progress') return 'picking'; // or packed depending on business flow
            if (strtolower($order['displayFinancialStatus']) === 'paid') return 'confirmed';
            return 'pending';
        };

        $paymentStatusMap = [
            'PENDING'        => 'pending',
            'AUTHORIZED'     => 'pending',
            'PARTIALLY_PAID' => 'partial_paid',
            'PAID'           => 'paid',
            'VOIDED'         => 'cancelled',
            'REFUNDED'       => 'cancelled',
        ];


        return [
            'platform_order_id' => (string)$platformOrderId,
            'platform' => 'shopify',
            'platform_created_at' => $o['createdAt'] ?? ($o['created_at'] ?? null),
            'platform_updated_at' => $o['updatedAt'] ?? ($o['updated_at'] ?? null),
            'order_status' => $mapStatus($o),
            'payment_status' => $paymentStatusMap[$o['displayFinancialStatus']] ?? 'pending',
            'currency' => (string)$currency,
            'total_amount' => (float)$totalAmount,
            'shipping_fee' => (float)$shippingFee,
            'tax_amount' => (float)$taxAmount,
            'paid_amount' => (float)$paidAmount,

            # Customer data
            'recipient_name' => trim(($customer['firstName'] ?? '') . ' ' . ($customer['lastName'] ?? '')),
            'recipient_phone' => $customer['phone'] ?? $shipping['phone'] ?? null,

            # Shipping data
            'shipping_address' => [
                "city" => Arr::get($shipping, 'city'),
                "state" => Arr::get($shipping, 'province'),
                "country" => Arr::get($shipping, 'country'),
                "postcode" => Arr::get($shipping, 'zip'),
                "address_1" => Arr::get($shipping, 'address1'),
                "address_2" => Arr::get($shipping, 'address2'),
            ],
            'shipping_country' => Arr::get($shipping, 'country'),
            'shipping_postal_code' => Arr::get($shipping, 'zip'),

            'items' => $items,
            'raw' => $o,
        ];
    }


    /* end: Orders Management */

    /* Products Management */
    public function fetchProducts(array $params = []): array
    {
        $query = <<<'GQL'
         query getProducts($first: Int!) {
          products(first: $first) {
            edges {
              node {
                id
                legacyResourceId
                title
                descriptionHtml
                handle
                status
                vendor
                productType
                tags
                createdAt
                updatedAt
                totalInventory
                featuredImage {
                  url
                  altText
                }
                images(first: 10) {
                  edges {
                    node {
                      url
                      altText
                    }
                  }
                }
                variants(first: 50) {
                  edges {
                    node {
                      id
                      legacyResourceId
                      title
                      sku
                      barcode
                      price
                      inventoryQuantity
                      inventoryItem {
                        id
                        tracked
                        measurement {
                          weight {
                            value
                            unit
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
            pageInfo {
              hasNextPage
              endCursor
            }
          }
        }
        GQL;

        $responseData = $this->gql($query, [
            'first' => $params['limit'] ?? 50,
        ]);

        $products = [];
        if (isset($responseData['products']['edges'])) {
            foreach ($responseData['products']['edges'] as $edge) {
                $productNode = $edge['node'];

                // Process variants
                $variants = [];
                if (isset($productNode['variants']['edges'])) {
                    foreach ($productNode['variants']['edges'] as $variantEdge) {

                        $variants[] = $variantEdge['node'];
                    }
                }

                $productNode['variants'] = $variants;

                // Process images
                $images = [];
                if (isset($productNode['images']['edges'])) {
                    foreach ($productNode['images']['edges'] as $imageEdge) {
                        $images[] = $imageEdge['node'];
                    }
                }
                $productNode['images'] = $images;

                $products[] = $productNode;
            }
        }
        return $products;
    }

    public function fetchProduct(string $productId): ?array
    {
        $query = <<<'GQL'
    query getProduct($id: ID!) {
      product(id: $id) {
        id
        legacyResourceId
        title
        descriptionHtml
        handle
        status
        vendor
        productType
        tags
        createdAt
        updatedAt
        totalInventory
        featuredImage {
          url
          altText
        }
        images(first: 10) {
          edges {
            node {
              url
              altText
            }
          }
        }
        variants(first: 50) {
          edges {
            node {
              id
              legacyResourceId
              title
              sku
              barcode
              price
              inventoryQuantity
              inventoryItem {
                id
                tracked
                measurement {
                  weight {
                    value
                    unit
                  }
                }
              }
            }
          }
        }
      }
    }
    GQL;

        $responseData = $this->gql($query, [
            'id' => $productId, // Shopify expects a gid:// string like gid://shopify/Product/1234567890
        ]);

        if (!isset($responseData['product'])) {
            return null;
        }

        $p = $responseData['product'];

        // Process variants
        $variants = [];
        if (isset($p['variants']['edges'])) {
            foreach ($p['variants']['edges'] as $variantEdge) {
                $variants[] = $variantEdge['node'];
            }
        }
        $p['variants'] = $variants;

        // Process images
        $images = [];
        if (isset($p['images']['edges'])) {
            foreach ($p['images']['edges'] as $imageEdge) {
                $images[] = $imageEdge['node'];
            }
        }
        $p['images'] = $images;

        return $p;
    }

    public function normalizeProduct(array $p): array
    {
        $variants = $p['variants'] ?? [];
        $primaryVariant = $variants[0] ?? null;

        // Extract images
        $images = $p['images'] ?? [];
        $mainImage = $p['featuredImage']['url'] ?? ($images[0]['url'] ?? null);

        // Map platform IDs for all variants
        $platformMapping = [];
        foreach ($variants as $variant) {
            $platformMapping[$variant['legacyResourceId'] ?? $variant['id']] = [
                'shopify_product_id' => $p['legacyResourceId'] ?? $p['id'],
                'shopify_variant_id' => $variant['legacyResourceId'] ?? $variant['id'],
            ];
        }

        // Categories and Tags
        $categories = [];

        // Primary category (productType)
        if (!empty($p['productType'])) {
            $categories[] = [
                'platform_category_id' => null, // Shopify doesn’t give an ID
                'name' => $p['productType'],
                'slug' => Str::slug($p['productType']),
                'type' => 'primary',
            ];
        }

        // Tags as categories
        foreach ($p['tags'] ?? [] as $tag) {
            $categories[] = [
                'platform_category_id' => null,
                'name' => $tag,
                'slug' => Str::slug($tag),
                'type' => 'tag',
            ];
        }

        return [
            'product' => [
                'platform_product_id' => $p['legacyResourceId'] ?? null,
                'sku' => $primaryVariant['sku'] ?? null,
                'product_name' => $p['title'] ?? null,
                'description' => $p['descriptionHtml'] ?? null,
                'barcode' => $primaryVariant['barcode'] ?? null,
                'status' => Arr::get($p, 'status'),
                'brand' => $p['vendor'] ?? null,
                'platform_mapping' => !empty($platformMapping) ? $platformMapping : null,
                'categories' => $categories,
            ],
            'variants' => array_map(function ($variant) {
                return [
                    'sku' => $variant['sku'] ?? null,
                    'regular_price' => (float)($variant['price'] ?? 0),
                    'sale_price' => null,
                    'weight' => (float)Arr::get($variant, 'inventoryItem.measurement.weight.value', 0),
                    'length' => null,
                    'width' => null,
                    'height' => null,
                    'available_stock' => $variant['inventoryQuantity'] ?? 0,
                    'reserved_stock' => 0,
                    'incoming_stock' => 0,
                    'safety_stock' => 0,
                ];
            }, $variants),
            'images' => array_map(fn($img) => [
                'url' => $img['url'],
                'altText' => $img['altText'] ?? null,
            ], $images),
            'main_image' => $mainImage,
        ];
    }


    public function updateProduct(array $payload)
    {
//        dd($payload);

        $productGid = $this->toGid('Product', $payload['platform_product_id']);

        // 1) Core product update (no images/variants)
        $productInput = array_filter([
            'id' => $productGid,
            'title' => $payload['product_name'] ?? null,
            'descriptionHtml' => $payload['description'] ?? null,
            'vendor' => $payload['vendor'] ?? null,
            'productType' => $payload['product_type'] ?? null,
//            'weight' => $payload['weight'] ?? 0,
//            'price ' => (float)$payload['regular_price'] ?? null,
            'tags' => $payload['tags'] ?? null, // array of strings
            'category' => isset($payload['category_id']) ? ['id' => $payload['category_id']] : null,
            'collectionsToJoin' => $payload['collections_join'] ?? null,
            'collectionsToLeave' => $payload['collections_leave'] ?? null,
        ], fn($v) => !is_null($v));

        $mutation = <<<'GQL'
        mutation ProductUpdate($product: ProductUpdateInput!) {
          productUpdate(product: $product) {
            product { id title tags productType vendor }
            userErrors { field message }
          }
        }
        GQL;

        $data = $this->gql($mutation, ['product' => $productInput]);

        $this->throwIfUserErrors($data['productUpdate']['userErrors'] ?? [], 'product');

        // 2) Media: images update
        $this->syncProductMediaImages($productGid, $payload['images'], $payload['newImages']);

        // 3) Variants update + inventory)
        $this->syncProductVariations($productGid, $payload['variants']);


        // 4) Sync db product  with store product
        $shopifyProductId = Arr::get($data, 'productUpdate.product.id');

        $productNode = $this->fetchProduct($shopifyProductId);
        if ($productNode) {
            $normalized = $this->normalizeProduct($productNode);

            Product::updateOrCreate(
                [
                    'platform_product_id' => $normalized['platform_product_id'],
                    'merchant_id' => $payload['merchant_id'],
                    'platform' => $payload['platform'],
                    'store_id' => $payload['store_id'],
                ],
                $normalized
            );
        }

    }

    /** Add images to product using productCreateMedia (Media API). */
    protected function syncProductMediaImages(string $productGid, $uploadedImages = [], $newImages = []): array
    {
        $this->deleteRemovedImages($productGid, $uploadedImages);

        // 2. Determine which new images to upload
        if (!empty($newImages)) {
            $newMediaInputs = [];
            foreach ($newImages as $image) {
                if ($image) {
                    // 2.1 Request a staged upload URL
                    $mutation = <<<'GQL'
                mutation stagedUploadsCreate($input: [StagedUploadInput!]!) {
                  stagedUploadsCreate(input: $input) {
                    stagedTargets {
                      url
                      resourceUrl
                      parameters {
                        name
                        value
                      }
                    }
                    userErrors {
                      field
                      message
                    }
                  }
                }
               GQL;

                    $response = $this->gql($mutation, [
                        'input' => [[
                            'filename' => $image->getClientOriginalName(),
                            'mimeType' => $image->getMimeType(),
                            'resource' => 'IMAGE',
                        ]]
                    ]);

                    $this->throwIfUserErrors($response['stagedUploadsCreate']['userErrors'] ?? [], 'images');

                    $stagedTarget = $response['stagedUploadsCreate']['stagedTargets'][0];

                    // 2.2 Upload to S3 with proper error handling
                    $stream = fopen($image->getRealPath(), 'r');

                    $uploadResponse = Http::withHeaders([
                        'Content-Type' => $image->getMimeType(),
                    ])->withBody($stream, $image->getMimeType())
                        ->put($stagedTarget['url']);

                    fclose($stream);

                    if (!$uploadResponse->successful()) {
                        throw new \Exception("Upload failed: {$uploadResponse->status()} - {$uploadResponse->body()}");
                    }

                    // File successfully uploaded, prepare for productCreateMedia
                    $newMediaInputs[] = [
                        'alt' => 'IMAGE ALT TEXT',
                        'mediaContentType' => 'IMAGE',
                        'originalSource' => $stagedTarget['resourceUrl'],
                    ];
                }
            }
        }

        if (empty($newMediaInputs)) {
            return [];
        }

        // 3. Create media in Shopify
        $mutation = <<<'GQL'
        mutation productCreateMedia($productId: ID!, $media: [CreateMediaInput!]!) {
          productCreateMedia(productId: $productId, media: $media) {
            media {
              id
              alt
              preview {
                image {
                  url
                  originalSrc
                }
              }
            }
            mediaUserErrors {
              field
              message
            }
          }
        }
        GQL;

        $data = $this->gql($mutation, [
            'productId' => $productGid,
            'media' => $newMediaInputs,
        ]);

        // Check for errors in the correct field name
        $this->throwIfUserErrors($data['productCreateMedia']['mediaUserErrors'] ?? [], 'images');


        // If provided existing URL: check if already in Shopify

        // 4. Return merged results
//        return array_merge(
//            array_values($existingImages),
//            $data['productCreateMedia']['media'] ?? []
//        );
        return $data['productCreateMedia']['media'] ?? [];
    }

    protected function deleteRemovedImages(string $productGid, $uploadedImages = []): void
    {
        if (empty($uploadedImages)) return;

        // 1) Fetch current product images (media)
        $query = <<<'GQL'
            query getProductMedia($id: ID!) {
              product(id: $id) {
                media(first: 100) {
                  edges {
                    node {
                      ... on MediaImage {
                        id
                        preview {
                          image { url }
                        }
                      }
                    }
                  }
                }
              }
            }
            GQL;

        $resp = $this->gql($query, ['id' => $productGid]);

        $mediaEdges = Arr::get($resp, 'product.media.edges', []);
        $existing = collect($mediaEdges)->map(fn($e) => $e['node'])->all();

        // 2) Build map of incoming uploaded image URLs
        $incomingUrls = collect($uploadedImages)->pluck('url')->map(function ($url) {
            // normalize: drop query params
            return strtok($url, '?');
        })->toArray();

        // 3) Find mediaIds to delete (those not in incomingUrls)
        $toDelete = [];
        foreach ($existing as $media) {
            $url = strtok($media['preview']['image']['url'] ?? '', '?');
            if ($url && !in_array($url, $incomingUrls, true)) {
                $toDelete[] = $media['id'];
            }
        }

        if (empty($toDelete)) {
            return; // nothing to delete
        }

        // 4) Delete them
        $mutation = <<<'GQL'
            mutation productDeleteMedia($productId: ID!, $mediaIds: [ID!]!) {
              productDeleteMedia(productId: $productId, mediaIds: $mediaIds) {
                deletedMediaIds
                mediaUserErrors { field message }
              }
            }
            GQL;

        $result = $this->gql($mutation, [
            'productId' => $productGid,
            'mediaIds' => $toDelete,
        ]);

        $this->throwIfUserErrors($data['productDeleteMedia']['mediaUserErrors'] ?? [], 'images');
    }


    protected function syncProductVariations(string $productGid, $variants = [])
    {
        if (empty($variants) || empty($productGid)) return;

        $updateInputs = []; // For productVariantsBulkUpdate
        $createInputs = []; // For productVariantsBulkCreate (ProductVariantInput)
        $createMap = [];    // maps created input position -> original $variants index

        foreach ($variants as $idx => $variant) {
            if (!empty($variant['id'])) {
                // Existing variant -> prepare update payload (no sku/title/options)
                $updateInputs[] = [
                    'id' => $variant['id'],
                    'price' => isset($variant['price']) ? (string)$variant['price'] : null,
                    'compareAtPrice' => isset($variant['compareAtPrice']) ? (string)$variant['compareAtPrice'] : null,
                    'barcode' => $variant['barcode'] ?? null,
                ];

                // Update inventoryItem for existing variant (sku + weight)
                if (!empty($variant['inventoryItem']['id'])) {
                    $invMutation = <<<'GQL'
                    mutation inventoryItemUpdate($id: ID!, $input: InventoryItemInput!) {
                      inventoryItemUpdate(id: $id, input: $input) {
                        inventoryItem {
                          id
                          sku
                          measurement { weight { unit value } }
                        }
                        userErrors { field message }
                      }
                    }
                    GQL;

                    $invInput = [
                        // include sku only when present in original payload
                        'sku' => $variant['sku'] ?? null,
                        'measurement' => [
                            'weight' => [
                                'unit' => $variant['inventoryItem']['measurement']['weight']['unit'] ?? 'KILOGRAMS',
                                'value' => $variant['inventoryItem']['measurement']['weight']['value'] ?? 0,
                            ],
                        ],
                    ];

                    // strip nulls from 'input' top-level (so we don't send sku => null)
                    $invInput = array_filter($invInput, fn($v) => !is_null($v));
                    $resp = $this->gql($invMutation, [
                        'id' => $variant['inventoryItem']['id'],
                        'input' => $invInput,
                    ]);

                    $this->throwIfUserErrors($resp['inventoryItemUpdate']['userErrors'] ?? [], 'inventoryItem');
                }
            } else {
                // New variant -> ProductVariantInput (can include title, sku, options)
                $createInputs[] = [
                    'title' => $variant['title'] ?? 'Default Variant',
                    'sku' => $variant['sku'] ?? null,
                    'price' => isset($variant['price']) ? (string)$variant['price'] : null,
                    'compareAtPrice' => isset($variant['compareAtPrice']) ? (string)$variant['compareAtPrice'] : null,
                    'barcode' => $variant['barcode'] ?? null,
                    'options' => $variant['options'] ?? [], // only for create
                ];

                // keep mapping to original index so we can update inventoryItem after creation
                $createMap[] = $idx;
            }
        }

        /// TODO: create and delete not working
        // ---- Create new variants (if any) ----
        if (!empty($createInputs)) {
            $createMutation = <<<'GQL'
                mutation ProductVariantsCreate($productId: ID!, $variants: [ProductVariantInput!]!) {
                  productVariantsBulkCreate(productId: $productId, variants: $variants) {
                    productVariants {
                      id
                      title
                      price
                      sku
                       inventoryItem {
                        id
                      }
                    }
                    userErrors { field message }
                  }
                }
                GQL;

            $createResp = $this->gql($createMutation, [
                'productId' => $productGid,
                'variants' => $createInputs,
            ]);

            $this->throwIfUserErrors($createResp['productVariantsBulkCreate']['userErrors'] ?? [], 'createVariants');

            // Update sku + weight for each created variant's inventoryItem using mapping
            $createdVariants = $createResp['productVariantsBulkCreate']['productVariants'] ?? [];
            foreach ($createdVariants as $i => $createdVariant) {
                $origIndex = $createMap[$i] ?? null;
                if ($origIndex === null) continue; // safety
                $orig = $variants[$origIndex] ?? [];

                if (!empty($createdVariant['inventoryItem']['id'])) {
                    $invMutation = <<<'GQL'
                mutation inventoryItemUpdate($id: ID!, $input: InventoryItemInput!) {
                  inventoryItemUpdate(id: $id, input: $input) {
                    inventoryItem { id sku measurement { weight { unit value } } }
                    userErrors { field message }
                  }
                }
                GQL;

                    $invInput = [
                        'sku' => $orig['sku'] ?? null,
                        'measurement' => [
                            'weight' => [
                                'unit' => $orig['inventoryItem']['measurement']['weight']['unit'] ?? 'KILOGRAMS',
                                'value' => $orig['inventoryItem']['measurement']['weight']['value'] ?? 0,
                            ],
                        ],
                    ];
                    $invInput = array_filter($invInput, fn($v) => !is_null($v));

                    $invResp = $this->gql($invMutation, [
                        'id' => $createdVariant['inventoryItem']['id'],
                        'input' => $invInput,
                    ]);

                    $this->throwIfUserErrors($invResp['inventoryItemUpdate']['userErrors'] ?? [], 'inventoryItem');
                }
            }
        }

        // ---- Update existing variants (prices, compareAtPrice, barcode) ----
        if (!empty($updateInputs)) {
            $updateMutation = <<<'GQL'
            mutation ProductVariantsUpdate($productId: ID!, $variants: [ProductVariantsBulkInput!]!) {
              productVariantsBulkUpdate(productId: $productId, variants: $variants) {
                productVariants {
                  id
                  price
                  compareAtPrice
                  barcode
                }
                userErrors { field message }
              }
            }
            GQL;

            $updateResp = $this->gql($updateMutation, [
                'productId' => $productGid,
                'variants' => $updateInputs,
            ]);

            $this->throwIfUserErrors($updateResp['productVariantsBulkUpdate']['userErrors'] ?? [], 'updateVariants');
        }
    }


    /** Update variant core fields (not inventory). */
    protected function updateProductVariants(string $productGid, array $variants): array
    {
        $mutation = <<<'GQL'
            mutation productVariantsBulkUpdate($productId: ID!, $variants: [ProductVariantsBulkInput!]!) {
              productVariantsBulkUpdate(productId: $productId, variants: $variants) {
                product {
                  id
                  title
                  variants(first: 50) {
                    edges {
                      node {
                        id
                        legacyResourceId
                        title
                        barcode
                        price
                        inventoryQuantity
                      }
                    }
                  }
                }
                userErrors {
                  field
                  message
                }
              }
            }
            GQL;

        // Build variant input according to schema
        $variantInputs = [];
        foreach ($variants as $variant) {
            $variantInputs[] = array_filter([
                'id' => $variant['id'],
//                'sku' => $variant['sku'] ?? null,
                'barcode' => $variant['barcode'] ?? null,
                'price' => $variant['price'] ?? null,
                'compareAtPrice' => $variant['compare_at_price'] ?? null,
                'inventoryPolicy' => $variant['inventory_policy'] ?? 'DENY',
//                'requiresShipping' => $variant['requires_shipping'] ?? true,
                'taxable' => $variant['taxable'] ?? true,
//                'weight' => $variant['weight'] ?? null,
//                'weightUnit' => $variant['weight_unit'] ?? 'KILOGRAMS',
            ], fn($v) => !is_null($v)); // remove nulls
        }

        $data = $this->gql($mutation, [
            'productId' => $productGid,
            'variants' => $variantInputs,
        ]);

        if (!empty($data['data']['productVariantsBulkUpdate']['userErrors'])) {
            $errors = [];
            foreach ($data['data']['productVariantsBulkUpdate']['userErrors'] as $err) {
                $errors[$err['field'][0] ?? 'variant'][] = $err['message'];
            }
            throw ValidationException::withMessages($errors);
        }

        return $data['data']['productVariantsBulkUpdate']['product']['variants']['edges'] ?? [];
    }

    /** Set absolute "available" quantity for a variant at a location using inventorySetQuantities. */
    protected function setVariantInventoryAbsolute(array $variantPayload, int $quantity, ?string $locationId = null): void
    {
        // 1) get variant gid
        $variantGid = $this->toGid('ProductVariant', $variantPayload['id']);

        // 2) get inventoryItemId for the variant
        $query = <<<'GQL'
        query VariantInvItem($id: ID!) {
          productVariant(id: $id) {
            id
            inventoryItem { id }
          }
        }
        GQL;

        $data = $this->gql($query, ['id' => $variantGid]);
        $inventoryItemId = Arr::get($data, 'productVariant.inventoryItem.id');
        if (!$inventoryItemId) return;

        // 3) resolve a location (default to primary)
        if (!$locationId) {
            // NOTE: as of 2024-10+, you can query `location` with no ID to get the primary; locations() also works.
            $locQ = <<<'GQL'
            query PrimaryLocation {
              locations(first: 1) {
                edges { node { id } }
              }
            }
            GQL;
            $ld = $this->gql($locQ);
            $locationId = Arr::get($ld, 'locations.edges.0.node.id');
        }

        if (!$locationId) return;

        // 4) set absolute quantity
        $mutation = <<<'GQL'
        mutation InventorySet($input: InventorySetQuantitiesInput!) {
          inventorySetQuantities(input: $input) {
            inventoryAdjustmentGroup { createdAt reason }
            userErrors { field message }
          }
        }
        GQL;

        $vars = [
            'input' => [
                'name' => 'available',
                'reason' => 'correction',
                'quantities' => [[
                    'inventoryItemId' => $inventoryItemId,
                    'locationId' => $locationId,
                    'quantity' => (int)$quantity,
                    // optionally include compareQuantity if you maintain CAS
                ]]
            ]
        ];

        $res = $this->gql($mutation, $vars);
        $this->throwIfUserErrors($res['inventorySetQuantities']['userErrors'] ?? [], 'inventory');
    }

    /* end: Products Management */

    /** Map Shopify GraphQL user errors into ValidationException */
    protected function throwIfUserErrors(array $userErrors, string $prefix = 'shopify'): void
    {
        if (empty($userErrors)) return;

        $errors = [];
        foreach ($userErrors as $e) {
            $field = is_array($e['field'] ?? null) ? implode('.', $e['field']) : $prefix;
            $msg = $e['message'] ?? 'Unknown error';
            $errors[$field] = [$msg];
        }
        throw ValidationException::withMessages($errors);
    }

    /** Convert top-level GraphQL errors (and HTTP failures) into ValidationException where sensible */
    protected function throwShopifyValidationIfPossible(array $json, int $status): void
    {
        // Top-level GraphQL errors
        if (!empty($json['errors']) && is_array($json['errors'])) {
            $errors = [];
            foreach ($json['errors'] as $e) {
                $path = isset($e['path']) ? implode('.', (array)$e['path']) : 'shopify';
                $msg = $e['message'] ?? 'GraphQL Error';
                $errors[$path] = [$msg];
            }
            if (!empty($errors)) {
                throw ValidationException::withMessages($errors);
            }
        }

        // Fallback on HTTP 4xx/5xx: push into a generic field
        if ($status >= 400) {
            throw ValidationException::withMessages([
                'shopify' => [json_encode($json, JSON_UNESCAPED_UNICODE)]
            ]);
        }
    }

}
