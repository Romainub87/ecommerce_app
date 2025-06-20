<?php

declare(strict_types=1);

use App\Service\ApiService;
use App\Service\ProductService;

beforeEach(function (): void {
    $this->apiService = new ApiService();
    $this->productService = new ProductService($this->apiService);
});

it('décrémente le stock avec produit et stock valide', function (): void {
    $productId = 'e0a1b2c3-d4e5-6f7a-8b9c-0d1e2f3a4b5c';
    $quantity = 1;

    $stockAvant = $this->productService->getStockFromApi($productId);

    $this->productService->decrementStockById($productId, $quantity);

    $stockApres = $this->productService->getStockFromApi($productId);

    expect($stockApres)->toBe($stockAvant - $quantity);
});

it('lève une exception si le stock est insuffisant', function (): void {
    $productId = 'e0a1b2c3-d4e5-6f7a-8b9c-0d1e2f3a4b5c';
    $quantity = 1000;

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Stock insuffisant pour le produit ID: {$productId}");

    $this->productService->decrementStockById($productId, $quantity);
});

it('lève une exception si le produit n\'existe pas', function (): void {
    $productId = 'non-existent-id';
    $quantity = 1;

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Produit non trouvé pour l'ID: {$productId}");

    $this->productService->decrementStockById($productId, $quantity);
});
