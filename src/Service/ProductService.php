<?php

declare(strict_types = 1);

namespace App\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ProductService
{

    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * @throws GuzzleException|Exception
     */
    public function decrementStockById(string $productId, int $quantity): void
    {
        $product = $this->getProductOrFail($productId);
        $currentStock = $this->getStockOrFail($productId, $quantity);
        $newStock = $currentStock - $quantity;
        $this->updateProductStock($product, $productId, $newStock);
    }

    /**
     * @throws GuzzleException|Exception
     */
    public function getStockFromApi(string $productId)
    {
        $client = new Client();
        $response = $client->get($_ENV['JSONSERVER_URL'] . "/products/{$productId}");

        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['stock'] ?? 0;
        }

        throw new Exception("Failed to fetch product stock for ID: {$productId}");
    }

    /**
     * @throws Exception
     */
    private function getProductOrFail(string $productId): array
    {
        $product = $this->apiService->fetchProductById($productId);
        if (!$product) {
            throw new Exception("Produit non trouvé pour l'ID: {$productId}");
        }
        return $product;
    }

    /**
     * @throws Exception|GuzzleException
     */
    private function getStockOrFail(string $productId, int $quantity): int
    {
        $currentStock = $this->getStockFromApi($productId);
        if ($currentStock < $quantity) {
            throw new Exception("Stock insuffisant pour le produit ID: {$productId}");
        }
        return $currentStock;
    }

    /**
     * @throws GuzzleException|Exception
     */
    private function updateProductStock($product, string $productId, int $newStock): void
    {
        $client = new Client();
        $product[0]['stock'] = $newStock;
        $response = $client->put(
            $_ENV['JSONSERVER_URL'] . "/products/{$productId}", [
                'json' => $product[0],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception("Échec de la mise à jour du stock pour le produit ID: {$productId}");
        }
    }
}
