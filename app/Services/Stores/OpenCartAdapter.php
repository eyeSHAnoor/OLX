<?php


namespace App\Adapters;

use App\Models\Store;
use App\Services\Stores\PlatformAdapterInterface;
use Illuminate\Support\Facades\Http;

class OpenCartAdapter implements PlatformAdapterInterface
{
    protected Store $store;
    protected array $creds;
    protected string $baseUrl;
    protected string $apiToken;

    public function __construct(Store $store)
    {
        $this->store = $store;
        $this->creds = decrypt($store->credentials); // AES-256 decrypted array
        $this->baseUrl = rtrim($this->creds['base_url'], '/');
    }

    /**
     * Authenticate with OpenCart API
     */
    protected function authenticate(): bool
    {
        $response = Http::asForm()->post("{$this->baseUrl}/index.php?route=api/login", [
            'username' => $this->creds['username'],
            'key' => $this->creds['key'],
        ]);

        if ($response->failed()) {
            return false;
        }

        $data = $response->json();
        if (!empty($data['token'])) {
            $this->apiToken = $data['token'];
            return true;
        }

        return false;
    }

    /**
     * Test if credentials are valid
     */
    public function testConnection(): bool
    {
        if (!$this->authenticate()) {
            return false;
        }

        // Try fetching one product
        $response = Http::get("{$this->baseUrl}/index.php?route=api/product", [
            'token' => $this->apiToken,
            'limit' => 1,
        ]);

        return $response->successful();
    }

    /**
     * Fetch orders in date range
     */
    public function fetchOrders(\DateTime $from, \DateTime $to): array
    {
        if (!$this->authenticate()) {
            return [];
        }

        $response = Http::get("{$this->baseUrl}/index.php?route=api/order", [
            'token' => $this->apiToken,
            'date_added_from' => $from->format('Y-m-d H:i:s'),
            'date_added_to' => $to->format('Y-m-d H:i:s'),
        ]);

        return $response->successful() ? $response->json()['orders'] ?? [] : [];
    }

    /**
     * Fetch products from OpenCart
     */
    public function fetchProducts(): array
    {
        if (!$this->authenticate()) {
            return [];
        }

        $response = Http::get("{$this->baseUrl}/index.php?route=api/product", [
            'token' => $this->apiToken,
            'limit' => 100, // adjust as needed
        ]);

        return $response->successful() ? $response->json()['products'] ?? [] : [];
    }

    public function normalizeOrder(array $platformOrderJson): array
    {
        // TODO: Implement normalizeOrder() method.
    }

    public function normalizeProduct(array $platformProductJson): array
    {
        // TODO: Implement normalizeProduct() method.
    }
}
