<?php

declare(strict_types=1);

namespace App\Service\Cart;

class CartCalculationService
{
    public function getCartTotalPrice(array $cart): float
    {
        $total = 0.0;
        foreach ($cart as $item) {
            if (isset($item['product'], $item['quantity'])) {
                $total += $item['product']['price'] * $item['quantity'];
            }
        }
        return $total;
    }

    public function getCartTotalWeight(array $cart): int
    {
        $totalWeight = 0;
        foreach ($cart as $item) {
            if (isset($item['product'], $item['quantity'])) {
                $totalWeight += $item['product']['weight'] * $item['quantity'];
            }
        }
        return $totalWeight;
    }
}
