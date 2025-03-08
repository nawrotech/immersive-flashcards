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


    public function testExceptionIsThrown(): void
    {
        $response = new JsonMockResponse(["data" => []], ["http_code" => 400]);
        $client = new MockHttpClient([$response]);

        $unsplashApiService = new GiphyApiService($client, "foo");

        try {
            $unsplashApiService->getImagesByQuery("ball", "en");
        } catch (\RuntimeException $e) {
            $this->assertEquals(400, $e->getCode());
        }
    }

    // public function testSuccessfulResponse(): void
    // {
    //     $response = new JsonMockResponse(["results" => [
    //         [
    //             "urls" => [
    //                 "small" => "localhost:8000"
    //             ],
    //             "id" => "foo",
    //             "alt" => "soemthing",
    //             "source" => "unsplash"
    //         ]
    //     ], ["http_code" => 200]]);

    //     $client = new MockHttpClient([$response]);
    //     $unsplashApiService = new GiphyApiService($client, "foo");
    //     $result = $unsplashApiService->getImagesByQuery("blue", "en");

    //     $this->assertCount(1, $result);
    // }
}
