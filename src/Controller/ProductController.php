<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    #[Route('/product/{id}', name: 'product_details')]
    public function details(string $id): Response
    {
        $product = $this->apiService->fetchProductById($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found.');
        }

        return $this->render(
            'product/details.html.twig', [
            'product' => $product[0],
            ]
        );
    }
}
