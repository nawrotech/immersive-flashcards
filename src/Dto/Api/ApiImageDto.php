<?php

namespace App\Dto\Api;

class ApiImageDto
{
    public function __construct(
        public string $id,
        public string $url,
        public string $alt,
        public string $source,
    ) {}
}
