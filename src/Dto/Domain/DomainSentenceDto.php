<?php

namespace App\Dto\Domain;

class DomainSentenceDto
{
    public function __construct(
        public string $text,
        public ?string $author = null,
        public ?string $audioUrl = null
    ) {}
}
