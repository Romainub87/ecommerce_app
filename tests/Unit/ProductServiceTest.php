<?php

declare(strict_types=1);

use App\Service\ApiService;
use App\Service\ProductService;

beforeEach(function (): void {
    $this->apiService = $this->getMockBuilder(ApiService::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['fetchProductById'])
        ->getMock();
    $this->productService = $this->getMockBuilder(ProductService::class)
        ->setConstructorArgs([$this->apiService])
        ->onlyMethods(['getStockFromApi'])
        ->getMock();
});

it('décrémente le stock via l\'API', function (): void {
    $productId = 'e0a1b2c3-d4e5-6f7a-8b9c-0d1e2f3a4b5c';
    $quantity = 1;
    $product = ['id' => $productId, 'stock' => 10];

    $this->apiService->method('fetchProductById')
        ->with($productId)
        ->willReturn($product);

    $this->productService->method('getStockFromApi')
        ->with($productId)
        ->willReturn(10);

    $this->productService->decrementStockById($productId, $quantity);
});

it('lève une exception si le stock est insuffisant', function (): void {
    $productId = 'e0a1b2c3-d4e5-6f7a-8b9c-0d1e2f3a4b5c';
    $quantity = 1000;
    $product = ['id' => $productId, 'stock' => 10];

    $this->apiService->method('fetchProductById')
        ->with($productId)
        ->willReturn($product);

    $this->productService->method('getStockFromApi')
        ->with($productId)
        ->willReturn(5);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Stock insuffisant pour le produit ID: {$productId}");

    $this->productService->decrementStockById($productId, $quantity);
});

it('lève une exception si le produit n\'existe pas (mock)', function (): void {
    $productId = 'non-existent-id';
    $quantity = 1;

    $this->apiService->method('fetchProductById')
        ->with($productId)
        ->willReturn(null);

    $service = new ProductService($this->apiService);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Produit non trouvé pour l'ID: {$productId}");

    $service->decrementStockById($productId, $quantity);
});
