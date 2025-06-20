<?php

declare(strict_types=1);

namespace App\Service\Cart;

use App\Service\ProductService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartStockService
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @throws GuzzleException|Exception
     */
    public function addToCart($product, int $quantity, SessionInterface $session): bool
    {
        $cart = $session->get('cart', []);
        $productId = $product['id'];

        if ($this->checkStock($product, ($cart[$productId]['quantity'] ?? 0) + $quantity)) {
            $cart[$productId] = [
                'product' => $product,
                'quantity' => ($cart[$productId]['quantity'] ?? 0) + $quantity,
            ];
            $session->set('cart', $cart);
            return true;
        }
        return false;
    }

    /**
     * @throws Exception|GuzzleException
     */
    public function checkStock($product, int $quantity): bool
    {
        $actualStock = $this->productService->getStockFromApi($product['id']);
        return isset($product) && $actualStock >= $quantity;
    }
}
