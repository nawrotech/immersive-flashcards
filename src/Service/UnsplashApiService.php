<?php

namespace App\Service;

use Unsplash\HttpClient;
use Unsplash\Photo;
use Unsplash\Search;

class UnsplashApiService
{
    public function __construct()
    {
        HttpClient::init([
            'applicationId'    => 'jYL_BA4S0dDVx2LlCE6h_9NuIUE6oVsGgT7HER-5jrM',
            'secret'    => 'rli4eBYepjjy0wGIqA_U5-QyvxbsR6IGKCc2aYeb0yE',
            'utmSource' => 'immersive_flashcards',
        ]);
    }

    public function searchImages(string $search = 'chocolate')
    {

        $page = 1;
        $per_page = 12;
        $orientation = 'landscape';

        return Search::photos($search, $page, $per_page, $orientation);
    }

    public function getImageById(string $photoId = "vdx5hPQhXFk")
    {
        return Photo::find($photoId);
    }
}
