<?php

declare(strict_types=1);

namespace Tests\Unit\Cart;

use App\Service\Cart\CartCalculationService;

beforeEach(function (): void {
    $this->cartCalculationService = new CartCalculationService();
});

it("Calcul du total d'un panier valide", function (): void {
    $cart = [
        [
            'product' => ['price' => 10.5, 'id' => '1'],
            'quantity' => 2,
        ],
        [
            'product' => ['price' => 5.0, 'id' => '2'],
            'quantity' => 3,
        ],
    ];
    $total = $this->cartCalculationService->getCartTotalPrice($cart);
    expect($total)->toBe(10.5 * 2 + 5.0 * 3);
});
it('Calcul total panier pour un panier vide', function (): void {
    $cart = [];
    $total = $this->cartCalculationService->getCartTotalPrice($cart);
    expect($total)->toBe(0.0);
});
it('Calcul total panier non valide', function (): void {
    $cart = [
        [
            'product' => ['price' => 10.0, 'id' => '1'],
        ],
        [
            'quantity' => 2,
        ],
    ];
    $total = $this->cartCalculationService->getCartTotalPrice($cart);
    expect($total)->toBe(0.0);
});
