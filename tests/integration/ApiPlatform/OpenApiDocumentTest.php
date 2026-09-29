<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Rest\ApiPlatform;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use Ibexa\Contracts\Test\Core\IbexaKernelTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Builds the whole REST OpenAPI document, the same way `ibexa:openapi` does,
 * and checks that every schema reference in it points to a registered schema.
 */
#[CoversNothing]
final class OpenApiDocumentTest extends IbexaKernelTestCase
{
    private const string SCHEMA_REFERENCE_PREFIX = '#/components/schemas/';

    private const string COMPONENT_SCHEMA_LOCATION_PREFIX = '/components/schemas/';

    /**
     * Schemas registered by ibexa/product-catalog, which this kernel does not install,
     * keyed by the component allowed to reference them (`SummaryEntry` is used by ibexa/cart).
     *
     * @var array<string, list<string>>
     */
    private const array SCHEMAS_REGISTERED_BY_PRODUCT_CATALOG = [
        'SummaryEntry' => [
            'Product',
            'RestPriceWrapper',
            'VatCategory',
        ],
    ];

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testAllSchemaReferencesResolve(): void
    {
        $document = $this->buildDocument();

        self::assertIsArray($document['paths']);
        self::assertNotEmpty($document['paths'], 'The OpenAPI document has no paths.');
        self::assertIsArray($document['components']);
        self::assertIsArray($document['components']['schemas']);
        $schemas = $document['components']['schemas'];

        $unresolved = [];
        foreach ($this->collectReferences($document) as $location => $reference) {
            if (!str_starts_with($reference, self::SCHEMA_REFERENCE_PREFIX)) {
                $unresolved[] = sprintf('%s: %s (not a schema reference)', $location, $reference);
                continue;
            }

            $schemaName = substr($reference, strlen(self::SCHEMA_REFERENCE_PREFIX));
            if (
                !array_key_exists($schemaName, $schemas)
                && !$this->isRegisteredByProductCatalog($location, $schemaName)
            ) {
                $unresolved[] = sprintf('%s: %s', $location, $reference);
            }
        }

        self::assertSame([], $unresolved, 'Unresolved references in the OpenAPI document.');
    }

    private function isRegisteredByProductCatalog(string $location, string $schemaName): bool
    {
        foreach (self::SCHEMAS_REGISTERED_BY_PRODUCT_CATALOG as $component => $schemaNames) {
            if (
                str_starts_with($location, self::COMPONENT_SCHEMA_LOCATION_PREFIX . $component . '/')
                && in_array($schemaName, $schemaNames, true)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDocument(): array
    {
        $factory = self::getContainer()->get('ibexa.api_platform.ibexa_openapi.factory');
        self::assertInstanceOf(OpenApiFactoryInterface::class, $factory);

        $normalizer = self::getContainer()->get('api_platform.openapi.normalizer');
        self::assertInstanceOf(NormalizerInterface::class, $normalizer);

        $document = $normalizer->normalize($factory(), 'json');
        self::assertIsArray($document);

        /** @var array<string, mixed> $document */
        return $document;
    }

    /**
     * @param array<mixed> $node
     *
     * @return iterable<string, string>
     */
    private function collectReferences(array $node, string $path = ''): iterable
    {
        foreach ($node as $key => $value) {
            $location = $path . '/' . $key;
            if ($key === '$ref' && is_string($value)) {
                yield $path => $value;
            } elseif (is_array($value)) {
                yield from $this->collectReferences($value, $location);
            }
        }
    }
}
