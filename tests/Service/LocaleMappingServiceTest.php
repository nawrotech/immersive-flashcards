<?php

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\LocaleMappingService;

class LocaleMappingServiceTest extends TestCase
{
    private $service;

    protected function setUp(): void
    {
        $this->service = new LocaleMappingService();
    }

    public function testGetKeyMappingForLocaleReturnsCorrectMapping(): void
    {
        $expected = [
            'sentence_service' => 'eng',
            'image_service' => 'en'
        ];

        $result = $this->service->getKeyMappingForLocale('en', 'service_mappings');

        $this->assertEquals($expected, $result);
    }

    public function testGetKeyMappingForLocaleThrowsExceptionForInvalidLocale(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Locale "invalid" is not supported');

        $this->service->getKeyMappingForLocale('invalid', 'service_mappings');
    }

    public function testGetKeyMappingForLocaleThrowsExceptionForInvalidKey(): void
    {
        $this->expectException(\ErrorException::class); // or \InvalidArgumentException depending on how you want to handle it

        $this->service->getKeyMappingForLocale('en', 'invalid_key');
    }
}
