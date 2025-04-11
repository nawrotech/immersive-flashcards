<?php

namespace App\Tests;

use App\Exception\GiphyApiException;
use App\Service\GiphyApiService;
use JsonException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

class GiphyApiServiceTest extends TestCase
{
    private const TEST_API_KEY = 'test_api_key';

    protected function createServiceWithResponse(JsonMockResponse $response): GiphyApiService
    {
        $client = new MockHttpClient([$response]);
        return new GiphyApiService($client, self::TEST_API_KEY);
    }

    public function testEmptyResponse(): void
    {
        $response = new JsonMockResponse(["data" => [], ["http_code" => 200]]);
        $service = $this->createServiceWithResponse($response);

        $result = $service->getImagesByQuery("", "en");
        $this->assertEmpty($result);
    }

    public function testFailedResponseThrowsGiphyApiException(): void
    {
        $response = new JsonMockResponse(["data" => []], ["http_code" => 500]);
        $service = $this->createServiceWithResponse($response);

        $this->expectException(GiphyApiException::class);
        $this->expectExceptionMessage('Giphy API error: Received status code 500 for query "query" and lang "lang"');
        $service->getImagesByQuery("query", "lang");
    }

    public function testSuccessfulResponse(): void
    {
        $response = new JsonMockResponse(["data" => [
            [
                "id" => "foo",
                "images" => [
                    "original" => [
                        "mp4" => "https://example.com/gif.mp4",
                        "url" => "https://example.com/gif.gif"
                    ]
                ],
                "alt_text" => "something",
                "source" => "giphy"
            ]
        ]]);

        $service = $this->createServiceWithResponse($response);
        $result = $service->getImagesByQuery("blue", "en");

        $this->assertCount(1, $result);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $firstImage = $result[0];
        $this->assertEquals("foo", $firstImage->id);
        $this->assertEquals("https://example.com/gif.gif", $firstImage->imageUrl);
        $this->assertEquals("https://example.com/gif.mp4", $firstImage->videoUrl);
        $this->assertEquals("something", $firstImage->alt);
        $this->assertEquals("giphy", $firstImage->source);
    }

    public function testInvalidJsonResponse(): void
    {
        $response = new JsonMockResponse('invalid json', ['http_code' => 200]);
        $service = $this->createServiceWithResponse($response);

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('JSON content was expected to decode to an array');
        $service->getImagesByQuery('query', 'en');
    }
}
