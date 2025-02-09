<?php

namespace App\Controller;

use App\Contract\ImageProviderInterface;
use App\Service\GiphyApiService;
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
        #[MapQueryParameter()] string $flashcardType = "image",
    ): Response {

        // check csrf for the ajax call as well as 
        $images = $flashcardType == 'image' ?
            $unsplashApiService?->getImagesByQuery($query, $lang = 'en') :
            $giphyApiService?->getImagesByQuery($query, $lang = 'en');

        return $this->json($images);
    }
}
