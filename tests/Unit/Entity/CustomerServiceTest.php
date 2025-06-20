<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Service\Entity\CustomerService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Customer;
use App\Entity\Address;

uses(KernelTestCase::class);

beforeEach(function (): void {
    self::bootKernel();
    $this->em = self::$kernel->getContainer()->get('doctrine')->getManager();
    $this->customerService = new CustomerService();
});

test('getOrCreateCustomer retourne un nouveau client si l\'email n\'existe pas', function (): void {
    $deliveryInfo = [
        'email' => 'test@test.com',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'phone' => '0123456789',
    ];
    $address = new Address();
    $address
        ->setStreet('1 rue Test')
        ->setZipCode('75000')
        ->setCity('Paris')
        ->setCountry('France');
    $this->em->persist($address);
    $this->em->flush();
    $customer = $this->customerService->getOrCreateCustomer($deliveryInfo, $address, $this->em);
    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->getEmail())->toBe('test@test.com');
});
test('getOrCreateCustomer retourne le client existant si l\'email existe déjà', function (): void {
    $address = new Address();
    $address->setStreet('3 rue Client')
        ->setZipCode('34000')
        ->setCity('Montpellier')
        ->setCountry('France');
    $this->em->persist($address);
    $customer = new Customer();
    $customer->setEmail('exist@test.com')
        ->setFirstName('Alice')
        ->setLastName('Dupont')
        ->setPhoneNumber('0612345678')
        ->setAddress($address);
    $this->em->persist($customer);
    $this->em->flush();
    $deliveryInfo = [
        'email' => 'exist@test.com',
        'firstName' => 'Alice',
        'lastName' => 'Dupont',
        'phone' => '0612345678',
    ];
    $found = $this->customerService->getOrCreateCustomer($deliveryInfo, $address, $this->em);
    expect($found->getId())->toBe($customer->getId());
});
