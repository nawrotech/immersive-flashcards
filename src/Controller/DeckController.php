<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Flashcard;
use App\Form\DeckType;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
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
    ): Response {

        $flashcard1 = new Flashcard();
        $flashcard2 = new Flashcard();
        // $flashcard2 = new Flashcard();

        if ($deck == null) {
            $deck = new Deck();
            $deck->setCreator($this->getUser());
            $deck->addFlashcard($flashcard1);
            $deck->addFlashcard($flashcard2);
        }

        $form = $this->createForm(DeckType::class, $deck);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $deck = $form->getData();

            dd($deck);

            $em->persist($deck);
            $em->flush();

            return $this->redirectToRoute('app_deck');
        }

        return $this->render('deck/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route("/decks/details/{id}", name: "app_deck_details")]
    public function details(Deck $deck): Response
    {


        return $this->render('deck/details.html.twig', [
            'deck' => $deck
        ]);
    }
}
