<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\ApiPlatform;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;
use Ibexa\Bundle\Rest\ApiPlatform\EditionBadge\EditionBadgeFactoryInterface;
use Ibexa\Bundle\Rest\ApiPlatform\OpenApiFactory;
use Ibexa\Bundle\Rest\ApiPlatform\SchemasCollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @covers \Ibexa\Bundle\Rest\ApiPlatform\OpenApiFactory
 */
final class OpenApiFactoryTest extends TestCase
{
    private const EXAMPLE_REQUEST_FILE = __DIR__ . '/Fixtures/examples/test-request.json.example';
    private const EXAMPLE_REQUEST_XML_FILE = __DIR__ . '/Fixtures/examples/test-request.xml.example';
    private const EXAMPLE_RESPONSE_JSON_FILE = __DIR__ . '/Fixtures/examples/test-response.json.example';
    private const EXAMPLE_RESPONSE_XML_FILE = __DIR__ . '/Fixtures/examples/test-response.xml.example';

    private OpenApiFactory $factory;

    /** @var \ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface&\PHPUnit\Framework\MockObject\MockObject */
    private OpenApiFactoryInterface $decoratedFactory;

    private KernelInterface $kernel;

    protected function setUp(): void
    {
        $this->decoratedFactory = $this->getDecoratedFactoryMock();
        $schemasCollectionFactory = new SchemasCollectionFactory();
        $this->kernel = $this->getKernelMock();
        $editionBadgeFactory = $this->getEditionBadgeFactoryMock();

        $this->factory = new OpenApiFactory(
            $this->decoratedFactory,
            $schemasCollectionFactory,
            $this->kernel,
            $editionBadgeFactory,
            '/api/ibexa/v2'
        );
    }

    public function testInjectsJsonRequestExampleFromFile(): void
    {
        // Given: An operation with x-ibexa-example-file in request body
        $requestBody = new RequestBody(
            description: 'Login request',
            content: new ArrayObject([
                'application/json' => [
                    'schema' => ['$ref' => '#/components/schemas/LoginRequest'],
                    'x-ibexa-example-file' => '@TestBundle/examples/request.json',
                ],
            ])
        );

        $operation = new Operation(
            requestBody: $requestBody,
            responses: [
                '200' => ['description' => 'Success'],
            ]
        );

        $paths = new Paths();
        $paths->addPath('/test', new PathItem(post: $operation));

        $openApi = new OpenApi(
            info: new Info(title: 'Test API', version: '1.0'),
            servers: [],
            paths: $paths
        );

        $this->decoratedFactory
            ->expects(self::once())
            ->method('__invoke')
            ->willReturn($openApi);

        // When: Factory processes the OpenAPI spec
        $result = ($this->factory)([]);

        // Then: The request example is injected from file
        $processedPath = $result->getPaths()->getPath('/test');
        self::assertNotNull($processedPath);
        $processedOperation = $processedPath->getPost();
        self::assertNotNull($processedOperation);
        $processedRequestBody = $processedOperation->getRequestBody();

        self::assertNotNull($processedRequestBody);
        $content = $processedRequestBody->getContent();
        self::assertNotNull($content);

        $jsonContent = $content['application/json'];

        // Example should be injected
        self::assertArrayHasKey('example', $jsonContent);
        self::assertEquals([
            'username' => 'admin',
            'password' => 'secret',
        ], $jsonContent['example']);

        // x-ibexa-example-file should be removed
        self::assertArrayNotHasKey('x-ibexa-example-file', $jsonContent);
    }

    public function testInjectsXmlRequestExampleFromFile(): void
    {
        // Given: An operation with XML request example
        $requestBody = new RequestBody(
            description: 'Login request',
            content: new ArrayObject([
                'application/xml' => [
                    'schema' => ['$ref' => '#/components/schemas/LoginRequest'],
                    'x-ibexa-example-file' => '@TestBundle/examples/request.xml',
                ],
            ])
        );

        $operation = new Operation(
            requestBody: $requestBody,
            responses: [
                '200' => ['description' => 'Success'],
            ]
        );

        $paths = new Paths();
        $paths->addPath('/test', new PathItem(post: $operation));

        $openApi = new OpenApi(
            info: new Info(title: 'Test API', version: '1.0'),
            servers: [],
            paths: $paths
        );

        $this->decoratedFactory
            ->expects(self::once())
            ->method('__invoke')
            ->willReturn($openApi);

        // When: Factory processes the OpenAPI spec
        $result = ($this->factory)([]);

        // Then: The XML request example is injected as string
        $processedPath = $result->getPaths()->getPath('/test');
        self::assertNotNull($processedPath);
        $processedOperation = $processedPath->getPost();
        self::assertNotNull($processedOperation);
        $processedRequestBody = $processedOperation->getRequestBody();

        self::assertNotNull($processedRequestBody);
        $content = $processedRequestBody->getContent();
        self::assertNotNull($content);

        $xmlContent = $content['application/xml'];

        // Example should be injected as string
        self::assertArrayHasKey('example', $xmlContent);
        self::assertIsString($xmlContent['example']);
        self::assertStringContainsString('<LoginRequest>', $xmlContent['example']);
        self::assertStringContainsString('<username>admin</username>', $xmlContent['example']);

        // x-ibexa-example-file should be removed
        self::assertArrayNotHasKey('x-ibexa-example-file', $xmlContent);
    }

    public function testInjectsMultipleMediaTypeRequestExamples(): void
    {
        // Given: An operation with both JSON and XML request examples
        $requestBody = new RequestBody(
            description: 'Login request',
            content: new ArrayObject([
                'application/json' => [
                    'schema' => ['$ref' => '#/components/schemas/LoginRequest'],
                    'x-ibexa-example-file' => '@TestBundle/examples/request.json',
                ],
                'application/xml' => [
                    'schema' => ['$ref' => '#/components/schemas/LoginRequest'],
                    'x-ibexa-example-file' => '@TestBundle/examples/request.xml',
                ],
            ])
        );

        $operation = new Operation(
            requestBody: $requestBody,
            responses: [
                '200' => ['description' => 'Success'],
            ]
        );

        $paths = new Paths();
        $paths->addPath('/test', new PathItem(post: $operation));

        $openApi = new OpenApi(
            info: new Info(title: 'Test API', version: '1.0'),
            servers: [],
            paths: $paths
        );

        $this->decoratedFactory
            ->expects(self::once())
            ->method('__invoke')
            ->willReturn($openApi);

        // When: Factory processes the OpenAPI spec
        $result = ($this->factory)([]);

        // Then: Both examples are injected correctly
        $processedRequestBody = $result->getPaths()->getPath('/test')?->getPost()?->getRequestBody();

        self::assertNotNull($processedRequestBody);
        $content = $processedRequestBody->getContent();
        self::assertNotNull($content);

        // Verify JSON example
        $jsonContent = $content['application/json'];
        self::assertArrayHasKey('example', $jsonContent);
        self::assertEquals([
            'username' => 'admin',
            'password' => 'secret',
        ], $jsonContent['example']);
        self::assertArrayNotHasKey('x-ibexa-example-file', $jsonContent);

        // Verify XML example
        $xmlContent = $content['application/xml'];
        self::assertArrayHasKey('example', $xmlContent);
        self::assertIsString($xmlContent['example']);
        self::assertStringContainsString('<LoginRequest>', $xmlContent['example']);
        self::assertArrayNotHasKey('x-ibexa-example-file', $xmlContent);
    }

    public function testThrowsExceptionWhenRequestExampleFileNotFound(): void
    {
        // Given: An operation with invalid request example file path
        $requestBody = new RequestBody(
            description: 'Login request',
            content: new ArrayObject([
                'application/json' => [
                    'schema' => ['$ref' => '#/components/schemas/LoginRequest'],
                    'x-ibexa-example-file' => '@TestBundle/examples/non-existent.json',
                ],
            ])
        );

        $operation = new Operation(
            requestBody: $requestBody,
            responses: [
                '200' => ['description' => 'Success'],
            ]
        );

        $paths = new Paths();
        $paths->addPath('/test', new PathItem(post: $operation));

        $openApi = new OpenApi(
            info: new Info(title: 'Test API', version: '1.0'),
            servers: [],
            paths: $paths
        );

        $this->decoratedFactory
            ->expects(self::once())
            ->method('__invoke')
            ->willReturn($openApi);

        // Then: Exception is thrown for non-existent file
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown resource: @TestBundle/examples/non-existent.json');

        // When: Factory processes the OpenAPI spec
        ($this->factory)([]);
    }

    public function testThrowsExceptionWhenRequestExampleJsonIsInvalid(): void
    {
        // Create a temporary invalid JSON file
        $invalidJsonFile = __DIR__ . '/Fixtures/examples/invalid.json.example';
        file_put_contents($invalidJsonFile, '{invalid json}');

        try {
            // Update kernel mock to include the invalid file
            $kernel = $this->getKernelMockWithCustomResourceMap([
                '@TestBundle/examples/invalid.json' => $invalidJsonFile,
            ]);

            $factory = new OpenApiFactory(
                $this->decoratedFactory,
                new SchemasCollectionFactory(),
                $kernel,
                $this->getEditionBadgeFactoryMock(),
                '/api/ibexa/v2'
            );

            // Given: An operation with invalid JSON example file
            $requestBody = new RequestBody(
                description: 'Login request',
                content: new ArrayObject([
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/LoginRequest'],
                        'x-ibexa-example-file' => '@TestBundle/examples/invalid.json',
                    ],
                ])
            );

            $operation = new Operation(
                requestBody: $requestBody,
                responses: [
                    '200' => ['description' => 'Success'],
                ]
            );

            $paths = new Paths();
            $paths->addPath('/test', new PathItem(post: $operation));

            $openApi = new OpenApi(
                info: new Info(title: 'Test API', version: '1.0'),
                servers: [],
                paths: $paths
            );

            $this->decoratedFactory
                ->expects(self::once())
                ->method('__invoke')
                ->willReturn($openApi);

            // Then: JsonException is thrown for invalid JSON
            $this->expectException(\JsonException::class);

            // When: Factory processes the OpenAPI spec
            $factory([]);
        } finally {
            // Cleanup
            if (file_exists($invalidJsonFile)) {
                unlink($invalidJsonFile);
            }
        }
    }

    private function getDecoratedFactoryMock(): OpenApiFactoryInterface&MockObject
    {
        return $this->createMock(OpenApiFactoryInterface::class);
    }

    private function getKernelMock(): KernelInterface&MockObject
    {
        $kernelMock = $this->createMock(KernelInterface::class);

        $kernelMock
            ->method('locateResource')
            ->willReturnCallback(static function (string $path) {
                return match ($path) {
                    '@TestBundle/examples/request.json' => self::EXAMPLE_REQUEST_FILE,
                    '@TestBundle/examples/request.xml' => self::EXAMPLE_REQUEST_XML_FILE,
                    '@TestBundle/examples/response.json' => self::EXAMPLE_RESPONSE_JSON_FILE,
                    '@TestBundle/examples/response.xml' => self::EXAMPLE_RESPONSE_XML_FILE,
                    default => throw new \InvalidArgumentException("Unknown resource: $path"),
                };
            });

        return $kernelMock;
    }

    private function getEditionBadgeFactoryMock(): EditionBadgeFactoryInterface&MockObject
    {
        $editionBadgeFactoryMock = $this->createMock(EditionBadgeFactoryInterface::class);

        $editionBadgeFactoryMock
            ->method('getBadgesForOperation')
            ->willReturn([]);

        return $editionBadgeFactoryMock;
    }

    /**
     * @param array<string, string> $resourceMap
     */
    private function getKernelMockWithCustomResourceMap(array $resourceMap): KernelInterface&MockObject
    {
        $kernelMock = $this->createMock(KernelInterface::class);

        $kernelMock
            ->method('locateResource')
            ->willReturnCallback(static function (string $path) use ($resourceMap) {
                if (isset($resourceMap[$path])) {
                    return $resourceMap[$path];
                }

                throw new \InvalidArgumentException("Unknown resource: $path");
            });

        return $kernelMock;
    }
}
