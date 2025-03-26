<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FlashcardControllerTest extends WebTestCase
{
    public function testReturnsImagesFromUnsplashWhenTypeIsImage(): void
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

    public function testReturnsGifsFromGiphyWhenTypeIsGif(): void
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

    public function testHandlesEmptyQuery(): void
    {
        $client = static::createClient();
        $client->request('GET', '/flashcard', [
            'flashcardType' => 'image',
            'lang' => 'en'
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
    }

    public function testHandlesDifferentLanguages(): void
    {
        $client = static::createClient();
        $client->request('GET', '/flashcard', [
            'query' => 'chat', // French for cat
            'flashcardType' => 'image',
            'lang' => 'fr'
        ]);

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
    }
}
