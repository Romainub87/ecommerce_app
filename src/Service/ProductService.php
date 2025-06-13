<?php

namespace App\Service;

use GuzzleHttp\Client;

class ProductService
{

    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function decrementStockById(string $productId, int $quantity): void
    {
        $client = new Client();
        $currentStock = $this->getStockFromApi($productId);

        if ($currentStock < $quantity) {
            throw new \Exception("Stock insuffisant pour le produit ID: {$productId}");
        }

        $newStock = $currentStock - $quantity;
        $product = $this->apiService->fetchProductById($productId);
        if (!$product) {
            throw new \Exception("Produit non trouvé pour l'ID: {$productId}");
        }

        // Mise à jour du stock via l'API
        $product[0]['stock'] = $newStock;
        $response = $client->put(
            "http://localhost:3000/products/{$productId}", [
            'json' => $product[0],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Échec de la mise à jour du stock pour le produit ID: {$productId}");
        }
    }

    public function getStockFromApi(string $productId)
    {
        $client = new Client();
        $response = $client->get("http://localhost:3000/products/{$productId}");

        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['stock'] ?? 0;
        }

        throw new \Exception("Failed to fetch product stock for ID: {$productId}");
    }
}
