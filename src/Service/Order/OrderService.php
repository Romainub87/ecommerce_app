<?php

declare(strict_types=1);

namespace App\Service\Order;

use App\Entity\Customer;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class OrderService
{
    private OrderTotalCalculator $orderTotalCalculator;
    private OrderItemService $orderItemService;

    public function __construct(
        OrderTotalCalculator $orderTotalCalculator,
        OrderItemService $orderItemService
    ) {
        $this->orderTotalCalculator = $orderTotalCalculator;
        $this->orderItemService = $orderItemService;
    }

    /**
     * @throws Exception|GuzzleException
     */
    public function createOrder(array $deliveryMethod, string $paymentMethod, Customer $customer, array $cart, EntityManagerInterface $em): Order
    {
        $order = $this->initializeOrder($deliveryMethod, $paymentMethod, $customer, $em);
        $this->orderItemService->addOrderItems($order, $cart, $em);

        $orderTotal = $this->orderTotalCalculator->getOrderTotal($cart, $deliveryMethod['shipping_fee'] ?? 0.0);
        $order->setOrderTotal($orderTotal);

        $em->flush();

        return $order;
    }

    private function initializeOrder($deliveryMethod, $paymentMethod, Customer $customer, EntityManagerInterface $em): Order
    {
        $order = new Order();
        $order->setStatus('confirmed');
        $order->setCarrierId($deliveryMethod['id'] ?? null);
        $order->setPaymentId($paymentMethod);
        $order->setCustomer($customer);
        $order->setOrderDate(new \DateTimeImmutable());
        $em->persist($order);

        return $order;
    }
}
