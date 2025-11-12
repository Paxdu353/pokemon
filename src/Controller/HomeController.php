<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home.index', requirements: ['name' => '\w+'])]
    public function index(Request $request): Response
    {
        return $this->render('home/index.html.twig', [
            'name' => $request->query->get('name'),
        ]);
    }
}
