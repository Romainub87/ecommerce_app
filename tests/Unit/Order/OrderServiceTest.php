<?php

declare(strict_types=1);

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\Order;
use App\Service\ApiService;
use App\Service\Order\OrderItemService;
use App\Service\Order\OrderService;
use App\Service\Order\OrderTotalCalculator;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;

uses(KernelTestCase::class);
beforeAll(static function (): void {
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
beforeEach(function (): void {
    self::bootKernel();
    $this->em = self::$kernel->getContainer()->get('doctrine')->getManager();
    $connection = $this->em->getConnection();
    foreach ($this->em->getMetadataFactory()->getAllMetadata() as $meta) {
        $tableName = $meta->getTableName();
        $connection->executeStatement('TRUNCATE TABLE "' . $tableName . '" RESTART IDENTITY CASCADE;');
    }

    $this->apiService = new ApiService();
    $this->productService = new ProductService($this->apiService);
    $this->orderTotalCalculator = $this->createMock(OrderTotalCalculator::class);
    $this->orderItemService = $this->createMock(OrderItemService::class);
    $this->orderService = new OrderService(
        $this->orderTotalCalculator,
        $this->orderItemService
    );
});
test('createOrder crée une commande avec le bon total et appelle la décrémentation du stock', function (): void {
    $customer = new Customer();
    $customer
        ->setEmail('test@test.com')
        ->setFirstName('John')
        ->setLastName('Doe')
        ->setPhoneNumber('0123456789');
    $this->em->persist($customer);

    $cart = [
        [
            'product' => ['id' => 'e0a1b2c3-d4e5-6f7a-8b9c-0d1e2f3a4b5c', 'price' => 10],
            'quantity' => 2,
        ],
    ];

    $this->orderItemService
        ->expects($this->once())
        ->method('addOrderItems')
        ->with($this->isInstanceOf(Order::class), $cart, $this->em);

    $this->orderTotalCalculator
        ->expects($this->once())
        ->method('getOrderTotal')
        ->with($cart, 0.0)
        ->willReturn(20.0);

    $order = $this->orderService->createOrder(['id' => '1'], '2', $customer, $cart, $this->em);

    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->getOrderTotal())->toBe(20.0);
});
test('createOrder gère un panier vide', function (): void {
    $customer = new Customer();
    $customer
        ->setEmail('vide@test.com')
        ->setFirstName('Vide')
        ->setLastName('Panier')
        ->setPhoneNumber('0000000000');
    $this->em->persist($customer);

    $cart = [];

    $this->orderItemService
        ->expects($this->once())
        ->method('addOrderItems')
        ->with($this->isInstanceOf(Order::class), $cart, $this->em);

    $this->orderTotalCalculator
        ->expects($this->once())
        ->method('getOrderTotal')
        ->with($cart, 0.0)
        ->willReturn(0.0);

    $order = $this->orderService->createOrder(['id' => '1'], '2', $customer, $cart, $this->em);

    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->getOrderTotal())->toBe(0.0);
});
