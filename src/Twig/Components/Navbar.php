<?php

namespace App\Twig\Components;

use App\Repository\PostRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Navbar
{

  public function __construct(private PostRepository $postRepository) {}

  public array $links = [
    ['path' => 'home.index', 'label' => 'Home'],
    ['path' => 'actu.index', 'label' => 'Last news'],
  ];


  public function getPosts(): array
  {
    return $this->postRepository->findBy(
      ['is_published' => true],
      ['createdAt' => 'DESC'],
      5
    );
  }
}
