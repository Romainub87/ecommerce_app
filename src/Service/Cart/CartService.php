<?php

declare(strict_types=1);

namespace App\Service\Cart;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private CartSessionService $cartSessionService;
    private CartCalculationService $cartCalculationService;
    private CartStockService $cartStockService;

    public function __construct(
        CartSessionService $cartSessionService,
        CartCalculationService $cartCalculationService,
        CartStockService $cartStockService
    ) {
        $this->cartSessionService = $cartSessionService;
        $this->cartCalculationService = $cartCalculationService;
        $this->cartStockService = $cartStockService;
    }

    public function removeProduct($productId, SessionInterface $session): void
    {
        $this->cartSessionService->removeProduct($productId, $session);
    }

    public function decreaseQuantity($productId, SessionInterface $session): void
    {
        $this->cartSessionService->decreaseQuantity($productId, $session);
    }

    public function increaseQuantity($productId, SessionInterface $session): void
    {
        $this->cartSessionService->increaseQuantity($productId, $session);
    }

    public function getCartTotalPrice(array $cart): float
    {
        return $this->cartCalculationService->getCartTotalPrice($cart);
    }

    public function getCartTotalWeight(array $cart): int
    {
        return $this->cartCalculationService->getCartTotalWeight($cart);
    }

    /**
     * @throws GuzzleException|Exception
     */
    public function addToCart($product, int $quantity, SessionInterface $session): bool
    {
        return $this->cartStockService->addToCart($product, $quantity, $session);
    }

    /**
     * @throws Exception|GuzzleException
     */
    public function checkStock($product, int $quantity): bool
    {
        return $this->cartStockService->checkStock($product, $quantity);
    }
}
