<?php

declare(strict_types = 1);

namespace App\Service;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function removeProduct($productId, SessionInterface $session): void
    {
        $cart = $session->get('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $session->set('cart', $cart);
        }
    }

    public function decreaseQuantity($productId, SessionInterface $session): void
    {
        $cart = $session->get('cart', []);
        if (isset($cart[$productId])) {
            if ($cart[$productId]['quantity'] > 1) {
                $cart[$productId]['quantity']--;
            } else {
                unset($cart[$productId]);
            }
            $session->set('cart', $cart);
        }
    }

    public function increaseQuantity($productId, SessionInterface $session): void
    {
        $cart = $session->get('cart', []);
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
            $session->set('cart', $cart);
        }
    }

    /**
     * @throws Exception|GuzzleException
     */
    public function checkStock($product, int $quantity): bool
    {
        $actualStock = $this->productService->getStockFromApi($product[0]["id"]);
        return isset($product) && $actualStock >= $quantity;
    }

    public function getCartTotal(array $cart): float
    {
        $total = 0.0;
        foreach ($cart as $item) {
            if (isset($item['product'], $item['quantity'])) {
                $total += $item['product'][0]['price'] * $item['quantity'];
            }
        }
        return $total;
    }
}
