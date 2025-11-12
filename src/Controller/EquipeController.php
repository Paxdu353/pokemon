<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Entity\Joueur;
use App\Entity\PokemonEquipe;
use App\Form\EquipageType;
use App\Form\PokemonEquipeType;
use App\Service\PokemonApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/equipe')]
final class EquipeController extends AbstractController
{
    #[Route('', name: 'equipe.index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, PokemonApiService $pokemonApiService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Joueur $joueur */
        $joueur = $this->getUser();

        $team = $joueur->getEquipes()->first() ?: null;

        if (!$team instanceof Equipe) {
            $team = new Equipe();
            $teamForm = $this->createForm(EquipageType::class, $team);
            $teamForm->handleRequest($request);

            if ($teamForm->isSubmitted() && $teamForm->isValid()) {
                $team->setJoueur($joueur);
                $entityManager->persist($team);
                $entityManager->flush();

                $this->addFlash('success', 'Votre équipe a été créée ! Ajoutez maintenant des Pokémon.');

                return $this->redirectToRoute('equipe.index');
            }

            return $this->render('equipe/index.html.twig', [
                'equipe' => null,
                'teamForm' => $teamForm->createView(),
                'pokemonForm' => null,
            ]);
        }

        $pokemonEquipe = new PokemonEquipe();
        $pokemonForm = $this->createForm(PokemonEquipeType::class, $pokemonEquipe);
        $pokemonForm->handleRequest($request);

        if ($pokemonForm->isSubmitted() && $pokemonForm->isValid()) {
            if ($team->getPokemonEquipes()->count() >= 6) {
                $this->addFlash('warning', 'Votre équipe est déjà complète (6 Pokémon maximum).');

                return $this->redirectToRoute('equipe.index');
            }

            $pokemonId = $pokemonEquipe->getPokemonId();

            foreach ($team->getPokemonEquipes() as $existing) {
                if ($existing->getPokemonId() === $pokemonId) {
                    $this->addFlash('warning', 'Ce Pokémon est déjà dans votre équipe.');

                    return $this->redirectToRoute('equipe.index');
                }
            }

            $pokemonData = $pokemonApiService->getPokemon($pokemonId);

            $sprite = $pokemonData['sprites']['other']['official-artwork']['front_default']
                ?? $pokemonData['sprites']['front_default']
                ?? PokemonApiService::buildOfficialArtworkUrl($pokemonId);

            $pokemonEquipe
                ->setPokemonName($pokemonData['name'] ?? 'Inconnu')
                ->setSprite($sprite)
                ->setHp($this->extractStat($pokemonData, 'hp'))
                ->setAttack($this->extractStat($pokemonData, 'attack'))
                ->setDefense($this->extractStat($pokemonData, 'defense'))
                ->setSpeed($this->extractStat($pokemonData, 'speed'))
                ->setEquipe($team);

            $entityManager->persist($pokemonEquipe);
            $entityManager->flush();

            $this->addFlash('success', sprintf('%s a rejoint votre équipe !', ucfirst((string) $pokemonEquipe->getPokemonName())));

            return $this->redirectToRoute('equipe.index');
        }

        return $this->render('equipe/index.html.twig', [
            'equipe' => $team,
            'teamForm' => null,
            'pokemonForm' => $pokemonForm->createView(),
        ]);
    }

    #[Route('/pokemon/{id}/supprimer', name: 'equipe.pokemon_remove', methods: ['POST'])]
    public function removePokemon(PokemonEquipe $pokemonEquipe, EntityManagerInterface $entityManager, Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Joueur $joueur */
        $joueur = $this->getUser();

        if (!$this->isCsrfTokenValid('delete_pokemon_'.$pokemonEquipe->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Le jeton CSRF est invalide.');

            return $this->redirectToRoute('equipe.index');
        }

        $team = $pokemonEquipe->getEquipe();
        if (!$team instanceof Equipe || $team->getJoueur() !== $joueur) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($pokemonEquipe);
        $entityManager->flush();

        $this->addFlash('success', 'Le Pokémon a été retiré de votre équipe.');

        return $this->redirectToRoute('equipe.index');
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
}
