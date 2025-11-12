<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Navbar
{
    /**
     * @var array<int, array{path: string, label: string}>
     */
    public array $links = [
        ['path' => 'home.index', 'label' => 'Accueil'],
        ['path' => 'pokedex.index', 'label' => 'Pokédex'],
    ];
}
