<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\BankCardForm;
use App\Form\BankTransferForm;
use App\Form\DeliveryInfoType;
use App\Service\ApiService;
use App\Service\Cart\CartService;
use App\Service\DeliveryService;
use App\Service\Entity\AddressService;
use App\Service\Entity\CustomerService;
use App\Service\Order\OrderService;
use App\Service\Order\OrderTotalCalculator;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CheckoutController extends AbstractController
{
    public function __construct(
        private readonly ApiService $apiService,
        private readonly CartService $cartService,
        private readonly OrderService $orderService,
        private readonly CustomerService $customerService,
        private readonly OrderTotalCalculator $orderTotalCalculator,
        private readonly AddressService $addressService,
        private readonly SessionService $sessionService
    ) {
    }

    /**
     * @throws GuzzleException
     */
    #[Route('/checkout/details', name: 'checkout_details')]
    public function checkoutDetails(Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $hasStock = true;
        foreach ($cart as $item) {
            if (! $this->cartService->checkStock($item['product'], $item['quantity'])) {
                $hasStock = false;
                break;
            }
        }
        if ($cart === [] || $cart === null) {
            $this->addFlash('error', 'Votre panier est vide. Veuillez ajouter des articles avant de continuer.');
            return $this->redirectToRoute('cart');
        }
        if (! $hasStock) {
            $this->addFlash('error', 'Un ou plusieurs articles de votre panier ne sont plus en stock.');
            return $this->redirectToRoute('cart');
        }

        $deliveryInfo = $request->getSession()->get('delivery_info', []);
        $form = $this->createForm(DeliveryInfoType::class, $deliveryInfo);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $deliveryInfo = $form->getData();
            $request->getSession()->set('delivery_info', $deliveryInfo);

            return $this->redirectToRoute('checkout_delivery');
        }

        return $this->render(
            'checkout/details.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route('/checkout/delivery', name: 'checkout_delivery')]
    public function chooseDelivery(Request $request, DeliveryService $deliveryService): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $deliveryMethod = $request->getSession()->get('delivery_method', null);
        $deliveryMethods = $deliveryService->getAvailableDeliveryMethods($cart);

        if ($request->isMethod('POST')) {
            $selectedMethod = $request->request->get('delivery_method');
            $method = $deliveryService->findDeliveryMethodById($deliveryMethods, $selectedMethod);
            $request->getSession()->set('delivery_method', $method);

            return $this->redirectToRoute('checkout_payment');
        }

        return $this->render(
            'checkout/delivery.html.twig',
            [
                'deliveryMethods' => $deliveryMethods,
                'selectedMethod' => $deliveryMethod,
            ]
        );
    }

    #[Route('/checkout/payment', name: 'checkout_payment')]
    public function choosePayment(Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $deliveryInfo = $request->getSession()->get('delivery_info', []);
        $deliveryMethod = $request->getSession()->get('delivery_method', null);
        $paymentMethod = $request->getSession()->get('payment_method', null);
        $paymentMethods = $this->apiService->getPaymentMethods();

        if ($request->isMethod('POST')) {
            $selectedMethod = $request->request->get('payment_method');
            $request->getSession()->set('payment_method', $selectedMethod);

            return $this->redirectToRoute('checkout_confirmation');
        }

        return $this->render(
            'checkout/payment.html.twig',
            [
                'transferForm' => $this->createForm(BankTransferForm::class)->createView(),
                'cardForm' => $this->createForm(BankCardForm::class)->createView(),
                'totalOrder' => $this->orderTotalCalculator->getOrderTotal($cart, $deliveryMethod['shipping_fee'] ?? 0.0),
                'cart' => $cart,
                'deliveryInfo' => $deliveryInfo,
                'deliveryMethod' => $deliveryMethod,
                'paymentMethods' => $paymentMethods,
                'selectedMethod' => $paymentMethod,
            ]
        );
    }

    #[Route('/checkout/confirmation', name: 'checkout_confirmation')]
    public function confirmation(Request $request, EntityManagerInterface $em): Response
    {
        $deliveryInfo = $request->getSession()->get('delivery_info', []);
        $deliveryMethod = $request->getSession()->get('delivery_method', null);
        $paymentMethod = $request->getSession()->get('payment_method', null);
        $cart = $request->getSession()->get('cart', []);

        $address = $this->addressService->getOrCreateAddress($deliveryInfo, $em);
        $customer = $this->customerService->getOrCreateCustomer($deliveryInfo, $address, $em);

        try {
            $this->orderService->createOrder($deliveryMethod, $paymentMethod, $customer, $cart, $em);
            $this->sessionService->clearCheckoutSession($request->getSession());
            $this->addFlash('success', 'Votre commande a été créée avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la création de la commande. Veuillez réessayer.');
            return $this->redirectToRoute('checkout_payment');
        }

        return $this->render('checkout/confirmation.html.twig');
    }
}
