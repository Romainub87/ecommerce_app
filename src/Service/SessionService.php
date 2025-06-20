<?php

declare(strict_types=1);

namespace App\Service;

class SessionService
{
    public function clearCheckoutSession($session): void
    {
        $session->remove('cart');
        $session->remove('deliveryMethod');
        $session->remove('paymentMethod');
        $session->remove('deliveryInfo');
    }
}
