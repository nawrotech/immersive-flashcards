<?php

namespace App\Service;

use App\Contract\ImageProviderInterface;
use App\Dto\Api\ApiGifDto;
use App\Exception\GiphyApiException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class GiphyApiService implements ImageProviderInterface
{
    public const GIFS_PER_PAGE = 12;

    public function __construct(
        private HttpClientInterface $client,
        #[Autowire(env: 'GIPHY_API_KEY')] private string $giphyApiKey
    ) {}

    public function getImagesByQuery(string $query, ?string $lang = 'es'): array
    {
        $response = $this->client->request('GET', 'https://api.giphy.com/v1/gifs/search', [
            'query' => [
                'api_key' => $this->giphyApiKey,
                'q' => $query,
                'limit' => self::GIFS_PER_PAGE,
                'lang' => $lang
            ]
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new GiphyApiException(sprintf(
                'Giphy API error: Received status code %d for query "%s" and lang "%s"',
                $response->getStatusCode(),
                $query,
                $lang
            ));
        }

        $responseData = $response->toArray();

        return array_map(
            function ($gif) {
                return new ApiGifDto(
                    $gif['id'],
                    $gif['images']['original']['mp4'],
                    $gif['images']['original']['url'],
                    $gif['alt_text'] ?? "",
                    'giphy'
                );
            },
            $responseData['data']
        );
    }
}
