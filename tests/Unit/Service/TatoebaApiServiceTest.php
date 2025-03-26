<?php

namespace App\Tests\Unit\Service;

use App\Service\TatoebaApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

class TatoebaApiServiceTest extends TestCase
{
    private function createServiceWithResponse(JsonMockResponse $response): TatoebaApiService
    {
        $client = new MockHttpClient([$response]);
        return new TatoebaApiService($client);
    }

    public function testGetSentencesReturnsCorrectlyMappedData(): void
    {
        $mockResponse = new JsonMockResponse([
            'results' => [
                [
                    'id' => 1234,
                    'text' => 'Hello world',
                    'audios' => [
                        [
                            'author' => 'John Doe',
                            'id' => 5678
                        ]
                    ]
                ]
            ]
        ]);

        $service = $this->createServiceWithResponse($mockResponse);

        $result = $service->getSentences('test query');

        $this->assertCount(1, $result);
        $this->assertEquals(1234, $result[0]->id);
        $this->assertEquals('Hello world', $result[0]->text);
        $this->assertEquals('John Doe', $result[0]->author);
        $this->assertEquals(5678, $result[0]->audioId);
    }

    public function testGetSentencesWithNoAudioData(): void
    {

        $mockResponse = new JsonMockResponse([
            'results' => [
                [
                    'id' => 1234,
                    'text' => 'Hello world',
                    'audios' => []
                ]
            ]
        ]);

        $service = $this->createServiceWithResponse($mockResponse);
        $result = $service->getSentences('test query');

        $this->assertNull($result[0]->author);
        $this->assertNull($result[0]->audioId);
    }

    public function testEmptyResponse(): void
    {
        $response = new JsonMockResponse(['results' => []], ['http_code' => 200]);
        $service = $this->createServiceWithResponse($response);

        $result = $service->getSentences('nonexistent');
        $this->assertEmpty($result);
    }

    public function testNetworkErrorHandling(): void
    {
        $response = new JsonMockResponse([], ['http_code' => 503]);
        $service = $this->createServiceWithResponse($response);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(503);
        $service->getSentences('query');
    }

    public function testInvalidJsonResponse(): void
    {
        $response = new JsonMockResponse('invalid json', ['http_code' => 200]);
        $service = $this->createServiceWithResponse($response);

        $this->expectException(\Symfony\Component\HttpClient\Exception\JsonException::class);
        $this->expectExceptionMessage('JSON content was expected to decode to an array');
        $service->getSentences('query');
    }
}
