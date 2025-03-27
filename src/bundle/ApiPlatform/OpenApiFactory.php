<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\ApiPlatform;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;
use Ibexa\Contracts\Core\Ibexa;
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
     * @param array<string, mixed> $context
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $openApi = $openApi->withInfo((new Info('Ibexa DXP REST API', Ibexa::VERSION)));
        $openApi = $this->addSchemas($openApi);

        $this->insertExampleFilesContent($openApi);

        return $openApi;
    }

    private function addSchemas(OpenApi $openApi): OpenApi
    {
        $schemasCollection = $this->schemaCollectionFactory->create();
        $schemas = iterator_to_array($schemasCollection);

        $components = $openApi->getComponents();
        $components = $components->withSchemas(new ArrayObject($schemas));

        return $openApi->withComponents($components);
    }

    private function insertExampleFilesContent(OpenApi $openApi): void
    {
        $paths = $openApi->getPaths();

        /** @var \ApiPlatform\OpenApi\Model\PathItem $pathItem */
        foreach ($paths->getPaths() as $path => $pathItem) {
            $newPathItem = clone $pathItem;

            $methods = [
                'GET' => [$pathItem->getGet(), 'withGet'],
                'PUT' => [$pathItem->getPut(), 'withPut'],
                'POST' => [$pathItem->getPost(), 'withPost'],
                'DELETE' => [$pathItem->getDelete(), 'withDelete'],
                'OPTIONS' => [$pathItem->getOptions(), 'withOptions'],
                'HEAD' => [$pathItem->getHead(), 'withHead'],
                'PATCH' => [$pathItem->getPatch(), 'withPatch'],
                'TRACE' => [$pathItem->getTrace(), 'withTrace'],
            ];
            foreach ($methods as [$operation, $setter]) {
                if ($operation === null || $operation->getResponses() === null) {
                    continue;
                }

                $newOperation = $this->processOperationResponses($operation);
                if ($newOperation !== $operation) {
                    $newPathItem = $newPathItem->$setter($newOperation);
                }
            }

            if ($newPathItem !== $pathItem) {
                $paths->addPath($path, $newPathItem);
            }
        }
    }

    private function processOperationResponses(Operation $operation): Operation
    {
        $newOperation = $operation;

        foreach (($operation->getResponses() ?? []) as $responseCode => $response) {
            if (!is_array($response) || !array_key_exists('content', $response)) {
                continue;
            }

            $newContent = $response['content'];

            foreach ($newContent as $mediaType => $responseContent) {
                if (!array_key_exists('x-ibexa-example-file', $responseContent)) {
                    continue;
                }

                $exampleFilePath = $this->kernel->locateResource($responseContent['x-ibexa-example-file']);
                $exampleFileContent = file_get_contents($exampleFilePath);
                if ('json' === array_slice(explode('.', pathinfo($exampleFilePath, PATHINFO_FILENAME)), -1, 1)[0]) {
                    $newContent[$mediaType]['example'] = json_decode($exampleFileContent ?: '', true);
                } else {
                    $newContent[$mediaType]['example'] = $exampleFileContent;
                }
                unset($newContent[$mediaType]['x-ibexa-example-file']);
            }

            if ($newContent !== $response['content']) {
                $newOperation = $newOperation->withResponse(
                    $responseCode,
                    new Response((string)$responseCode, new ArrayObject($newContent)),
                );
            }
        }

        return $newOperation;
    }
}
