<?php

namespace App\Service;

class ApiService
{
    public function fetchProductsWithFilters(array|null $filters = [],int $page = 1, int $limit = 10): array
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
            $products = array_filter(
                $products, function ($product) use ($filters) {
                    return $product['price'] >= $filters['minPrice'];
                }
            );
        }

        if (isset($filters['maxPrice'])) {
            $products = array_filter(
                $products, function ($product) use ($filters) {
                    return $product['price'] <= $filters['maxPrice'];
                }
            );
        }

        if (isset($filters['name'])) {
            $products = array_filter(
                $products, function ($product) use ($filters) {
                    return stripos($product['name'], $filters['name']) !== false;
                }
            );
        }

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
        $uniqueCategories = array_unique(
            array_map(
                function ($product) {
                    return $product['category'] ?? null;
                }, $this->fetchProductsWithFilters([], 1, 1000)['items']
            )
        );
        return array_filter($uniqueCategories);
    }

    public function fetchProductById(string $id): array|null
    {
        $url = 'http://localhost:3000/products?id=' . $id;
        $response = file_get_contents($url);

        if ($response === false) {
            return null;
        }

        $product = json_decode($response, true);
        return $product ?: null;
    }

    public function getDeliveryMethodsWithCorrectWeight(int $orderTotalWeight): array
    {
        $weightInKg = $orderTotalWeight / 1000;
        $url = 'http://localhost:3000/carriers?max-weight_gte=' . $weightInKg;
        $response = file_get_contents($url);

        if ($response === false) {
            return [];
        }

        $deliveryMethods = json_decode($response, true);
        return array_values($deliveryMethods);
    }

    public function getDeliveryMethodById(string $id): array|null
    {
        $url = 'http://localhost:3000/carriers?id=' . $id;
        $response = file_get_contents($url);

        if ($response === false) {
            return null;
        }

        $deliveryMethod = json_decode($response, true);
        return $deliveryMethod ?: null;
    }

    public function getPaymentMethods(): array
    {
        $url = 'http://localhost:3000/payments';
        $response = file_get_contents($url);

        if ($response === false) {
            return [];
        }

        $paymentMethods = json_decode($response, true);
        return $paymentMethods ?: [];
    }
}
