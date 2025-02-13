<?php

namespace App\Mapper;

use App\Dto\Api\ApiSentenceDto;
use App\Dto\Domain\DomainSentenceDto;

class SentenceDtoMapper
{
    public function mapToDomain(ApiSentenceDto $apiSentenceDto): DomainSentenceDto
    {
        return new DomainSentenceDto(
            text: $apiSentenceDto->text,
            author: $apiSentenceDto?->author,
            audioUrl: $this->generateAudioUrl($apiSentenceDto?->audioId)
        );
    }

    private function generateAudioUrl(?int $audioId): ?string
    {
        return $audioId ? "https://tatoeba.org/en/audio/download/$audioId" : null;
    }
}
