<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Cart\CartService;
use App\Service\Order\ShippingFeeCalculator;

class DeliveryService
{
    private ApiService $apiService;
    private ShippingFeeCalculator $shippingFeeCalculator;
    private CartService $cartservice;

    public function __construct(
        ApiService $apiService,
        ShippingFeeCalculator $shippingFeeCalculator,
        CartService $cartservice
    ) {
        $this->apiService = $apiService;
        $this->shippingFeeCalculator = $shippingFeeCalculator;
        $this->cartservice = $cartservice;
    }

    /**
     * Retourne les méthodes de livraison disponibles, le poids total et le prix total.
     */
    public function getAvailableDeliveryMethods(array $cart): array
    {
        $orderTotalWeight = $this->cartservice->getCartTotalWeight($cart);
        $orderTotalPrice = $this->cartservice->getCartTotalPrice($cart);
        $deliveryMethods = $this->apiService->getDeliveryMethodsWithCorrectWeight($orderTotalWeight);

        return $this->shippingFeeCalculator->calculateShippingFees($deliveryMethods, $orderTotalWeight, $orderTotalPrice);
    }

    /**
     * Trouve une méthode de livraison par son ID.
     */
    public function findDeliveryMethodById(array $deliveryMethods, $selectedMethod)
    {
        foreach ($deliveryMethods as $method) {
            if ($method['id'] === $selectedMethod) {
                return $method;
            }
        }
        return null;
    }
}
