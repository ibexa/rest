<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Rest\Serializer;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Test\Core\IbexaKernelTestCase;
use Ibexa\Tests\Bundle\Rest\Functional\ResourceAssertionsTrait;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

final class SerializerTest extends IbexaKernelTestCase
{
    use ResourceAssertionsTrait;

    private const SNAPSHOT_DIR = __DIR__ . '/_snapshot';

    private Serializer $serializer;

    private LocationService $locationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->getIbexaTestCore()->getServiceByClassName(
            Serializer::class,
            'ibexa.rest.serializer',
        );
        $this->locationService = $this->getIbexaTestCore()->getServiceByClassName(
            LocationService::class,
        );
    }

    public function testSerializeTestDataObject(): void
    {
        $dataObject = new TestDataObject(
            'some_string',
            1,
        );

        $expectedData = [
            'string' => 'some_string',
            'int' => 1,
            'innerObject' => null,
            'location' => null,
        ];

        $serializedData = $this->serializer->serialize($dataObject, 'json');

        self::assertSame(json_encode($expectedData), $serializedData);
    }

    public function testNormalizeTestDataObjectWithApiLocation(): void
    {
        $dataObject = new TestDataObject(
            'some_string',
            1,
            null,
            $this->locationService->loadLocation(2),
        );

        $normalizedData = $this->serializer->normalize($dataObject);

        self::assertSame(
            'application/vnd.ibexa.api.Location+json',
            $normalizedData['location']['_media-type'] ?? [],
        );
        self::assertSame(
            '/api/ibexa/v2/content/locations/1/2',
            $normalizedData['location']['_href'] ?? [],
        );
        self::assertSame($normalizedData['location']['id'] ?? null, 2);

        self::assertArrayHasKey('Content', $normalizedData['location'] ?? []);
        self::assertSame(
            'application/vnd.ibexa.api.Content+json',
            $normalizedData['location']['Content']['_media-type'],
        );
    }

    public function testSerializeTestDataObjectWithApiLocation(): void
    {
        $dataObject = new TestDataObject(
            'some_string',
            1,
            null,
            $this->locationService->loadLocation(2),
        );

        $serializedData = $this->serializer->serialize($dataObject, 'xml');
        self::assertResponseMatchesXmlSnapshot(
            $serializedData,
            self::SNAPSHOT_DIR . '/TestDataObject.xml',
        );

        $serializedData = $this->serializer->serialize($dataObject, 'json');
        self::assertResponseMatchesJsonSnapshot(
            $serializedData,
            self::SNAPSHOT_DIR . '/TestDataObject.json',
        );
    }
}
