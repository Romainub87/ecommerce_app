<?php

namespace App\Service;

class ApiService
{
    public function fetchProductsWithFilters(array|null $filters = [], int $page = 1, int $limit = 10): array
    {
        $products = $this->fetchProductsFromApi($filters);
        $products = $this->applyFilters($products, $filters);

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
        return $response === false ? null : (json_decode($response, true) ?: null);
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
        $url = $_ENV['JSONSERVER_URL'] . '/products';
        if (!empty($filters['category'])) {
            $url .= '?category=' . urlencode($filters['category']);
        }
        $response = file_get_contents($url);
        if ($response === false) {
            return [];
        }
        return json_decode($response, true);
    }

    private function applyFilters(array $products, array $filters): array
    {
        if (isset($filters['minPrice'])) {
            $products = $this->filterByMinPrice($products, $filters['minPrice']);
        }
        if (isset($filters['maxPrice'])) {
            $products = $this->filterByMaxPrice($products, $filters['maxPrice']);
        }
        if (isset($filters['name'])) {
            $products = $this->filterByName($products, $filters['name']);
        }
        return $products;
    }

    private function filterByMinPrice(array $products, $minPrice): array
    {
        return array_filter($products, function ($product) use ($minPrice) {
            return $product['price'] >= $minPrice;
        });
    }

    private function filterByMaxPrice(array $products, $maxPrice): array
    {
        return array_filter($products, function ($product) use ($maxPrice) {
            return $product['price'] <= $maxPrice;
        });
    }

    private function filterByName(array $products, $name): array
    {
        return array_filter($products, function ($product) use ($name) {
            return stripos($product['name'], $name) !== false;
        });
    }
}
