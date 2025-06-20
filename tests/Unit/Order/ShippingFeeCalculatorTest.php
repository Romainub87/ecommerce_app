<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Service\Order\ShippingFeeCalculator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

uses(KernelTestCase::class);

beforeEach(function (): void {
    self::bootKernel();
    $this->em = self::$kernel->getContainer()->get('doctrine')->getManager();
    $connection = $this->em->getConnection();
    foreach ($this->em->getMetadataFactory()->getAllMetadata() as $meta) {
        $tableName = $meta->getTableName();
        $connection->executeStatement('TRUNCATE TABLE "' . $tableName . '" RESTART IDENTITY CASCADE;');
    }

    $this->shippingFeeCalculator = new ShippingFeeCalculator();
});

test('calcule les frais de port avec poids valide', function (): void {
    $deliveryMethods = [
        ['max-weight' => 10],
        ['max-weight' => 20],
    ];
    $result = $this->shippingFeeCalculator->calculateShippingFees($deliveryMethods, 5000, 100);
    expect($result[0])->toHaveKey('shipping_fee');
});
test('calcul de frais de port avec un poids total nul', function (): void {
    $deliveryMethods = [
        ['max-weight' => 5],
    ];
    $result = $this->shippingFeeCalculator->calculateShippingFees($deliveryMethods, 0, 50);
    expect($result[0]['shipping_fee'])->toBeGreaterThanOrEqual(0);
});
test('calcul frais de port avec un prix total élevé', function (): void {
    $deliveryMethods = [
        ['max-weight' => 15],
    ];
    $result = $this->shippingFeeCalculator->calculateShippingFees($deliveryMethods, 10000, 10000);
    expect($result[0]['shipping_fee'])->toBeGreaterThan(0);
});
