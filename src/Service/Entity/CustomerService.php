<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\Entity\Address;
use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;

class CustomerService
{
    public function getOrCreateCustomer(array $deliveryInfo, Address $address, EntityManagerInterface $em): Customer
    {
        $customer = $this->findCustomerByEmail($deliveryInfo, $em);
        if (! $customer) {
            $customer = $this->createCustomer($deliveryInfo, $address, $em);
        }
        return $customer;
    }

    private function findCustomerByEmail(array $deliveryInfo, EntityManagerInterface $em): ?Customer
    {
        return $em->getRepository(Customer::class)->findOneBy(
            [
                'email' => $deliveryInfo['email'] ?? null,
            ]
        );
    }

    private function createCustomer(array $deliveryInfo, Address $address, EntityManagerInterface $em): Customer
    {
        $customer = new Customer();
        $customer->setEmail($deliveryInfo['email'] ?? null);
        $customer->setFirstName($deliveryInfo['firstName'] ?? null);
        $customer->setLastName($deliveryInfo['lastName'] ?? null);
        $customer->setPhoneNumber($deliveryInfo['phone'] ?? null);
        $customer->setAddress($address);
        $em->persist($customer);
        return $customer;
    }
}
