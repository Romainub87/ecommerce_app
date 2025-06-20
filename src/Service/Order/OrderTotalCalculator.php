<?php

declare(strict_types=1);

namespace App\Service\Order;

class OrderTotalCalculator
{
    public function getOrderTotal(array $cart, float $shippingFee = 0.0): float
    {
        $total = 0.0;

        foreach ($cart as $product) {
            $total += $product['quantity'] * $product['product']['price'];
        }

        if ($shippingFee >= 0) {
            return $total + $shippingFee;
        }
        return $total;
    }
}
