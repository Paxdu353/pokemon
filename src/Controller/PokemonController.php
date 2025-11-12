<?php

namespace App\Controller;

use App\Service\PokemonApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PokemonController extends AbstractController
{
    public function __construct(private readonly PokemonApiService $pokemonApiService)
    {
    }

    #[Route('/pokemon/{id}', name: 'pokemon.show', requirements: ['id' => '[\\w-]+'], methods: ['GET'])]
    public function show(string $id): Response
    {
        $pokemon = $this->pokemonApiService->getPokemon($id);

        $stats = array_map(static function (array $stat): array {
            return [
                'label' => $stat['stat']['name'] ?? 'stat',
                'value' => $stat['base_stat'] ?? 0,
            ];
        }, $pokemon['stats'] ?? []);

        $types = array_map(static fn (array $type) => $type['type']['name'] ?? 'unknown', $pokemon['types'] ?? []);

        return $this->render('pokemon/show.html.twig', [
            'pokemon' => $pokemon,
            'stats' => $stats,
            'types' => $types,
            'artwork' => $pokemon['sprites']['other']['official-artwork']['front_default'] ?? null,
        ]);
    }
}
