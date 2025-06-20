<?php

declare(strict_types=1);

namespace App\Service\Order;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class OrderItemService
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @throws Exception|GuzzleException
     */
    public function addOrderItems(Order $order, array $cart, EntityManagerInterface $em): void
    {
        foreach ($cart as $product) {
            $item = new OrderItem();
            $item->setProductId($product['product']['id']);
            $item->setQuantity($product['quantity']);
            $item->setUnitPrice($product['product']['price']);
            $item->setTotalPrice($product['quantity'] * $product['product']['price']);
            $item->setOrderId($order);
            $this->productService->decrementStockById($product['product']['id'], $product['quantity']);
            $em->persist($item);
        }
    }
}
