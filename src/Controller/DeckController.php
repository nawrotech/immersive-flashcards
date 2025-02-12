<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Flashcard;
use App\Enum\FlashcardResult;
use App\Form\DeckType;
use App\Repository\DeckRepository;
use App\Repository\FlashcardRepository;
use App\Service\FlashcardService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class DeckController extends AbstractController
{

    public const MAX_FLASHCARDS_IN_DECK = 30;

    public function __construct(private EntityManagerInterface $em) {}

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
        ?Deck $deck = null
    ): Response {

        if ($deck == null) {
            $deck = new Deck();
            $deck->setCreator($this->getUser());
            $deck->addFlashcard(new Flashcard());
        }

        $form = $this->createForm(DeckType::class, $deck);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $deck = $form->getData();
            $this->em->persist($deck);
            $this->em->flush();
            return $this->redirectToRoute('app_deck');
        }

        return $this->render('deck/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route("/decks/details/{id}", name: "app_deck_details")]
    public function details(
        Deck $deck,
        FlashcardRepository $flashcardRepository,
        FlashcardService $flashcardService
    ): Response {

        $flashcards = $flashcardRepository->findByDeck($deck, true);
        $deckResultSummary = $flashcardService->getDeckResultsSummary($flashcards);

        return $this->render('deck/details.html.twig', [
            'deck' => $deck,
            'flashcards' => $flashcards,
            'deckResultSummary' => $deckResultSummary
        ]);
    }


    #[Route("/decks/practice/{id}", name: "app_deck_practice")]
    public function practice(
        Deck $deck,
        FlashcardRepository $flashcardRepository,
        #[MapQueryParameter()] ?string $flashcardResult = null
    ) {

        $flashcards = $flashcardRepository
            ->findByDeck($deck, result: FlashcardResult::tryFrom($flashcardResult));

        foreach ($flashcards as $flashcard) {
            $flashcard->setResult(FlashcardResult::UNANSWERED);
            $this->em->flush();
        }

        return $this->render('deck/practice.html.twig', [
            'deck' => $deck,
            'flashcards' => $flashcards
        ]);
    }

    #[Route("/decks/practice/results/{id}", name: "app_deck_store_practice_results", methods: ['POST'])]
    public function storePracticeResults(Request $request, Deck $deck, FlashcardRepository $flashcardRepository): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $data = $request->toArray();
            $answers = array_column($data, 'result', 'id');

            dump($answers);

            $flashcardIdsList = array_keys($answers);
            $flashcards = $flashcardRepository->findBy(["id" => $flashcardIdsList]);

            foreach ($flashcards as $flashcard) {
                if (isset($answers[$flashcard->getId()])) {
                    $flashcard->setResult(FlashcardResult::tryFrom($answers[$flashcard->getId()]));
                }
            }

            $this->em->flush();
        }

        return $this->json([
            'redirect' => true,
            'url' => $this->generateUrl("app_deck_details", [
                'id' => $deck->getId()
            ])
        ]);
    }
}
