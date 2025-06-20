<?php

namespace Tests\Unit;

use App\Entity\Address;
use App\Service\Entity\AddressService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

uses(KernelTestCase::class);

beforeEach(function (): void {
    self::bootKernel();
    $this->addressService = new AddressService();
    $this->em = self::$kernel->getContainer()->get('doctrine')->getManager();
});

test('getOrCreateAddress retourne une nouvelle adresse si elle n\'existe pas', function (): void {
    $deliveryInfo = [
        'address' => '1 rue Test',
        'postalCode' => '75000',
        'city' => 'Paris',
        'country' => 'France',
    ];
    $address = $this->addressService->getOrCreateAddress($deliveryInfo, $this->em);
    expect($address)->toBeInstanceOf(Address::class)
        ->and($address->getStreet())->toBe('1 rue Test');
});
test('getOrCreateAddress retourne l\'adresse existante si elle existe déjà', function (): void {
    $deliveryInfo = [
        'address' => '2 rue Existant',
        'postalCode' => '69000',
        'city' => 'Lyon',
        'country' => 'France',
    ];
    $address = new Address();
    $address->setStreet('2 rue Existant')
        ->setZipCode('69000')
        ->setCity('Lyon')
        ->setCountry('France');
    $this->em->persist($address);
    $this->em->flush();
    $found = $this->addressService->getOrCreateAddress($deliveryInfo, $this->em);
    expect($found->getId())->toBe($address->getId());
});
