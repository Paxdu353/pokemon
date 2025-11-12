<?php

namespace App\Controller;

use App\Service\PokemonApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PokedexController extends AbstractController
{
    public function __construct(private readonly PokemonApiService $pokemonApiService)
    {
    }

    #[Route('/pokedex', name: 'pokedex.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = (string) $request->query->get('search', '');
        $searchResult = null;

        if ('' !== trim($search)) {
            try {
                $searchResult = $this->pokemonApiService->searchPokemon($search);
            } catch (\RuntimeException) {
                $this->addFlash('danger', 'Impossible de contacter la PokéAPI pour la recherche.');
                $searchResult = null;
            }

            if (null === $searchResult) {
                $this->addFlash('warning', sprintf('Aucun Pokémon ne correspond à "%s".', $search));
            } else {
                $searchResult = $this->normalizePokemon($searchResult);
            }
        }

        try {
            $pokemonList = $this->pokemonApiService->getPokemonList();
        } catch (\RuntimeException) {
            $this->addFlash('danger', 'Impossible de charger le Pokédex pour le moment. Réessayez plus tard.');
            $pokemonList = [];
        }

        return $this->render('pokedex/index.html.twig', [
            'searchTerm' => $search,
            'searchResult' => $searchResult,
            'pokemonList' => array_map([$this, 'normalizePokemon'], $pokemonList),
        ]);
    }

    /**
     * @param array<string, mixed> $pokemon
     *
     * @return array<string, mixed>
     */
    private function normalizePokemon(array $pokemon): array
    {
        if (isset($pokemon['id'], $pokemon['name'], $pokemon['image'])) {
            return $pokemon;
        }

        $id = $pokemon['id'] ?? null;
        if (null === $id && isset($pokemon['url'])) {
            $id = $this->extractIdFromUrl($pokemon['url']);
        }

        if (isset($pokemon['sprites']['other']['official-artwork']['front_default'])) {
            $image = $pokemon['sprites']['other']['official-artwork']['front_default'];
        } else {
            $image = PokemonApiService::buildOfficialArtworkUrl(is_int($id) ? $id : null);
        }

        return [
            'id' => $id,
            'name' => $pokemon['name'] ?? 'Unknown',
            'image' => $image,
            'types' => $pokemon['types'] ?? [],
        ];
    }

    private function extractIdFromUrl(string $url): ?int
    {
        $matches = [];
        if (1 === preg_match('#/pokemon/(\d+)/?#', $url, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
