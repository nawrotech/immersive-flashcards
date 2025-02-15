<?php

namespace App\Controller;

use App\Contract\ImageProviderInterface;
use App\Service\DefinitionService;
use App\Service\GiphyApiService;
use App\Service\SentenceService;
use App\Service\UnsplashApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;


final class FlashcardController extends AbstractController
{
    #[Route('/flashcard', name: 'app_flashcard')]
    public function index(
        #[Autowire(service: UnsplashApiService::class)] ImageProviderInterface $unsplashApiService,
        #[Autowire(service: GiphyApiService::class)] ImageProviderInterface $giphyApiService,
        #[MapQueryParameter()] string $query = "",
        #[MapQueryParameter()] string $flashcardType = "",
        #[MapQueryParameter()] string $lang = ""
    ): Response {

        $images = $flashcardType == 'image' ?
            $unsplashApiService?->getImagesByQuery($query, $lang) :
            $giphyApiService?->getImagesByQuery($query, $lang);

        return $this->json($images);
    }


    #[Route('/flashcard/sentences', name: 'app_flashcard_sentences')]
    public function sentences(
        SentenceService $sentenceService,
        #[MapQueryParameter()] string $query = "",
        #[MapQueryParameter()] string $lang = "",
    ) {

        return $this->json($sentenceService->getSentences($query, $lang));
    }
}
