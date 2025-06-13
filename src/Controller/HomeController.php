<?php

namespace App\Controller;

use App\Form\FilterProductsForm;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    private ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $categories = $this->apiService->fetchUniqueCategories();

        $reset = $request->query->get('reset');

        if ($reset) {
            $request->getSession()->set('filters', []);
            $filters = [];
        } else {
            $filters = $request->getSession()->get('filters', $request->query->all());
            if (!is_array($filters)) {
                $filters = [];
            }
        }

        $form = $this->createForm(
            FilterProductsForm::class,
            $filters,
            [
                'categories' => array_combine($categories, $categories),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $request->getSession()->set('filters', $filters);

            return $this->redirectToRoute('app_home', $filters);
        }

        $page = $request->query->getInt('page', 1);
        $products = $this->apiService->fetchProductsWithFilters($filters, $page);

        return $this->render(
            'home/index.html.twig', [
            'form' => $form->createView(),
            'products' => $products['items'],
            'totalPages' => $products['totalPages'],
            'currentPage' => $page,
            ]
        );
    }
}
