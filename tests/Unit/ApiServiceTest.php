<?php

use App\Service\ApiService;

beforeEach(function () {
    $this->service = new ApiService();
});

it('fetches products with filters minPrice and maxPrice', function () {
    $filters = ['minPrice' => 100, 'maxPrice' => 500];
    $result = $this->service->fetchProductsWithFilters($filters, 1, 10);
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['items', 'totalPages'])
        ->and($result['items'])->toBeArray();
});

it('fetches products with filters category', function () {
    $filters = ['category' => 'pantalon'];
    $result = $this->service->fetchProductsWithFilters($filters, 1, 10);
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['items', 'totalPages'])
        ->and($result['items'])->toBeArray();

    foreach ($result['items'] as $item) {
        expect($item['category'])->toBe('pantalon');
    }
});

it('fetches products with filters minPrice, maxPrice and category', function () {
    $filters = ['minPrice' => 100, 'maxPrice' => 500, 'category' => 'pantalon'];
    $result = $this->service->fetchProductsWithFilters($filters, 1, 10);
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['items', 'totalPages'])
        ->and($result['items'])->toBeArray();

    foreach ($result['items'] as $item) {
        expect($item['category'])->toBe('pantalon')
            ->and($item['price'])->toBeGreaterThanOrEqual(100)
            ->and($item['price'])->toBeLessThanOrEqual(500);
    }
});

it('fetches products with filter name', function () {
    $filters = ['name' => 'jean'];
    $result = $this->service->fetchProductsWithFilters($filters, 1, 10);
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['items', 'totalPages'])
        ->and($result['items'])->toBeArray();

    foreach ($result['items'] as $item) {
        expect(stripos($item['name'], 'jean'))->not()->toBeFalse();
    }
});

it('fetches products with no filters', function () {
    $result = $this->service->fetchProductsWithFilters([], 1, 10);
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['items', 'totalPages'])
        ->and($result['items'])->toBeArray();
});

it('fetches unique categories', function () {
    $categories = $this->service->fetchUniqueCategories();
    expect($categories)->toBeArray()
        ->and($categories)->toHaveLength(2);
});

it('fetches product with incorrect id', function () {
    $product = $this->service->fetchProductById('1');
    expect($product)->toBeNull();
});

it('fetches product with correct id', function () {
    $product = $this->service->fetchProductById('e0a1b2c3-d4e5-6f7a-8b9c-0d1e2f3a4b5c');
    expect($product)->toBeArray()
        ->and($product)->toHaveLength(1);
});

it('gets delivery method with incorrect id', function () {
    $method = $this->service->getDeliveryMethodById('1');
    expect($method)->toBeNull();
});

it('gets delivery method with correct id', function () {
    $method = $this->service->getDeliveryMethodById('trk001');
    expect($method)->toBeArray();
});

it('gets all payment methods', function () {
    $methods = $this->service->getPaymentMethods();
    expect($methods)->toBeArray()
        ->and($methods)->toHaveLength(4);
});
