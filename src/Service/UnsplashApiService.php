<?php

namespace App\Service;

use App\Contract\ImageProviderInterface;
use App\Dto\ImageDto;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UnsplashApiService implements ImageProviderInterface
{
    public const IMAGES_PER_PAGE = 12;

    public function __construct(
        private HttpClientInterface $client,
        #[Autowire(env: 'UNSPLASH_API_ACCESS_KEY')] private string $unsplashApiKey
    ) {}

    public function getImagesByQuery(string $query, ?string $lang = 'en'): array
    {
        $response = $this->client->request('GET', 'https://api.unsplash.com/search/photos', [
            'query' => [
                'client_id' => $this->unsplashApiKey,
                'query' => $query,
                'per_page' => self::IMAGES_PER_PAGE,
                'lang' => $lang
            ]
        ])->toArray();

        return array_map(
            function ($image) {
                return new ImageDto(
                    $image['id'],
                    $image['urls']['small'],
                    $image['alt_description'] ?? "",
                    'unsplash'
                );
            },
            $response['results']
        );
    }
}
