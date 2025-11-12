<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PokemonApiService
{
    private const DEFAULT_LIMIT = 30;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl = 'https://pokeapi.co/api/v2'
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPokemonList(int $limit = self::DEFAULT_LIMIT, int $offset = 0): array
    {
        try {
            $response = $this->httpClient->request('GET', sprintf('%s/pokemon', $this->baseUrl), [
                'query' => [
                    'limit' => $limit,
                    'offset' => $offset,
                ],
            ]);

            $payload = $response->toArray();
        } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface|DecodingExceptionInterface $exception) {
            throw new \RuntimeException('Unable to contact PokéAPI.', previous: $exception);
        }

        $results = $payload['results'] ?? [];

        return array_map(static function (array $pokemon): array {
            $id = self::extractIdFromUrl($pokemon['url'] ?? '');

            return [
                'id' => $id,
                'name' => $pokemon['name'] ?? 'Unknown',
                'image' => self::buildOfficialArtworkUrl($id),
            ];
        }, $results);
    }

    public function getPokemon(int|string $identifier): array
    {
        try {
            $response = $this->httpClient->request('GET', sprintf('%s/pokemon/%s', $this->baseUrl, $identifier));

            return $response->toArray();
        } catch (ClientExceptionInterface $exception) {
            throw new NotFoundHttpException('Pokémon introuvable.', $exception);
        } catch (RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface|DecodingExceptionInterface $exception) {
            throw new \RuntimeException('Une erreur est survenue lors de la récupération du Pokémon.', 0, $exception);
        }
    }

    public function searchPokemon(string $term): ?array
    {
        $term = strtolower(trim($term));

        if ('' === $term) {
            return null;
        }

        try {
            return $this->getPokemon($term);
        } catch (NotFoundHttpException) {
            return null;
        }
    }

    public static function buildOfficialArtworkUrl(?int $pokemonId): ?string
    {
        if (null === $pokemonId) {
            return null;
        }

        return sprintf('https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/%d.png', $pokemonId);
    }

    private static function extractIdFromUrl(?string $url): ?int
    {
        if (null === $url) {
            return null;
        }

        $matches = [];
        if (1 === preg_match('#/pokemon/(\d+)/?#', $url, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
