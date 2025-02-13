<?php

namespace App\Dto\Api;

class ApiSentenceDto
{
    public function __construct(
        public int $id,
        public string $text,
        public ?string $author,
        public ?int $audioId
    ) {}
}
