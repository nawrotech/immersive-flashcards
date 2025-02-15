<?php

namespace App\Service;

use App\Dto\Api\ApiSentenceDto;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TatoebaApiService
{
    public function __construct(
        private HttpClientInterface $client,
    ) {}

    /**
     * @return ApiSentenceDto[]
     */
    public function getSentences(string $query, ?string $lang = 'eng', ?string $hasAudio = 'yes'): array
    {
        $response = $this->client->request('GET', "https://tatoeba.org/en/api_v0/search", [
            'query' => [
                'from' => $lang,
                'to' => $lang,
                'query' => $query,
                'has_audio' => $hasAudio,
                'sort' => 'random',
                'direct' => 'only'
            ]
        ])->toArray();

        return array_map(
            function ($sentence) {
                return new ApiSentenceDto(
                    $sentence['id'],
                    $sentence['text'],
                    $sentence['audios'][0]['author'] ?? null,
                    $sentence['audios'][0]['id'] ?? null,
                );
            },
            $response['results']
        );
    }
}
