<?php

namespace App\Controller;

use App\Dto\ImageDto;
use App\Service\GiphyApiService;
use App\Service\UnsplashApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class FlashcardController extends AbstractController
{
    #[Route('/flashcard', name: 'app_flashcard')]
    public function index(
        UnsplashApiService $unsplashApiService,
        GiphyApiService $giphyApiService
    ): Response {

        $images = $unsplashApiService->getImagesByQuery('cake');
        $gifs = $giphyApiService->getImagesByQuery('cake');

        return $this->render('flashcard/index.html.twig', [
            'images' => $images,
            'gifs' => $gifs
        ]);
    }
}
