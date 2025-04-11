<?php

namespace App\Controller;

use App\Contract\ImageProviderInterface;
use App\Service\GiphyApiService;
use App\Service\SentenceService;
use App\Service\UnsplashApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class FlashcardController extends AbstractController
{
    #[Route('/flashcard', name: 'app_flashcard')]
    public function images(
        #[Autowire(service: UnsplashApiService::class)] ImageProviderInterface $unsplashApiService,
        #[Autowire(service: GiphyApiService::class)] ImageProviderInterface $giphyApiService,
        Request $request,
        #[MapQueryParameter()] string $query = "",
        #[MapQueryParameter()] string $flashcardType = "",
        #[MapQueryParameter()] string $lang = ""
    ): Response {

        $token = $request->headers->get('X-CSRF-TOKEN');
        if (!$this->isCsrfTokenValid('get_images', $token)) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }

        if (empty($query)) {
            return $this->json([]);
        }

        try {
            $images = $flashcardType === 'image' ?
                $unsplashApiService?->getImagesByQuery($query, $lang) :
                $giphyApiService?->getImagesByQuery($query, $lang);

            return $this->json($images);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Download fetching images: ' . $e->getMessage()], 500);
        }
    }


    #[Route('/flashcard/sentences', name: 'app_flashcard_sentences')]
    public function sentences(
        SentenceService $sentenceService,
        Request $request,
        #[MapQueryParameter()] string $query = "",
        #[MapQueryParameter()] string $lang = "",
    ) {
        $token = $request->headers->get('X-CSRF-TOKEN');
        if (!$this->isCsrfTokenValid('get_sentences', $token)) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }

        if (empty($query)) {
            return $this->json([]);
        }

        try {
            return $this->json($sentenceService->getSentences($query, $lang));
        } catch (\Exception $e) {
            return $this->json(['error' => 'Download fetching sentences: ' . $e->getMessage()], 500);
        }
    }
}
