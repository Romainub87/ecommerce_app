<?php

declare(strict_types=1);

namespace App\Service\Order;

class ShippingFeeCalculator
{
    private const BASE_FEE_RATE = 0.1;
    private const PERCENT_FEE_RATE = 0.02;
    private const EXTRA_FEE_THRESHOLD = 0.8;
    private const EXTRA_FEE_MULTIPLIER = 3;
    private const GRAMS_PER_KG = 1000;
    public function calculateShippingFees(array $deliveryMethods, float $orderTotalWeight, float $orderTotalPrice): array
    {
        foreach ($deliveryMethods as &$method) {
            $maxWeight = $method['max-weight'];
            $baseFee = self::BASE_FEE_RATE * $maxWeight;
            $percentFee = self::PERCENT_FEE_RATE * $orderTotalPrice;
            $weightRatio = $maxWeight > 0 ? $orderTotalWeight / ($maxWeight * self::GRAMS_PER_KG) : 0;
            $extraFee = $weightRatio > self::EXTRA_FEE_THRESHOLD && $maxWeight > 0 ? self::EXTRA_FEE_MULTIPLIER * $weightRatio : 0;
            $method['shipping_fee'] = round($baseFee + $percentFee + $extraFee, 2);
        }
        return $deliveryMethods;
    }
}
