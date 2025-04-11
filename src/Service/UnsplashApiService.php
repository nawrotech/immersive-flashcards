<?php

namespace App\Service;

use App\Contract\ImageProviderInterface;
use App\Dto\Api\ApiImageDto;
use App\Exception\UnsplashApiException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UnsplashApiService implements ImageProviderInterface
{
    public const IMAGES_PER_PAGE = 12;

    public function __construct(
        private HttpClientInterface $client,
        #[Autowire(env: 'UNSPLASH_API_ACCESS_KEY')] private string $unsplashApiKey
    ) {}

    public function getImagesByQuery(string $query, ?string $lang): array
    {
        $response = $this->client->request('GET', 'https://api.unsplash.com/search/photos', [
            'query' => [
                'client_id' => $this->unsplashApiKey,
                'query' => $query,
                'per_page' => self::IMAGES_PER_PAGE,
                'lang' => $lang,
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new UnsplashApiException(sprintf(
                'Unsplash API error: Received status code %d for query "%s" and lang "%s"',
                $response->getStatusCode(),
                $query,
                $lang
            ));
        }

        $responseData = $response->toArray();

        return array_map(
            function ($image) {
                return new ApiImageDto(
                    $image['id'],
                    $image['urls']['small'],
                    $image['user']['name'],
                    $image['user']['links']['html'],
                    $image['alt_description'] ?? "",
                    $image["links"]["download_location"],
                    'unsplash'
                );
            },
            $responseData['results']
        );
    }

    public function getDownloadLocationLink(string $url)
    {
        $response = $this->client->request('GET', $url, [
            'query' => ['client_id' => $this->unsplashApiKey],
            "timeout" => 5.0
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new UnsplashApiException(sprintf(
                'Unsplash API error: Received status code %d for url %s',
                $response->getStatusCode(),
                $url,
            ));
        }

        return $response->toArray();
    }
}
