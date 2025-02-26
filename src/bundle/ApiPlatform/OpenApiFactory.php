<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\ApiPlatform;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\HttpKernel\KernelInterface;

final readonly class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
        private SchemasCollectionFactory $schemaCollectionFactory,
        private KernelInterface $kernel,
    ) {
    }

    /**
     * @param array<mixed> $context
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);
        $openApi = $this->addSchemas($openApi);

        $this->insertExampleFilesContent($openApi);

        return $openApi;
    }

    private function addSchemas(OpenApi $openApi): OpenApi
    {
        $schemasCollection = $this->schemaCollectionFactory->create();
        $schemas = iterator_to_array($schemasCollection);

        $components = $openApi->getComponents();
        $components = $components->withSchemas(new \ArrayObject($schemas));

        $openApi = $openApi->withComponents($components);

        return $openApi;
    }

    private function insertExampleFilesContent(OpenApi $openApi): void
    {
        $paths = $openApi->getPaths();

        /** @var \ApiPlatform\OpenApi\Model\PathItem $pathItem */
        foreach ($paths->getPaths() as $path => $pathItem) {
            $newPathItem = $pathItem;

            /** @var array<string, \ApiPlatform\OpenApi\Model\Operation|null> $methods */
            $methods = [
                'GET' => $pathItem->getGet(),
                'PUT' => $pathItem->getPut(),
                'POST' => $pathItem->getPost(),
                'DELETE' => $pathItem->getDelete(),
                'OPTIONS' => $pathItem->getOptions(),
                'HEAD' => $pathItem->getHead(),
                'PATCH' => $pathItem->getPatch(),
                'TRACE' => $pathItem->getTrace(),
            ];
            foreach ($methods as $method => $operation) {
                if (empty($operation)) {
                    continue;
                }

                $responses = $operation->getResponses();

                if ($responses === null) {
                    continue;
                }

                $newOperation = $operation;

                foreach ($responses as $responseCode => $response) {
                    if (!is_array($response) || !array_key_exists('content', $response)) {
                        continue;
                    }

                    $content = $response['content'];
                    $newContent = $content;

                    foreach ($newContent as $mediaType => $responseContent) {
                        if (array_key_exists('x-ibexa-example-file', $responseContent)) {
                            $exampleFilePath = $this->kernel->locateResource($responseContent['x-ibexa-example-file']);
                            $exampleFileContent = file_get_contents($exampleFilePath);
                            $newContent[$mediaType]['example'] = $exampleFileContent;
                            unset($newContent[$mediaType]['x-ibexa-example-file']);
                        }
                    }

                    if ($newContent !== $content) {
                        $newOperation = $newOperation->withResponse(
                            $responseCode,
                            new Response((string)$responseCode, new \ArrayObject($newContent)),
                        );
                    }
                }

                if ($newOperation !== $operation) {
                    switch ($method) {
                        case 'GET':
                            $newPathItem = $newPathItem->withGet($newOperation);
                            break;
                        case 'PUT':
                            $newPathItem = $newPathItem->withPut($newOperation);
                            break;
                        case 'POST':
                            $newPathItem = $newPathItem->withPost($newOperation);
                            break;
                        case 'DELETE':
                            $newPathItem = $newPathItem->withDelete($newOperation);
                            break;
                        case 'OPTIONS':
                            $newPathItem = $newPathItem->withOptions($newOperation);
                            break;
                        case 'HEAD':
                            $newPathItem = $newPathItem->withHead($newOperation);
                            break;
                        case 'PATCH':
                            $newPathItem = $newPathItem->withPatch($newOperation);
                            break;
                        case 'TRACE':
                            $newPathItem = $newPathItem->withTrace($newOperation);
                            break;
                    }
                }
            }

            if ($newPathItem !== $pathItem) {
                $paths->addPath($path, $newPathItem);
            }
        }
    }
}
