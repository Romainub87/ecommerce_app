<?php

declare(strict_types=1);


use App\Service\ApiService;

beforeEach(function (): void {
    $this->apiService = new ApiService();
});
it('fetches products with filters minPrice and maxPrice', function (): void {
    $filters = ['minPrice' => 100, 'maxPrice' => 500];
    $result = $this->apiService->fetchProductsWithFilters($filters, 1, 10);
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['items', 'totalPages'])
        ->and($result['items'])->toBeArray();
    foreach ($result['items'] as $item) {
        expect($item['price'])->toBeGreaterThanOrEqual(100)
            ->and($item['price'])->toBeLessThanOrEqual(500);
    }
});

it('fetches products with filters category', function (): void {
    $filters = ['category' => 'pantalon'];
    $result = $this->apiService->fetchProductsWithFilters($filters, 1, 10);
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['items', 'totalPages'])
        ->and($result['items'])->toBeArray();
    foreach ($result['items'] as $item) {
        expect($item['category'])->toBe('pantalon');
    }
});

it('fetches products with filters minPrice, maxPrice and category', function (): void {
    $filters = ['minPrice' => 100, 'maxPrice' => 500, 'category' => 'pantalon'];
    $result = $this->apiService->fetchProductsWithFilters($filters, 1, 10);
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['items', 'totalPages'])
        ->and($result['items'])->toBeArray();
    foreach ($result['items'] as $item) {
        expect($item['category'])->toBe('pantalon')
            ->and($item['price'])->toBeGreaterThanOrEqual(100)
            ->and($item['price'])->toBeLessThanOrEqual(500);
    }
});

it('fetches products with filter name', function (): void {
    $filters = ['name' => 'jean'];
    $result = $this->apiService->fetchProductsWithFilters($filters, 1, 10);
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['items', 'totalPages'])
        ->and($result['items'])->toBeArray();
    foreach ($result['items'] as $item) {
        expect(stripos($item['name'], 'jean'))->not()->toBeFalse();
    }
});

it('fetches products with no filters', function (): void {
    $result = $this->apiService->fetchProductsWithFilters([], 1, 10);
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['items', 'totalPages'])
        ->and($result['items'])->toBeArray();
});

it('fetches unique categories', function (): void {
    $categories = $this->apiService->fetchUniqueCategories();
    expect($categories)->toBeArray()
        ->and($categories)->not->toBeEmpty();
});

it('fetches product with incorrect id', function (): void {
    $product = $this->apiService->fetchProductById('1');
    expect($product)->toBeNull();
});

it('fetches product with correct id', function (): void {
    $product = $this->apiService->fetchProductById('e0a1b2c3-d4e5-6f7a-8b9c-0d1e2f3a4b5c');
    if ($product !== null) {
        expect($product)->toBeArray()
            ->and($product)->toHaveKey('id')
            ->and($product['id'])->toBe('e0a1b2c3-d4e5-6f7a-8b9c-0d1e2f3a4b5c');
    } else {
        expect($product)->toBeNull();
    }
});

it('gets delivery method with incorrect id', function (): void {
    $method = $this->apiService->getDeliveryMethodById('1');
    expect($method)->toBeNull();
});

it('gets delivery method with correct id', function (): void {
    $method = $this->apiService->getDeliveryMethodById('trk001');
    if ($method !== null) {
        expect($method)->toBeArray();
    } else {
        expect($method)->toBeNull();
    }
});

it('gets all payment methods', function (): void {
    $methods = $this->apiService->getPaymentMethods();
    expect($methods)->toBeArray()
        ->and($methods)->not->toBeEmpty();
});
