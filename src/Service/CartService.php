<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function removeProduct($productId, SessionInterface $session)
    {
        $cart = $session->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $session->set('cart', $cart);
        }
    }

    public function decreaseQuantity($productId, SessionInterface $session)
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

    public function increaseQuantity($productId, SessionInterface $session)
    {
        $cart = $session->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
        }
        $session->set('cart', $cart);
    }

    public function checkStock($product, int $quantity): bool
    {
        $actualStock = $this->productService->getStockFromApi($product[0]["id"]);
        return isset($product) && $actualStock - $quantity >= 0 && $quantity <= $actualStock;
    }

}
