<?php

namespace App\Controller;

use App\Form\BankCardForm;
use App\Form\BankTransferForm;
use App\Form\DeliveryInfoType;
use App\Service\ApiService;
use App\Service\CartService;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CheckoutController extends AbstractController
{
    private ApiService $apiService;
    private CartService $cartService;
    private OrderService $orderService;

    public function __construct(ApiService $apiService, CartService $cartService, OrderService $orderService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->apiService = $apiService;
    }

    #[Route('/checkout/details', name: 'checkout_details')]
    public function checkoutDetails(Request $request): Response
    {

        $cart = $request->getSession()->get('cart', []);
        $hasStock = true;
        foreach ($cart as $item) {
            if (!$this->cartService->checkStock($item['product'], $item['quantity'])) {
                $hasStock = false;
                break;
            }
        }
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide. Veuillez ajouter des articles avant de continuer.');
            return $this->redirectToRoute('cart');
        }
        if (!$hasStock) {
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
            'checkout/details.html.twig', [
            'form' => $form->createView(),
            ]
        );
    }

    #[Route('/checkout/delivery', name: 'checkout_delivery')]
    public function chooseDelivery(Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);

        $orderTotalWeight = 0;
        $orderTotalPrice = 0;

        foreach ($cart as $item) {
            $product = $item['product'][0] ?? null;
            if ($product) {
                $orderTotalWeight += $product['weight'] * $item['quantity'];
                $orderTotalPrice += $product['price'] * $item['quantity'];
            }
        }

        $deliveryMethod = $request->getSession()->get('delivery_method', null);
        $deliveryMethods = $this->apiService->getDeliveryMethodsWithCorrectWeight($orderTotalWeight);
        $deliveryMethods = $this->orderService->calculateShippingFees($deliveryMethods, $orderTotalWeight, $orderTotalPrice);

        if ($request->isMethod('POST')) {
            $selectedMethod = $request->request->get('delivery_method');
            $method = array_values(
                array_filter(
                    $deliveryMethods, function ($method) use ($selectedMethod) {
                        return $method['id'] == $selectedMethod;
                    }
                )
            )[0] ?? null;

            $request->getSession()->set('delivery_method', $method);

            return $this->redirectToRoute('checkout_payment');
        }

        return $this->render(
            'checkout/delivery.html.twig', [
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
        $deliveryMethod =$request->getSession()->get('delivery_method', null);
        $paymentMethod = $request->getSession()->get('payment_method', null);
        $paymentMethods = $this->apiService->getPaymentMethods();

        if ($request->isMethod('POST')) {
            $selectedMethod = $request->request->get('payment_method');
            $request->getSession()->set('payment_method', $selectedMethod);

            return $this->redirectToRoute('checkout_confirmation');
        }

        return $this->render(
            'checkout/payment.html.twig', [
            'transferForm' => $this->createForm(BankTransferForm::class)->createView(),
            'cardForm' => $this->createForm(BankCardForm::class)->createView(),
            'cart' => $cart,
            'deliveryInfo' => $deliveryInfo,
            'deliveryMethod' => $deliveryMethod,
            'paymentMethods' => $paymentMethods,
            'selectedMethod' => $paymentMethod,
            ]
        );
    }

    #[Route('/checkout/confirmation', name: 'checkout_confirmation')]
    public function confirmation(Request $request, EntityManagerInterface $em, OrderService $orderService): Response
    {
        $deliveryInfo = $request->getSession()->get('delivery_info', []);
        $deliveryMethod = $request->getSession()->get('delivery_method', null);
        $paymentMethod = $request->getSession()->get('payment_method', null);
        $cart = $request->getSession()->get('cart', []);

        $address = $orderService->getOrCreateAddress($deliveryInfo, $em);
        $customer = $orderService->getOrCreateCustomer($deliveryInfo, $address, $em);
        $order = $orderService->createOrder($deliveryMethod, $paymentMethod, $customer, $cart, $em);

        $em->flush();

        // Nettoyer la session après la confirmation de la commande
        $request->getSession()->remove('delivery_info');
        $request->getSession()->remove('delivery_method');
        $request->getSession()->remove('payment_method');
        $request->getSession()->remove('cart');

        return $this->render('checkout/confirmation.html.twig');
    }
}
