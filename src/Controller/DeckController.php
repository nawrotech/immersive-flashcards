<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Form\DeckType;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeckController extends AbstractController
{

    #[Route('/', name: 'app_deck')]
    public function index(DeckRepository $deckRepository): Response
    {
        $decks = $deckRepository->findBy(['creator' => $this->getUser()]);

        return $this->render('deck/index.html.twig', [
            'decks' => $decks
        ]);
    }

    #[Route("/decks/create/{id?}", name: "app_deck_create")]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ?Deck $deck = null
    ) {

        if ($deck == null) {
            $deck = new Deck();
            $deck->setCreator($this->getUser());
        }

        $form = $this->createForm(DeckType::class, $deck);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $deck = $form->getData();

            $em->persist($deck);
            $em->flush();

            return $this->redirectToRoute('app_deck');
        }

        return $this->render('deck/create.html.twig', [
            'form' => $form,
        ]);
    }
}
