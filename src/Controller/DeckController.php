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
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class DeckController extends AbstractController
{
    public const MAX_FLASHCARDS_IN_DECK = 20;

    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'app_deck')]
    public function index(): Response
    {
        return $this->render('deck/index.html.twig');
    }

    #[Route("/decks/{ulid?}", name: "app_deck_create")]
    public function create(
        Request $request,
        LocaleMappingService $localeMappingService,
        #[MapEntity(mapping: ["ulid" => "ulid"])] ?Deck $deck = null
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
            $this->em->persist($deck);
            $this->em->flush();
            return $this->redirectToRoute('app_deck');
        }

        return $this->render('deck/create.html.twig', [
            'form' => $form,
            'serviceLocalesMapping' => $serviceLocalesMapping
        ]);
    }

    #[Route("/decks/{ulid}/results", name: "app_deck_results")]
    public function results(
        #[MapEntity(mapping: ["ulid" => "ulid"])] Deck $deck,
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


    #[Route("/decks/practice/{ulid}", name: "app_deck_practice")]
    public function practice(
        #[MapEntity(mapping: ["ulid" => "ulid"])] Deck $deck,
        FlashcardRepository $flashcardRepository,
        LocaleMappingService $localeMappingService,
        #[MapQueryParameter()] ?string $flashcardResult = null
    ): Response {

        $flashcards = $flashcardRepository
            ->findByDeck($deck, result: FlashcardResult::tryFrom($flashcardResult));

        $sentencesLanguage = $localeMappingService
            ->getServiceMappingForLocale($deck->getLang(), 'sentence_service');

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

    #[Route("/decks/practice/results/{ulid}", name: "app_deck_store_practice_results", methods: ['POST'])]
    public function storePracticeResults(
        Request $request,
        #[MapEntity(mapping: ["ulid" => "ulid"])] Deck $deck,
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
                'ulid' => $deck->getUlid()
            ])
        ]);
    }
}
