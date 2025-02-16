<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Flashcard;
use App\Enum\FlashcardResult;
use App\Form\DeckType;
use App\Repository\FlashcardRepository;
use App\Service\FlashcardService;
use App\Service\LocaleMappingService;
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
    public function index(): Response
    {
        return $this->render('deck/index.html.twig');
    }


    #[Route("/decks/create/{id?}", name: "app_deck_create")]
    public function create(
        Request $request,
        LocaleMappingService $localeMappingService,
        ?Deck $deck = null
    ): Response {

        if ($deck == null) {
            $deck = new Deck();
            $deck->setCreator($this->getUser());
            $deck->addFlashcard(new Flashcard());
        }

        $form = $this->createForm(DeckType::class, $deck);
        $form->handleRequest($request);

        $serviceLocalesMapping = $localeMappingService->getServiceMappings(['image_service']);

        if ($form->isSubmitted() && $form->isValid()) {
            $deck = $form->getData();
            $this->em->persist($deck);
            $this->em->flush();
            return $this->redirectToRoute('app_deck');
        }

        return $this->render('deck/create.html.twig', [
            'form' => $form,
            'serviceLocalesMapping' => $serviceLocalesMapping
        ]);
    }

    #[Route("/decks/{id}/results", name: "app_deck_results")]
    public function results(
        Deck $deck,
        FlashcardRepository $flashcardRepository,
        FlashcardService $flashcardService
    ): Response {

        $flashcards = $flashcardRepository->findByDeck($deck, true);
        $deckResultSummary = $flashcardService->getDeckResultsSummary($flashcards);

        return $this->render('deck/results.html.twig', [
            'deck' => $deck,
            'deckResultSummary' => $deckResultSummary
        ]);
    }


    #[Route("/decks/practice/{id}", name: "app_deck_practice")]
    public function practice(
        Deck $deck,
        FlashcardRepository $flashcardRepository,
        LocaleMappingService $localeMappingService,
        #[MapQueryParameter()] ?string $flashcardResult = null
    ): Response {

        $flashcards = $flashcardRepository
            ->findByDeck($deck, result: FlashcardResult::tryFrom($flashcardResult));

        $sentencesLanguage = $localeMappingService
            ->getServiceMapping($deck->getLang(), 'sentence_service');

        foreach ($flashcards as $flashcard) {
            $flashcard->setResult(FlashcardResult::UNANSWERED);
            $this->em->flush();
        }

        return $this->render('deck/practice.html.twig', [
            'deck' => $deck,
            'flashcards' => $flashcards,
            'sentencesLanguage' => $sentencesLanguage
        ]);
    }

    #[Route("/decks/practice/results/{id}", name: "app_deck_store_practice_results", methods: ['POST'])]
    public function storePracticeResults(
        Request $request,
        Deck $deck,
        FlashcardRepository $flashcardRepository
    ): JsonResponse {
        if ($request->isXmlHttpRequest()) {
            $data = $request->toArray();
            $answers = array_column($data, 'result', 'id');

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
            'url' => $this->generateUrl("app_deck_results", [
                'id' => $deck->getId()
            ])
        ]);
    }
}
