<?php

declare(strict_types=1);

namespace Tests\Unit\Cart;

use App\Service\Cart\CartSessionService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

beforeEach(function (): void {
    $this->session = $this->createMock(SessionInterface::class);
    $this->cartSessionService = new CartSessionService();
});

it('retire un produit du panier', function (): void {
    $cart = ['1' => ['quantity' => 2], '2' => ['quantity' => 1]];
    $this->session->method('get')->with('cart', [])->willReturn($cart);
    $this->session->expects($this->once())->method('set')->with('cart', ['2' => ['quantity' => 1]]);
    $this->cartSessionService->removeProduct('1', $this->session);
});
it('diminue la quantité d\'un produit', function (): void {
    $cart = ['1' => ['quantity' => 2]];
    $this->session->method('get')->with('cart', [])->willReturn($cart);
    $this->session->expects($this->once())->method('set')->with('cart', ['1' => ['quantity' => 1]]);
    $this->cartSessionService->decreaseQuantity('1', $this->session);
});
it('retire le produit si la quantité passe à zéro', function (): void {
    $cart = ['1' => ['quantity' => 1]];
    $this->session->method('get')->with('cart', [])->willReturn($cart);
    $this->session->expects($this->once())->method('set')->with('cart', []);
    $this->cartSessionService->decreaseQuantity(1, $this->session);
});
it('augmente la quantité d\'un produit', function (): void {
    $cart = ['1' => ['quantity' => 1]];
    $this->session->method('get')->with('cart', [])->willReturn($cart);
    $this->session->expects($this->once())->method('set')->with('cart', ['1' => ['quantity' => 2]]);
    $this->cartSessionService->increaseQuantity('1', $this->session);
});
