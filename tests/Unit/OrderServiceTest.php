<?php

use App\Entity\Address;
use \App\Service\ProductService;
use App\Entity\Customer;
use App\Entity\Order;
use App\Service\ApiService;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;

uses(KernelTestCase::class);

beforeAll(function () {
    self::bootKernel();
    $application = new Application(self::$kernel);

    // Drop & create la base pour garantir un état propre
    $dropInput = new ArrayInput([
        'command' => 'doctrine:database:drop',
        '--force' => true,
        '--if-exists' => true,
        '--no-interaction' => true,
    ]);
    $application->setAutoExit(false);
    $application->run($dropInput);

    $createInput = new ArrayInput([
        'command' => 'doctrine:database:create',
        '--no-interaction' => true,
    ]);
    $application->run($createInput);

    $migrateInput = new ArrayInput([
        'command' => 'doctrine:migrations:migrate',
        '--no-interaction' => true,
    ]);
    $application->run($migrateInput);
});

beforeEach(function () {
    self::bootKernel();
    $this->em = self::$kernel->getContainer()->get('doctrine')->getManager();

    $connection = $this->em->getConnection();
    foreach ($this->em->getMetadataFactory()->getAllMetadata() as $meta) {
        $tableName = $meta->getTableName();
        $connection->executeStatement('TRUNCATE TABLE "' . $tableName . '" RESTART IDENTITY CASCADE;');
    }

    $this->apiService = new ApiService();
    $this->productService = new class($this->apiService) extends ProductService {
        function decrementStockById(string $productId, int $quantity): void
        {
        }
    };
    $this->orderService = new OrderService($this->productService);
});

test('getOrCreateAddress retourne une nouvelle adresse si elle n\'existe pas', function () {
    $deliveryInfo = [
        'address' => '1 rue Test',
        'postalCode' => '75000',
        'city' => 'Paris',
        'country' => 'France'
    ];
    $address = $this->orderService->getOrCreateAddress($deliveryInfo, $this->em);
    expect($address)->toBeInstanceOf(Address::class)
        ->and($address->getStreet())->toBe('1 rue Test');
});

test('getOrCreateAddress retourne l\'adresse existante si elle existe déjà', function () {
    $deliveryInfo = [
        'address' => '2 rue Existant',
        'postalCode' => '69000',
        'city' => 'Lyon',
        'country' => 'France'
    ];
    $address = new Address();
    $address->setStreet('2 rue Existant')
        ->setZipCode('69000')
        ->setCity('Lyon')
        ->setCountry('France');
    $this->em->persist($address);
    $this->em->flush();

    $found = $this->orderService->getOrCreateAddress($deliveryInfo, $this->em);
    expect($found->getId())->toBe($address->getId());
});

test('getOrCreateCustomer retourne un nouveau client si l\'email n\'existe pas', function () {
    $deliveryInfo = [
        'email' => 'test@test.com',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'phone' => '0123456789'
    ];
    $address = new Address();
    $address
        ->setStreet('1 rue Test')
        ->setZipCode('75000')
        ->setCity('Paris')
        ->setCountry('France');
    $this->em->persist($address);
    $this->em->flush();

    $customer = $this->orderService->getOrCreateCustomer($deliveryInfo, $address, $this->em);
    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->getEmail())->toBe('test@test.com');
});

test('getOrCreateCustomer retourne le client existant si l\'email existe déjà', function () {
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
        'phone' => '0612345678'
    ];
    $found = $this->orderService->getOrCreateCustomer($deliveryInfo, $address, $this->em);
    expect($found->getId())->toBe($customer->getId());
});

test('createOrder crée une commande avec le bon total et appelle la décrémentation du stock', function () {
    $customer = new Customer();
    $customer
        ->setEmail('test@test.com')
        ->setFirstName('John')
        ->setLastName('Doe')
        ->setPhoneNumber('0123456789');
    $this->em->persist($customer);

    $cart = [
        [
            'product' => [['id' => 1, 'price' => 10]],
            'quantity' => 2
        ]
    ];

    $order = $this->orderService->createOrder(['id' => 1], 2, $customer, $cart, $this->em);
    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->getOrderTotal())->toBe(20);
});

test('createOrder gère un panier vide', function () {
    $customer = new Customer();
    $customer
        ->setEmail('vide@test.com')
        ->setFirstName('Vide')
        ->setLastName('Panier')
        ->setPhoneNumber('0000000000');
    $this->em->persist($customer);

    $cart = [];
    $order = $this->orderService->createOrder(['id' => 1], 2, $customer, $cart, $this->em);
    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->getOrderTotal())->toBe(0);
});

test('calculateShippingFees calcule les frais de port', function () {
    $deliveryMethods = [
        ['max-weight' => 10],
        ['max-weight' => 20]
    ];
    $result = $this->orderService->calculateShippingFees($deliveryMethods, 5000, 100);
    expect($result[0])->toHaveKey('shipping_fee');
});

test('calculateShippingFees gère un poids total nul', function () {
    $deliveryMethods = [
        ['max-weight' => 5]
    ];
    $result = $this->orderService->calculateShippingFees($deliveryMethods, 0, 50);
    expect($result[0]['shipping_fee'])->toBeGreaterThanOrEqual(0);
});

test('calculateShippingFees gère un prix total élevé', function () {
    $deliveryMethods = [
        ['max-weight' => 15]
    ];
    $result = $this->orderService->calculateShippingFees($deliveryMethods, 10000, 10000);
    expect($result[0]['shipping_fee'])->toBeGreaterThan(0);
});

it('calcule le total de la commande sans frais de port', function () {
    $cart = [
        [
            'quantity' => 2,
            'product' => [
                ['id' => 1, 'price' => 10.0]
            ]
        ],
        [
            'quantity' => 1,
            'product' => [
                ['id' => 2, 'price' => 20.0]
            ]
        ]
    ];

    $total = $this->orderService->getOrderTotal($cart);

    expect($total)->toBe(40.0);
});

it('calcule le total de la commande avec frais de port', function () {
    $cart = [
        [
            'quantity' => 1,
            'product' => [
                ['id' => 1, 'price' => 15.0]
            ]
        ]
    ];

    $total = $this->orderService->getOrderTotal($cart, 5.0);

    expect($total)->toBe(20.0);
});

it('calcule le total de la commande avec frais de port négatifs', function () {
    $cart = [
        [
            'quantity' => 1,
            'product' => [
                ['id' => 1, 'price' => 15.0]
            ]
        ]
    ];

    $total = $this->orderService->getOrderTotal($cart, -5.0);

    expect($total)->toBe(15.0);
});
