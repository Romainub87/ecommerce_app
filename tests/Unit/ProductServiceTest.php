<?php

use App\Service\ProductService;
use App\Service\ApiService;
use GuzzleHttp\Exception\ClientException;

beforeAll(function () {
    $GLOBALS['apiService'] = new ApiService();
    $GLOBALS['productService'] = new ProductService($GLOBALS['apiService']);
});

it('décrémente le stock via l\'API réelle', function () {
    $service = $GLOBALS['productService'];
    $productId = 'e0a1b2c3-d4e5-6f7a-8b9c-0d1e2f3a4b5c';
    $quantity = 1;

    $stockAvant = $service->getStockFromApi($productId);

    $service->decrementStockById($productId, $quantity);

    $stockApres = $service->getStockFromApi($productId);

    expect($stockApres)->toBe($stockAvant - $quantity);
});

it('lève une exception si le stock est insuffisant', function () {
    $service = $GLOBALS['productService'];
    $productId = 'e0a1b2c3-d4e5-6f7a-8b9c-0d1e2f3a4b5c';
    $quantity = 1000;

    expect(fn() => $service->decrementStockById($productId, $quantity))
        ->toThrow(Exception::class, "Stock insuffisant pour le produit ID: {$productId}");
});

it('lève une exception si le produit n\'existe pas', function () {
    $service = $GLOBALS['productService'];
    $productId = 'non-existent-id';
    $quantity = 1;

    expect(fn() => $service->decrementStockById($productId, $quantity))
        ->toThrow(Exception::class, "Produit non trouvé pour l'ID: {$productId}");
});
