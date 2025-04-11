<?php

namespace App\Controller;

use App\Service\UnsplashApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_USER')]
class UnsplashController extends AbstractController
{
    #[Route('/api/unsplash/download', name: 'app_unsplash_download_location', methods: ['GET'])]
    public function downloadLocation(
        Request $request,
        UnsplashApiService $unsplashApiService,
        ValidatorInterface $validator
    ): JsonResponse {

        $token = $request->headers->get('X-CSRF-TOKEN');
        if (!$this->isCsrfTokenValid('get_images', $token)) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }

        $encodedUrl = $request->query->get('url');
        $decodedUrl = urldecode($encodedUrl);

        $errors = $validator->validate(
            $decodedUrl,
            [
                new NotBlank(),
                new Url(),
                new Regex('#^https://api\.unsplash\.com/photos/[a-zA-Z0-9_-]+/download(\?.*)?$#'),
            ]
        );

        if ($errors->count() > 0) {
            return $this->json(['error' => 'Invalid Unsplash URL'], 400);
        }

        try {
            $response = $unsplashApiService->getDownloadLocationLink($decodedUrl);
            return $this->json($response);
        } catch (\Exception) {
            return $this->json(['error' => 'Download failed'], 500);
        }
    }
}
