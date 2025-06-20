<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ApiService;
use App\Service\Cart\CartService;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private ApiService $apiService;
    private CartService $cartService;

    public function __construct(ApiService $apiService, CartService $cartService)
    {
        $this->cartService = $cartService;
        $this->apiService = $apiService;
    }

    /**
     * @throws GuzzleException
     */
    #[Route('/cart/add', name: 'cart_add', methods: ['POST'])]
    public function addToCart(Request $request, SessionInterface $session)
    {
        $productId = $request->request->get('product_id');
        $quantity = (int) $request->request->get('quantity', 1);
        $product = $this->apiService->fetchProductById($productId);

        if ($product && $this->cartService->addToCart($product, $quantity, $session)) {
            $this->addFlash('success', 'Produit ajouté au panier avec succès.');
        } else {
            $this->addFlash(
                'error',
                'Le produit est en rupture de stock ou la quantité demandée dépasse le stock disponible.'
            );
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }

    #[Route('/cart/update', name: 'cart_update', methods: ['POST'])]
    public function updateCart(Request $request, CartService $cartService): JsonResponse
    {
        $action = trim($request->request->get('action'));
        $productId = trim($request->request->get('productId'));

        try {
            switch ($action) {
                case 'decrease':
                    $cartService->decreaseQuantity($productId, $request->getSession());
                    break;
                case 'increase':
                    $cartService->increaseQuantity($productId, $request->getSession());
                    break;
                case 'remove':
                    $cartService->removeProduct($productId, $request->getSession());
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid action');
            }
            return new JsonResponse(
                [
                    'success' => true,
                    'count' => $request->getSession()->get('cart')[$productId]['quantity'] ?? 0,
                ]
            );
        } catch (\Throwable $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    #[Route('/cart', name: 'cart')]
    public function viewCart(SessionInterface $session)
    {
        $cart = $session->get('cart', []);

        $totalCart = $this->cartService->getCartTotalPrice($cart);

        return $this->render(
            'cart/index.html.twig',
            [
                'cart' => $cart,
                'totalCart' => $totalCart,
            ]
        );
    }
}
