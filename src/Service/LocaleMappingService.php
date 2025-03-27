<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LocaleMappingService
{
    private array $supportedLocales;

    public function __construct(
        #[Autowire(param: 'locale_mappings')]
        private array $localeMappings
    ) {
        $this->supportedLocales = array_keys($this->localeMappings);
    }

    public function getMappingForLocale(string $locale): ?array
    {
        if (!in_array($locale, $this->supportedLocales)) {
            throw new \InvalidArgumentException(sprintf('Locale "%s" is not supported', $locale));
        }

        return $this->localeMappings[$locale];
    }

    public function getKeyMappingForLocale(string $locale, string $key): ?array
    {
        $mapping = $this->getMappingForLocale($locale);
        if (!isset($mapping[$key])) {
            throw new \ErrorException(sprintf('Key "%s" does not exist in the mapping for locale "%s"', $key, $locale));
        }
        return $mapping[$key];
    }

    public function getServiceMappingForLocale(string $locale, string $serviceName): ?string
    {
        return $this->getKeyMappingForLocale($locale, 'service_mappings')[$serviceName];
    }

    public function getLanguagesMapping(): array
    {
        return array_map(fn($item) => $item['language'], $this->localeMappings);
    }

    public function getServiceMappings(array $serviceKeys): array
    {

        if (empty($serviceKeys)) {
            return [];
        }

        $result = [];
        foreach ($this->supportedLocales as $locale) {
            $serviceMappings = $this->getMappingForLocale($locale)['service_mappings'];
            foreach ($serviceKeys as $serviceKey) {
                if (isset($serviceMappings[$serviceKey])) {
                    $result[$locale][$serviceKey] = $serviceMappings[$serviceKey];
                }
            }
        }

        return $result;
    }
}
