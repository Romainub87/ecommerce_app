<?php

namespace App\Service;

class ApiService
{
    public function fetchProductsWithFilters(array|null $filters = []): array
    {
        $url = 'http://localhost:3000/products';

        if (!empty($filters['category'])) {
            $url .= '?category=' . urlencode($filters['category']);
        }
        $response = file_get_contents($url);

        if ($response === false) {
            return [];
        }
        $products = json_decode($response, true);

        if (isset($filters['minPrice'])) {
            $products = array_filter($products, function ($product) use ($filters) {
                return $product['price'] >= $filters['minPrice'];
            });
        }

        if (isset($filters['maxPrice'])) {
            $products = array_filter($products, function ($product) use ($filters) {
                return $product['price'] <= $filters['maxPrice'];
            });
        }

        if (isset($filters['name'])) {
            $products = array_filter($products, function ($product) use ($filters) {
                return stripos($product['name'], $filters['name']) !== false;
            });
        }

        return array_values($products);
    }

    public function fetchUniqueCategories(): array
    {
        $uniqueCategories = array_unique(array_map(function ($product) {
            return $product['category'] ?? null;
        }, $this->fetchProductsWithFilters([])));
        return array_filter($uniqueCategories);
    }
}
