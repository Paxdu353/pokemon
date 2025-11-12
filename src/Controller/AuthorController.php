<?php

namespace App\Controller;

use Dom\Entity;
use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AuthorController extends AbstractController
{
    #[Route('/author', name: 'author.index')]
    public function index(AuthorRepository $authorRepository): Response
    {
        $authors = $authorRepository->findAll();

        return $this->render('author/index.html.twig', [
            'authors' => $authors,
        ]);
    }

    #[Route('/author/{id}', name: 'author.show', requirements: ['id' => '\d+'])]
    public function show(AuthorRepository $authorRepository, Author $author): Response
    {
        if (!$author) {
            throw $this->createNotFoundException('Auteur non trouvé');
        }

        return $this->render('author/show.html.twig', [
            'author' => $author,
        ]);
    }

    #[Route('/author/create', name: 'author.create')]
    public function create(AuthorRepository $authorRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($author);
            $entityManager->flush();

            return $this->redirectToRoute('author.index');
        }

        return $this->render('author/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
