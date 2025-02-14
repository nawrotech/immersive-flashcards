<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LocaleMappingService
{
    public function __construct(#[Autowire(param: 'locale_mappings')] private array $localeMappings) {}

    public function getMapping(string $locale): ?array
    {

        return $this->localeMappings[$locale] ?? null;
    }
}
