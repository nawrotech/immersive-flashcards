<?php

namespace App\Tests\Unit\Mapper;

use App\Mapper\SentenceDtoMapper;
use App\Dto\Api\ApiSentenceDto;
use App\Dto\Domain\DomainSentenceDto;
use PHPUnit\Framework\TestCase;

class SentenceDtoMapperTest extends TestCase
{
    private SentenceDtoMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new SentenceDtoMapper();
    }

    public function testMapToDomainWithCompleteData(): void
    {
        $apiDto = new ApiSentenceDto(1, "Hello world", "John Doe", 123);

        $result = $this->mapper->mapToDomain($apiDto);

        $this->assertInstanceOf(DomainSentenceDto::class, $result);
        $this->assertEquals("Hello world", $result->text);
        $this->assertEquals("John Doe", $result->author);
        $this->assertEquals("https://tatoeba.org/en/audio/download/123", $result->audioUrl);
    }

    public function testMapToDomainWithNullValues(): void
    {
        $apiDto = new ApiSentenceDto(1, "Hello world", null, null);

        $result = $this->mapper->mapToDomain($apiDto);

        $this->assertInstanceOf(DomainSentenceDto::class, $result);
        $this->assertEquals("Hello world", $result->text);
        $this->assertNull($result->author);
        $this->assertNull($result->audioUrl);
    }
}
