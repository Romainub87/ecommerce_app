<?php

use App\Service\CartService;
use App\Service\ProductService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

beforeEach(function () {
    $this->productService = $this->createMock(ProductService::class);
    $this->session = $this->createMock(SessionInterface::class);
    $this->cartService = new CartService($this->productService);
});

it('retire un produit du panier', function () {
    $cart = [1 => ['quantity' => 2], 2 => ['quantity' => 1]];
    $this->session->method('get')->with('cart', [])->willReturn($cart);
    $this->session->expects($this->once())->method('set')->with('cart', [2 => ['quantity' => 1]]);
    $this->cartService->removeProduct(1, $this->session);
});

it('diminue la quantité d\'un produit', function () {
    $cart = [1 => ['quantity' => 2]];
    $this->session->method('get')->with('cart', [])->willReturn($cart);
    $this->session->expects($this->once())->method('set')->with('cart', [1 => ['quantity' => 1]]);
    $this->cartService->decreaseQuantity(1, $this->session);
});

it('retire le produit si la quantité passe à zéro', function () {
    $cart = [1 => ['quantity' => 1]];
    $this->session->method('get')->with('cart', [])->willReturn($cart);
    $this->session->expects($this->once())->method('set')->with('cart', []);
    $this->cartService->decreaseQuantity(1, $this->session);
});

it('augmente la quantité d\'un produit', function () {
    $cart = [1 => ['quantity' => 1]];
    $this->session->method('get')->with('cart', [])->willReturn($cart);
    $this->session->expects($this->once())->method('set')->with('cart', [1 => ['quantity' => 2]]);
    $this->cartService->increaseQuantity(1, $this->session);
});

it('vérifie le stock', function () {
    $product = [['id' => 1]];
    $this->productService->method('getStockFromApi')->with(1)->willReturn(5);
    expect($this->cartService->checkStock($product, 3))->toBeTrue()
        ->and($this->cartService->checkStock($product, 6))->toBeFalse();
});

it("Calcul du total d'un panier valide", function () {
    $cart = [
        [
            'product' => [
                ['price' => 10.5, 'id' => 1]
            ],
            'quantity' => 2
        ],
        [
            'product' => [
                ['price' => 5.0, 'id' => 2]
            ],
            'quantity' => 3
        ]
    ];

    $total = $this->cartService->getCartTotal($cart);

    expect($total)->toBe(10.5 * 2 + 5.0 * 3);
});

it('Calcul total panier pour un panier vide', function () {
    $cart = [];
    $total = $this->cartService->getCartTotal($cart);

    expect($total)->toBe(0.0);
});

it('Calcul total panier non valide', function () {
    $cart = [
        [
            'product' => [
                ['price' => 10.0, 'id' => 1]
            ],
            // pas de quantity
        ],
        [
            'quantity' => 2
            // pas de product
        ]
    ];

    $total = $this->cartService->getCartTotal($cart);

    expect($total)->toBe(0.0);
});
