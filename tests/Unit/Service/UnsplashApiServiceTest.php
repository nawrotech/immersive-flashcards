<?php

namespace App\Tests;

use App\Service\UnsplashApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;


class UnsplashApiServiceTest extends TestCase
{
    private const TEST_API_KEY = 'test_api_key';

    protected function createServiceWithResponse(JsonMockResponse $response): UnsplashApiService
    {
        $client = new MockHttpClient([$response]);
        return new UnsplashApiService($client, self::TEST_API_KEY);
    }

    public function testEmptyResponse(): void
    {
        $response = new JsonMockResponse(["results" => [], ["http_code" => 200]]);
        $service = $this->createServiceWithResponse($response);

        $result = $service->getImagesByQuery("", "en");
        $this->assertEmpty($result);
    }

    public function testLackOfLangParameterThrowsException(): void
    {
        $response = new JsonMockResponse(["results" => []], ["http_code" => 400]);
        $service = $this->createServiceWithResponse($response);

        try {
            $service->getImagesByQuery("ball", "wrongLanguageCode");
            $this->fail('Expected exception was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals(400, $e->getCode());
        }
    }

    public function testSuccessfulResponse(): void
    {
        $response = new JsonMockResponse(["results" => [
            [
                "urls" => [
                    "small" => "https://example.com/image.jpg"
                ],
                "id" => "foo",
                "alt_description" => "soemthing",
                "source" => "unsplash"
            ]
        ], ["http_code" => 200]]);

        $service = $this->createServiceWithResponse($response);
        $result = $service->getImagesByQuery("blue", "en");

        $this->assertCount(1, $result);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $firstImage = $result[0];
        $this->assertEquals("foo", $firstImage->id);
        $this->assertEquals("https://example.com/image.jpg", $firstImage->url);
        $this->assertEquals("soemthing", $firstImage->alt);
        $this->assertEquals("unsplash", $firstImage->source);
    }


    public function testNetworkErrorHandling(): void
    {
        $response = new JsonMockResponse([], ['http_code' => 503]);
        $service = $this->createServiceWithResponse($response);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(503);
        $service->getImagesByQuery('query', 'en');
    }

    public function testInvalidJsonResponse(): void
    {
        $response = new JsonMockResponse('invalid json', ['http_code' => 200]);
        $service = $this->createServiceWithResponse($response);

        $this->expectException(\Symfony\Component\HttpClient\Exception\JsonException::class);
        $this->expectExceptionMessage('JSON content was expected to decode to an array');
        $service->getImagesByQuery('query', 'en');
    }
}
