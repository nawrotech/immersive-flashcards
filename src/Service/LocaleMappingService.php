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

    public function getLanguagesMapping()
    {
        return array_map(fn($item) => $item['language'], $this->localeMappings);
    }

    public function getServiceMappings(array $serviceKeys): array
    {
        $result = [];
        foreach ($this->localeMappings as $locale => $data) {
            foreach ($serviceKeys as $serviceKey) {
                if (isset($this->localeMappings[$locale]['service_mappings'][$serviceKey])) {
                    $result[$locale][$serviceKey] = $this->localeMappings[$locale]['service_mappings'][$serviceKey];
                }
            }
        }

        return $result;
    }


    public function getKeyMapping(string $locale, string $key): ?array
    {
        return $this->getMapping($locale)[$key] ?? null;
    }

    public function getServiceMapping(string $locale, string $serviceName): ?string
    {
        return $this->getKeyMapping($locale, 'service_mappings')[$serviceName];
    }
}
