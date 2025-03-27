<?php

namespace App\Tests\Unit\Service;

use App\Service\SentenceService;
use App\Service\TatoebaApiService;
use App\Mapper\SentenceDtoMapper;
use App\Dto\Api\ApiSentenceDto;
use App\Dto\Domain\DomainSentenceDto;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SentenceServiceTest extends TestCase
{
    private TatoebaApiService|MockObject $apiClient;
    private SentenceDtoMapper|MockObject $mapper;
    private SentenceService $service;

    protected function setUp(): void
    {
        $this->apiClient = $this->createMock(TatoebaApiService::class);
        $this->mapper = $this->createMock(SentenceDtoMapper::class);

        assert($this->apiClient instanceof TatoebaApiService);
        assert($this->mapper instanceof SentenceDtoMapper);

        $this->service = new SentenceService($this->apiClient, $this->mapper);
    }

    public function testGetSentencesSuccessfullyMapsMultipleResults(): void
    {

        $query = "hello";
        $lang = "eng";

        $apiDto1 = new ApiSentenceDto(
            id: 1,
            text: "Hello, world!",
            author: "John Doe",
            audioId: 123
        );
        $apiDto2 = new ApiSentenceDto(
            id: 2,
            text: "Hello there!",
            author: null,
            audioId: null
        );
        $apiDtos = [$apiDto1, $apiDto2];

        $domainDto1 = new DomainSentenceDto(
            text: "Hello, world!",
            author: "John Doe",
            audioUrl: "https://audio.tatoeba.org/123.mp3"
        );
        $domainDto2 = new DomainSentenceDto(
            text: "Hello there!",
            author: null,
            audioUrl: null
        );

        $this->apiClient
            ->expects($this->once())
            ->method('getSentences')
            ->with($query, $lang)
            ->willReturn($apiDtos);

        $this->mapper
            ->expects($this->exactly(2))
            ->method('mapToDomain')
            ->willReturnOnConsecutiveCalls($domainDto1, $domainDto2);


        $result = $this->service->getSentences($query, $lang);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(DomainSentenceDto::class, $result);
        $this->assertEquals("Hello, world!", $result[0]->text);
        $this->assertEquals("John Doe", $result[0]->author);
        $this->assertEquals("https://audio.tatoeba.org/123.mp3", $result[0]->audioUrl);
        $this->assertEquals("Hello there!", $result[1]->text);
        $this->assertNull($result[1]->author);
        $this->assertNull($result[1]->audioUrl);
    }

    public function testGetSentencesReturnsEmptyArrayWhenNoResults(): void
    {

        $query = "nonexistentquery";
        $lang = "eng";

        $this->apiClient
            ->expects($this->once())
            ->method('getSentences')
            ->with($query, $lang)
            ->willReturn([]);


        $result = $this->service->getSentences($query, $lang);

        $this->assertEmpty($result);
    }

    public function testGetSentencesWithNullableFields(): void
    {

        $query = "test";
        $lang = "fra";

        $apiDto = new ApiSentenceDto(
            id: 3,
            text: "Bonjour!",
            author: null,
            audioId: null
        );

        $domainDto = new DomainSentenceDto(
            text: "Bonjour!",
            author: null,
            audioUrl: null
        );

        $this->apiClient
            ->expects($this->once())
            ->method('getSentences')
            ->with($query, $lang)
            ->willReturn([$apiDto]);

        $this->mapper
            ->expects($this->once())
            ->method('mapToDomain')
            ->with($apiDto)
            ->willReturn($domainDto);


        $result = $this->service->getSentences($query, $lang);


        $this->assertCount(1, $result);
        $this->assertEquals("Bonjour!", $result[0]->text);
        $this->assertNull($result[0]->author);
        $this->assertNull($result[0]->audioUrl);
    }
}
