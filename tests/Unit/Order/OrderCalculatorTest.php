<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Service\Order\OrderTotalCalculator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

uses(KernelTestCase::class);

beforeEach(function (): void {
    self::bootKernel();
    $this->em = self::$kernel->getContainer()->get('doctrine')->getManager();
    $this->orderCalculator = new OrderTotalCalculator();
});

it('calcule le total de la commande sans frais de port', function (): void {
    $cart = [
        [
            'quantity' => 2,
            'product' => ['id' => '1', 'price' => 10.0],
        ],
        [
            'quantity' => 1,
            'product' => ['id' => '2', 'price' => 20.0],
        ],
    ];
    $total = $this->orderCalculator->getOrderTotal($cart);
    expect($total)->toBe(40.0);
});
it('calcule le total de la commande avec frais de port', function (): void {
    $cart = [
        [
            'quantity' => 1,
            'product' => ['id' => '1', 'price' => 15.0],
        ],
    ];
    $total = $this->orderCalculator->getOrderTotal($cart, 5.0);
    expect($total)->toBe(20.0);
});
it('calcule le total de la commande avec frais de port négatifs', function (): void {
    $cart = [
        [
            'quantity' => 1,
            'product' => ['id' => '1', 'price' => 15.0],
        ],
    ];
    $total = $this->orderCalculator->getOrderTotal($cart, -5.0);
    expect($total)->toBe(15.0);
});
