<?php

namespace App\Tests;

use App\Service\UnsplashApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;


class UnsplashApiServiceTest extends TestCase
{
    private const TEST_API_KEY = 'test_api_key';
    private const TEST_URL = 'https://api.unsplash.com/photos/123/download';

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
        $response = new JsonMockResponse('{"error": "Invalid language"}', [
            'http_code' => 400,
        ]);

        $service = $this->createServiceWithResponse($response);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(400);

        $service->getImagesByQuery("ball", "wrongLanguageCode");
    }

    public function testSuccessfulResponse(): void
    {
        $response = new JsonMockResponse(["results" => [
            [
                "urls" => [
                    "small" => "urls_small_value"
                ],
                "user" => [
                    "name" => "user_name_value",
                    "links" => [
                        "html" => "user_links_html_value"
                    ]
                ],
                "links" => [
                    "download_location" => "links_download_location_value"
                ],
                "id" => "id_value",
                "alt_description" => "alt_description_value",
                "source" => "source_value"
            ]
        ], ["http_code" => 200]]);

        $service = $this->createServiceWithResponse($response);
        $result = $service->getImagesByQuery("blue", "en");

        $this->assertCount(1, $result);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $firstImage = $result[0];
        $this->assertEquals("id_value", $firstImage->id);
        $this->assertEquals("urls_small_value", $firstImage->url);
        $this->assertEquals("alt_description_value", $firstImage->alt);
        $this->assertEquals("user_name_value", $firstImage->authorName);
        $this->assertEquals("user_links_html_value", $firstImage->authorProfileUrl);
        $this->assertEquals("links_download_location_value", $firstImage->downloadLocation);
    }


    public function testNetworkErrorHandling(): void
    {
        $response = new JsonMockResponse([], ['http_code' => 503]);
        $service = $this->createServiceWithResponse($response);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(503);
        $this->expectExceptionMessage("HTTP 503 returned for");
        $service->getImagesByQuery('query', 'en');
    }

    public function testInvalidJsonResponse(): void
    {
        $response = new JsonMockResponse('invalid json', ['http_code' => 200]);
        $service = $this->createServiceWithResponse($response);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid API response format');
        $service->getImagesByQuery('query', 'en');
    }


}
