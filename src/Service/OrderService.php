<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{

    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function getOrCreateAddress(array $deliveryInfo, EntityManagerInterface $em): Address
    {
        $address = $em->getRepository(Address::class)->findOneBy(
            [
            'street' => $deliveryInfo['address'],
            'zipCode' => $deliveryInfo['postalCode']
            ]
        );
        if (!$address) {
            $address = new Address();
            $address->setStreet($deliveryInfo['address'] ?? null);
            $address->setZipCode($deliveryInfo['postalCode'] ?? null);
            $address->setCity($deliveryInfo['city'] ?? null);
            $address->setCountry($deliveryInfo['country'] ?? null);
            $em->persist($address);
        }
        return $address;
    }

    public function getOrCreateCustomer(array $deliveryInfo, Address $address, EntityManagerInterface $em): Customer
    {
        $customer = $em->getRepository(Customer::class)->findOneBy(
            [
            'email' => $deliveryInfo['email'] ?? null
            ]
        );
        if (!$customer) {
            $customer = new Customer();
            $customer->setEmail($deliveryInfo['email'] ?? null);
            $customer->setFirstName($deliveryInfo['firstName'] ?? null);
            $customer->setLastName($deliveryInfo['lastName'] ?? null);
            $customer->setPhoneNumber($deliveryInfo['phone'] ?? null);
            $customer->setAddress($address);
            $em->persist($customer);
        }
        return $customer;
    }

    public function createOrder($deliveryMethod, $paymentMethod, Customer $customer, array $cart, EntityManagerInterface $em): Order
    {
        $order = new Order();
        $order->setStatus('confirmed');
        $order->setCarrierId($deliveryMethod['id'] ?? null);
        $order->setPaymentId($paymentMethod);
        $order->setCustomer($customer);
        $order->setOrderDate(new \DateTimeImmutable());
        $em->persist($order);

        foreach ($cart as $product) {
            $item = new OrderItem();
            $item->setProductId($product['product'][0]['id']);
            $item->setQuantity($product['quantity']);
            $item->setUnitPrice($product['product'][0]['price']);
            $item->setTotalPrice($product['quantity'] * $product['product'][0]['price']);
            $item->setOrderId($order);
            $this->productService->decrementStockById($product['product'][0]['id'], $product['quantity']);
            $em->persist($item);
        }

        $orderTotal = array_reduce(
            $cart, function ($carry, $item) {
                return $carry + ($item['quantity'] * $item['product'][0]['price']);
            }, 0
        );
        $order->setOrderTotal($orderTotal);

        return $order;
    }

    public function calculateShippingFees(array $deliveryMethods, float $orderTotalWeight, float $orderTotalPrice): array
    {
        foreach ($deliveryMethods as &$method) {
            $maxWeight = $method['max-weight'];
            $baseFee = 0.1 * $maxWeight;
            $percentFee = 0.02 * $orderTotalPrice;
            $weightRatio = $maxWeight > 0 ? ($orderTotalWeight / ($maxWeight * 1000)) : 0;
            $extraFee = ($weightRatio > 0.8 && $maxWeight > 0) ? 3 * $weightRatio : 0.0;
            $method['shipping_fee'] = round($baseFee + $percentFee + $extraFee, 2);
        }
        return $deliveryMethods;
    }
}
