<?php

namespace App\Service;

use App\Dto\Api\ApiSentenceDto;
use App\Exception\TatoebaApiException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TatoebaApiService
{
    public function __construct(
        private HttpClientInterface $client,
    ) {}

    /**
     * @return ApiSentenceDto[]
     */
    public function getSentences(string $query, string $lang = 'eng', ?string $hasAudio = 'any'): array
    {
        $response = $this->client->request('GET', "https://tatoeba.org/en/api_v0/search", [
            'query' => [
                'from' => $lang,
                'to' => $lang,
                'query' => $query,
                'has_audio' => $hasAudio,
                'sort' => 'random',
                'direct' => 'only'
            ],
            "timeout" => 5.0
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new TatoebaApiException(sprintf(
                'Tatoeba API error: Received status code %d for query "%s" and lang "%s"',
                $response->getStatusCode(),
                $query,
                $lang
            ));
        }

        $responseData = $response->toArray();

        return array_map(
            function ($sentence) {
                return new ApiSentenceDto(
                    $sentence['id'],
                    $sentence['text'],
                    $sentence['audios'][0]['author'] ?? null,
                    $sentence['audios'][0]['id'] ?? null,
                );
            },
            $responseData['results']
        );
    }
}
