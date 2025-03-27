<?php

namespace App\Tests;

use App\Service\LocaleMappingService;
use PHPUnit\Framework\TestCase;

class LocaleMappingServiceTest extends TestCase
{
    private array $sampleMappings = [
        'en' => [
            'language' => 'english',
            'service_mappings' => [
                'sentence_service' => 'eng',
                'image_service' => 'en'
            ]
        ],
        'fr' => [
            'language' => 'french',
            'service_mappings' => [
                'sentence_service' => 'fra',
                'image_service' => 'fr'
            ]
        ]
    ];

    private LocaleMappingService $service;

    protected function setUp(): void
    {
        $this->service = new LocaleMappingService($this->sampleMappings);
    }

    public function testGetMappingForLocaleReturnsCorrectMapping(): void
    {
        $expected = [
            'language' => 'english',
            'service_mappings' => [
                'sentence_service' => 'eng',
                'image_service' => 'en'
            ]
        ];

        $result = $this->service->getMappingForLocale('en');

        $this->assertEquals($expected, $result);
    }

    public function testGetMappingForLocaleThrowsExceptionForInvalidLocale(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Locale "invalid" is not supported');

        $this->service->getMappingForLocale('invalid');
    }

    public function testGetKeyMappingForLocaleWithNonExistentKey(): void
    {
        $this->expectException(\ErrorException::class);
        $this->service->getKeyMappingForLocale('en', 'non_existent_key');
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

    public function testServiceMappingForLocaleReturnsCorrectMapping(): void
    {
        $expected = 'eng';

        $result = $this->service->getServiceMappingForLocale('en', 'sentence_service');

        $this->assertEquals($expected, $result);
    }


    public function testGetLanguagesMappingReturnsCorrectMapping(): void
    {
        $expected = ['en' => 'english', 'fr' => 'french'];

        $result = $this->service->getLanguagesMapping();

        $this->assertEquals($expected, $result);
    }

    public function testGetServiceMappingsReturnsCorrectMapping(): void
    {
        $expected = [
            'en' => ['sentence_service' => 'eng', 'image_service' => 'en'],
            'fr' => ['sentence_service' => 'fra', 'image_service' => 'fr']
        ];

        $result = $this->service->getServiceMappings(['sentence_service', 'image_service']);

        $this->assertEquals($expected, $result);
    }

    public function testGetServiceMappingsReturnsEmptyArrayForUnsupportedServices(): void
    {
        $result = $this->service->getServiceMappings(['unsupported_service']);

        $this->assertEquals([], $result);
    }

    public function testGetServiceMappingsReturnsEmptyArrayForEmptyServiceKeys(): void
    {
        $result = $this->service->getServiceMappings([]);
        $this->assertEquals([], $result);
    }

    /**
     * @dataProvider invalidLocaleDataProvider
     */
    public function testGetKeyMappingForLocaleWithInvalidLocaleFormat(string $invalidLocale): void
    {

        $this->expectException(\InvalidArgumentException::class);
        $this->service->getKeyMappingForLocale($invalidLocale, 'service_mappings');
    }

    public function invalidLocaleDataProvider(): array
    {
        return [
            'empty string' => [''],
            'too long locale' => ['en_US_POSIX_INVALID'],
            'invalid format' => ['en__US'],
            'special characters' => ['en@#']
        ];
    }
}
