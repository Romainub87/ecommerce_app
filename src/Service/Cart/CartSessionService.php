<?php

declare(strict_types=1);

namespace App\Service\Cart;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartSessionService
{
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
}
