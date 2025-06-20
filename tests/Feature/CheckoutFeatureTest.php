<?php

namespace Tests\Feature;

use Symfony\Component\Panther\PantherTestCase;

class CheckoutFeatureTest extends PantherTestCase
{
    public function testCheckoutTunnelEndToEnd()
    {
        $client = static::createPantherClient();

        // 1. Ajout d'un produit au panier
        $client->request('GET', '/produits/1');
        $client->clickLink('Ajouter au panier');

        // 2. Affichage du panier
        $client->request('GET', '/cart');
        $this->assertSelectorTextContains('h1', 'Votre panier');

        // 3. Passage à la page de livraison
        $client->clickLink('Passer à la livraison');
        $this->assertSelectorTextContains('h1', 'Adresse de livraison');

        // 4. Soumission de l'adresse de livraison
        $client->submitForm('Valider', [
            'address' => '123 rue Exemple',
            'city' => 'Paris',
            'postal_code' => '75000',
        ]);
        $this->assertPageTitleContains('Paiement');

        // 5. Soumission du paiement
        $client->submitForm('Payer', [
            'payment_method' => 'credit_card',
            'card_number' => '4111111111111111',
            'expiration' => '12/25',
            'cvv' => '123',
        ]);
        $this->assertSelectorTextContains('h1', 'Merci pour votre commande');
    }
}
