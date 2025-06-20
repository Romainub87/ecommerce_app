<?php

declare(strict_types=1);


use App\Service\ApiService;

test('obtenir les méthodes de livraison avec un poids nul (0kg)', function (): void {
    $service = new ApiService();
    $result = $service->getDeliveryMethodsWithCorrectWeight(0);
    expect($result)->toBeArray()
        ->and($result)->not->toBeEmpty()
        ->and(count($result))->toBe(7);
});
test('obtenir les méthodes de livraison avec un poids positif (5kg)', function (): void {
    $service = new ApiService();
    $result = $service->getDeliveryMethodsWithCorrectWeight(5000);
    expect($result)->toBeArray()
        ->and($result)->not->toBeEmpty()
        ->and(count($result))->toBeGreaterThan(0)
        ->and(count($result))->toBeLessThanOrEqual(7, 'Doit retourner un maximum de 7 méthodes de livraison');
});
test('obtenir les méthodes de livraison avec un poids négatif (-1kg)', function (): void {
    $service = new ApiService();
    $result = $service->getDeliveryMethodsWithCorrectWeight(-100);
    expect($result)->toBeArray()
        ->and($result)->toBeEmpty('Doit retourner un tableau vide pour un poids négatif');
});
test('obtenir les méthodes de livraison avec un poids très élevé (100kg)', function (): void {
    $service = new ApiService();
    $result = $service->getDeliveryMethodsWithCorrectWeight(100000);
    expect($result)->toBeArray()
        ->and($result)->toBeEmpty('Doit retourner un tableau vide pour un poids très élevé');
});
