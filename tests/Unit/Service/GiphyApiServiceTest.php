<?php

namespace App\Tests;

use App\Service\GiphyApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;


class GiphyApiServiceTest extends TestCase
{
    public function testEmptyResponse(): void
    {
        $response = new JsonMockResponse(["data" => [], ["http_code" => 200]]);
        $client = new MockHttpClient([$response]);

        $unsplashApiService = new GiphyApiService($client, "foo");

        $result = $unsplashApiService->getImagesByQuery("", "en");
        $this->assertEmpty($result);
    }
}
