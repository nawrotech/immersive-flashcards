<?php

namespace App\Service;

use App\Dto\Domain\DomainSentenceDto;
use App\Mapper\SentenceDtoMapper;

class SentenceService
{
    public function __construct(
        private TatoebaApiService $apiClient,
        private SentenceDtoMapper $mapper
    ) {}

    /**
     * @return DomainSentenceDto[]
     */
    public function getSentences(string $query, string $lang): array
    {
        $sentenceApiDtos = $this->apiClient->getSentences($query, $lang);
        return array_map(
            fn($apiDto) => $this->mapper->mapToDomain($apiDto),
            $sentenceApiDtos
        );
    }
}
