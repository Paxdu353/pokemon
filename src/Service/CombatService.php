<?php

namespace App\Service;

use App\Entity\Equipe;
use App\Entity\PokemonEquipe;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CombatService
{
    public function __construct(private readonly PokemonApiService $pokemonApiService)
    {
    }

    /**
     * @return array{
     *     playerTeam: array<int, array<string, mixed>>,
     *     opponentTeam: array<int, array<string, mixed>>,
     *     rounds: array<int, string>,
     *     winner: string
     * }
     */
    public function simulate(Equipe $equipe): array
    {
        $playerTeam = array_map(
            static fn (PokemonEquipe $pokemon) => [
                'id' => $pokemon->getPokemonId(),
                'name' => $pokemon->getPokemonName(),
                'sprite' => $pokemon->getSprite()
                    ?? PokemonApiService::buildOfficialArtworkUrl($pokemon->getPokemonId())
                    ?? PokemonApiService::placeholderSprite($pokemon->getPokemonId()),
                'hp' => $pokemon->getHp(),
                'attack' => $pokemon->getAttack(),
                'defense' => $pokemon->getDefense(),
                'speed' => $pokemon->getSpeed(),
                'currentHp' => $pokemon->getHp(),
                'surnom' => $pokemon->getSurnom(),
            ],
            $equipe->getPokemonEquipes()->toArray()
        );

        $opponentTeam = $this->generateOpponentTeam(count($playerTeam));

        [$rounds, $winner] = $this->runBattle($playerTeam, $opponentTeam);

        return [
            'playerTeam' => $playerTeam,
            'opponentTeam' => $opponentTeam,
            'rounds' => $rounds,
            'winner' => $winner,
        ];
    }

    /**
     * @return array{0: array<int, string>, 1: string}
     */
    private function runBattle(array &$playerTeam, array &$opponentTeam): array
    {
        $rounds = [];
        $playerIndex = 0;
        $opponentIndex = 0;

        while (isset($playerTeam[$playerIndex], $opponentTeam[$opponentIndex])) {
            $player = &$playerTeam[$playerIndex];
            $opponent = &$opponentTeam[$opponentIndex];

            $rounds[] = sprintf('Un duel commence entre %s et %s !', $this->formatName($player), $this->formatName($opponent));

            while ($player['currentHp'] > 0 && $opponent['currentHp'] > 0) {
                $firstAttacker = $player['speed'] >= $opponent['speed'] ? 'player' : 'opponent';
                $this->performAttack($firstAttacker, $player, $opponent, $rounds);

                if ($opponent['currentHp'] <= 0) {
                    $rounds[] = sprintf('%s est K.O. !', ucfirst($opponent['name']));
                    ++$opponentIndex;
                    break;
                }

                if ($player['currentHp'] <= 0) {
                    $rounds[] = sprintf('%s est K.O. !', ucfirst($player['name']));
                    ++$playerIndex;
                    break;
                }

                $this->performAttack('player' === $firstAttacker ? 'opponent' : 'player', $player, $opponent, $rounds);

                if ($opponent['currentHp'] <= 0) {
                    $rounds[] = sprintf('%s est K.O. !', ucfirst($opponent['name']));
                    ++$opponentIndex;
                    break;
                }

                if ($player['currentHp'] <= 0) {
                    $rounds[] = sprintf('%s est K.O. !', ucfirst($player['name']));
                    ++$playerIndex;
                    break;
                }
            }
        }

        $winner = isset($playerTeam[$playerIndex]) ? 'joueur' : 'adversaire';

        return [$rounds, $winner];
    }

    private function performAttack(string $attackerSide, array &$player, array &$opponent, array &$rounds): void
    {
        if ('player' === $attackerSide) {
            $damage = $this->calculateDamage($player, $opponent);
            $opponent['currentHp'] = max(0, $opponent['currentHp'] - $damage);
            $rounds[] = sprintf('%s attaque et inflige %d dégâts !', $this->formatName($player), $damage);
        } else {
            $damage = $this->calculateDamage($opponent, $player);
            $player['currentHp'] = max(0, $player['currentHp'] - $damage);
            $rounds[] = sprintf('%s attaque et inflige %d dégâts !', $this->formatName($opponent), $damage);
        }
    }

    private function calculateDamage(array $attacker, array $defender): int
    {
        $baseDamage = max(1, $attacker['attack'] - (int) floor($defender['defense'] / 2));
        $randomFactor = random_int(0, 5);

        return $baseDamage + $randomFactor;
    }

    private function formatName(array $pokemon): string
    {
        if (!empty($pokemon['surnom'])) {
            return sprintf('%s (%s)', $pokemon['surnom'], ucfirst((string) $pokemon['name']));
        }

        return ucfirst((string) $pokemon['name']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function generateOpponentTeam(int $size): array
    {
        $size = max(1, $size);
        $team = [];

        for ($i = 0; $i < $size; ++$i) {
            $id = random_int(1, 151);
            try {
                $data = $this->pokemonApiService->getPokemon($id);
                $team[] = $this->normalizeOpponentData($data, $id);
            } catch (NotFoundHttpException|\RuntimeException) {
                $team[] = $this->buildFallbackOpponent($id);
            }
        }

        return $team;
    }

    /**
     * @param array<string, mixed> $pokemonData
     */
    private function extractStat(array $pokemonData, string $statName): int
    {
        foreach ($pokemonData['stats'] ?? [] as $stat) {
            if (($stat['stat']['name'] ?? null) === $statName) {
                return (int) ($stat['base_stat'] ?? 0);
            }
        }

        return 0;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function normalizeOpponentData(array $data, int $fallbackId): array
    {
        $hp = $this->extractStat($data, 'hp');

        return [
            'id' => (int) ($data['id'] ?? $fallbackId),
            'name' => $data['name'] ?? sprintf('mystère #%d', $fallbackId),
            'sprite' => $data['sprites']['other']['official-artwork']['front_default']
                ?? $data['sprites']['front_default']
                ?? PokemonApiService::buildOfficialArtworkUrl($fallbackId)
                ?? PokemonApiService::placeholderSprite($fallbackId),
            'hp' => $hp,
            'attack' => $this->extractStat($data, 'attack'),
            'defense' => $this->extractStat($data, 'defense'),
            'speed' => $this->extractStat($data, 'speed'),
            'currentHp' => $hp,
            'surnom' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFallbackOpponent(int $id): array
    {
        return [
            'id' => $id,
            'name' => sprintf('mystère #%d', $id),
            'sprite' => PokemonApiService::placeholderSprite($id),
            'hp' => 50,
            'attack' => 50,
            'defense' => 50,
            'speed' => 50,
            'currentHp' => 50,
            'surnom' => null,
        ];
    }
}
