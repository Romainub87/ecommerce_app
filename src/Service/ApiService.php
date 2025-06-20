<?php

declare(strict_types=1);

namespace App\Service;

class ApiService
{
    public function fetchProductsWithFilters(array|null $filters = [], int $page = 1, int $limit = 10): array
    {
        $products = $this->fetchProductsFromApi($filters);

        $totalProducts = count($products);
        $totalPages = ceil($totalProducts / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedProducts = array_slice($products, $offset, $limit);

        return [
            'items' => $paginatedProducts,
            'totalPages' => $totalPages,
        ];
    }

    public function fetchUniqueCategories(): array
    {
        $products = $this->fetchProductsWithFilters([], 1, 1000)['items'];
        $categories = array_column($products, 'category');
        return array_values(array_filter(array_unique($categories)));
    }

    public function fetchProductById(string $id): array|null
    {
        $response = file_get_contents($_ENV['JSONSERVER_URL'] . '/products?id=' . $id);
        $data = $response === false ? null : (json_decode($response, true) ?: null);
        return is_array($data) && isset($data[0]) ? $data[0] : null;
    }

    public function getDeliveryMethodsWithCorrectWeight(int $orderTotalWeight = 0): array
    {
        if ($orderTotalWeight < 0) {
            return [];
        }
        $url = $_ENV['JSONSERVER_URL'] . '/carriers?max-weight_gte=' . ($orderTotalWeight / 1000);
        $response = file_get_contents($url);
        return $response === false ? [] : array_values(json_decode($response, true));
    }

    public function getDeliveryMethodById(string $id): array|null
    {
        $response = file_get_contents($_ENV['JSONSERVER_URL'] . '/carriers?id=' . $id);
        return $response === false ? null : (json_decode($response, true) ?: null);
    }

    public function getPaymentMethods(): array
    {
        $response = file_get_contents($_ENV['JSONSERVER_URL'] . '/payments');
        return $response === false ? [] : (json_decode($response, true) ?: []);
    }

    private function fetchProductsFromApi(array $filters): array
    {
        $url = $this->buildProductsUrl($filters);
        $response = file_get_contents($url);
        if ($response === false) {
            return [];
        }
        $products = json_decode($response, true);
        if (! empty($filters['name'])) {
            return $this->filterByName($products, $filters['name']);
        }
        return $products;
    }

    private function buildProductsUrl(array $filters): string
    {
        $url = $_ENV['JSONSERVER_URL'] . '/products';
        $params = [];

        if (! empty($filters['category'])) {
            $params[] = 'category=' . urlencode($filters['category']);
        }
        if (isset($filters['minPrice']) || isset($filters['maxPrice'])) {
            $params[] = 'price_gte=' . ($filters['minPrice'] ?? 0);
            $params[] = 'price_lte=' . ($filters['maxPrice'] ?? PHP_INT_MAX);
        }
        if ($params) {
            $url .= '?' . implode('&', $params);
        }
        return $url;
    }

    private function filterByName(array $products, $name): array
    {
        return array_filter($products, static function ($product) use ($name) {
            return stripos($product['name'], $name) !== false;
        });
    }
}
