<?php

namespace App\Service;

use App\Contract\ImageProviderInterface;
use App\Dto\ImageDto;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GiphyApiService implements ImageProviderInterface
{
    public const GIFS_PER_PAGE = 12;

    public function __construct(
        private HttpClientInterface $client,
        #[Autowire(env: 'GIPHY_API_KEY')] private string $giphyApiKey
    ) {}

    public function getImagesByQuery(string $query): array
    {
        $response = $this->client->request('GET', 'https://api.giphy.com/v1/gifs/search', [
            'query' => [
                'api_key' => $this->giphyApiKey,
                'q' => $query,
                'limit' => self::GIFS_PER_PAGE
            ]
        ])->toArray();

        return array_map(
            function ($gif) {
                return new ImageDto(
                    $gif['id'],
                    $gif['images']['original']['url'],
                    $gif['alt_text'] ?? "",
                    'giphy'
                );
            },
            $response['data']
        );
    }

    public function getImageById(string $gifId): ImageDto
    {

        $response = $this->client->request('GET', sprintf('https://api.giphy.com/v1/gifs/%s', $gifId), [
            'query' => [
                'api_key' => $this->giphyApiKey,

            ]
        ])->toArray();

        $gif = $response['data'];

        return new ImageDto(
            $gif['id'],
            $gif['images']['original']['url'],
            $gif['alt_text'],
            'giphy'
        );
    }
}
