<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FlashcardControllerTest extends WebTestCase
{
    // Images endpoint tests
    public function testImagesEndpointReturnsUnsplashImagesWhenTypeIsImage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/flashcard', [
            'query' => 'cat',
            'flashcardType' => 'image',
            'lang' => 'en'
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertNotEmpty($response);
    }

    public function testImagesEndpointReturnsGifsFromGiphyWhenTypeIsGif(): void
    {
        $client = static::createClient();
        $client->request('GET', '/flashcard', [
            'query' => 'cat',
            'flashcardType' => 'gif',
            'lang' => 'en'
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertNotEmpty($response);
    }

    public function testImagesEndpointReturnsEmptyArrayForEmptyQuery(): void
    {
        $client = static::createClient();
        $client->request('GET', '/flashcard', [
            'flashcardType' => 'image',
            'lang' => 'en'
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame([], $response);
    }

    // Sentences endpoint tests
    public function testSentencesEndpointReturnsSentencesForValidQuery(): void
    {
        $client = static::createClient();
        $client->request('GET', '/flashcard/sentences', [
            'query' => 'cat',
            'lang' => 'eng'
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertNotEmpty($response);
    }

    public function testSentencesEndpointReturnsEmptyArrayForEmptyQuery(): void
    {
        $client = static::createClient();
        $client->request('GET', '/flashcard/sentences', [
            'lang' => 'eng'
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame([], $response);

        $client->request('GET', '/flashcard/sentences', [
            'query' => '',
            'lang' => 'eng'
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame([], $response);
    }

    public function testSentencesEndpointHandlesMultipleLanguages(): void
    {
        $client = static::createClient();
        $client->request('GET', '/flashcard/sentences', [
            'query' => 'chat',
            'lang' => 'fra'
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertNotEmpty($response);
    }
}
