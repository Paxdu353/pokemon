<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Entity\Joueur;
use App\Service\CombatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CombatController extends AbstractController
{
    #[Route('/combat', name: 'combat.index', methods: ['GET', 'POST'])]
    public function index(Request $request, CombatService $combatService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Joueur $joueur */
        $joueur = $this->getUser();
        $team = $joueur->getEquipes()->first() ?: null;

        $result = null;

        if ($request->isMethod('POST')) {
            if (!$team instanceof Equipe || 0 === $team->getPokemonEquipes()->count()) {
                $this->addFlash('warning', 'Vous devez avoir au moins un Pokémon dans votre équipe pour lancer un combat.');

                return $this->redirectToRoute('combat.index');
            }

            $result = $combatService->simulate($team);
        }

        return $this->render('combat/index.html.twig', [
            'equipe' => $team,
            'result' => $result,
        ]);
    }
}
