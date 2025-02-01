<?php

namespace App\Controller;

use App\Dto\ImageDto;
use App\Service\GiphyApiService;
use App\Service\UnsplashApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;


final class FlashcardController extends AbstractController
{
    #[Route('/flashcard', name: 'app_flashcard')]
    public function index(
        UnsplashApiService $unsplashApiService,
        GiphyApiService $giphyApiService,
        #[MapQueryParameter()] string $query = ""
    ): Response {

        $images = $query ? $unsplashApiService?->getImagesByQuery($query) : [];

        return $this->render('flashcard/index.html.twig', [
            'images' => $images,
        ]);
    }
}
