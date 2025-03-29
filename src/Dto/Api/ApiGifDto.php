<?php

namespace App\Dto\Api;

class ApiGifDto
{
    public function __construct(
        public string $id,
        public string $videoUrl,
        public string $imageUrl,
        public string $alt,
        public string $source,
    ) {}
}
