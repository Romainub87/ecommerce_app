<?php

declare(strict_types=1);

namespace Tests\Unit\Cart;

use App\Service\Cart\CartStockService;
use App\Service\ProductService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

beforeEach(function (): void {
    $this->session = $this->createMock(SessionInterface::class);
    $this->productService = $this->createMock(ProductService::class);
    $this->cartStockService = new CartStockService($this->productService);;
});

it('vérifie l\'ajout au panier selon le stock', function (): void {
    $product = ['id' => '1'];
    $this->productService->method('getStockFromApi')->with('1')->willReturn(5);
    expect($this->cartStockService->addToCart($product, 5, $this->session))->toBeTrue()
        ->and($this->cartStockService->addToCart($product, 6, $this->session))->toBeFalse();
});
