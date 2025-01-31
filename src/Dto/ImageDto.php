<?php

namespace App\Dto;

class ImageDto
{
    public function __construct(
        public string $id,
        public string $url,
        public string $alt,
        public string $source,
    ) {}
}
