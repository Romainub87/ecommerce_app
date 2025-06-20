<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;

class AddressService
{
    public function getOrCreateAddress(array $deliveryInfo, EntityManagerInterface $em): Address
    {
        $address = $this->findAddress($deliveryInfo, $em);
        if (! $address) {
            $address = $this->createAddress($deliveryInfo, $em);
        }
        return $address;
    }

    private function findAddress(array $deliveryInfo, EntityManagerInterface $em): ?Address
    {
        return $em->getRepository(Address::class)->findOneBy(
            [
                'street' => $deliveryInfo['address'],
                'zipCode' => $deliveryInfo['postalCode'],
            ]
        );
    }

    private function createAddress(array $deliveryInfo, EntityManagerInterface $em): Address
    {
        $address = new Address();
        $address->setStreet($deliveryInfo['address'] ?? null);
        $address->setZipCode($deliveryInfo['postalCode'] ?? null);
        $address->setCity($deliveryInfo['city'] ?? null);
        $address->setCountry($deliveryInfo['country'] ?? null);
        $em->persist($address);
        return $address;
    }
}
