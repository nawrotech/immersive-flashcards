<?php

namespace App\Tests;

use App\Service\UnsplashApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;


class UnsplashApiServiceTest extends TestCase
{
    public function testEmptyResponse(): void
    {
        $response = new JsonMockResponse(["results" => [], ["http_code" => 200]]);
        $client = new MockHttpClient([$response]);

        $unsplashApiService = new UnsplashApiService($client, "foo");

        $result = $unsplashApiService->getImagesByQuery("", "en");
        $this->assertEmpty($result);
    }

    public function testLackOfLangParameterThrowsException(): void
    {
        $response = new JsonMockResponse(["results" => []], ["http_code" => 400]);
        $client = new MockHttpClient([$response]);

        $unsplashApiService = new UnsplashApiService($client, "foo");

        try {
            $unsplashApiService->getImagesByQuery("ball", "wrongLanguageCode");
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
                    "small" => "localhost:8000"
                ],
                "id" => "foo",
                "alt" => "soemthing",
                "source" => "unsplash"
            ]
        ], ["http_code" => 200]]);

        $client = new MockHttpClient([$response]);
        $unsplashApiService = new UnsplashApiService($client, "foo");
        $result = $unsplashApiService->getImagesByQuery("blue", "en");

        $this->assertCount(1, $result);
    }
}
