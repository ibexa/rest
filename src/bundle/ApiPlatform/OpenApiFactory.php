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
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\Model\Server;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;
use Ibexa\Bundle\Rest\ApiPlatform\EditionBadge\EditionBadgeFactoryInterface;
use Ibexa\Contracts\Core\Ibexa;
use Symfony\Component\HttpKernel\KernelInterface;

final readonly class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
        private SchemasCollectionFactory $schemaCollectionFactory,
        private KernelInterface $kernel,
        private EditionBadgeFactoryInterface $editionBadgeFactory,
        private string $prefix,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws \JsonException
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $openApi = $openApi->withInfo((new Info('Ibexa DXP REST API', Ibexa::VERSION)));
        $openApi = $this->addSchemas($openApi);

        $this->processPaths($openApi);

        return $openApi->withServers([new Server($this->prefix, 'Current server')]);
    }

    private function addSchemas(OpenApi $openApi): OpenApi
    {
        $schemasCollection = $this->schemaCollectionFactory->create();
        $schemas = iterator_to_array($schemasCollection);

        $components = $openApi->getComponents();
        $components = $components->withSchemas(new ArrayObject($schemas));

        return $openApi->withComponents($components);
    }

    /**
     * @throws \JsonException
     */
    private function processPaths(OpenApi $openApi): void
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

                $newOperation = $this->processOperations($operation);
                if ($newOperation !== $operation) {
                    $newPathItem = $newPathItem->$setter($newOperation);
                }
            }

            if ($newPathItem !== $pathItem) {
                $paths->addPath($path, $newPathItem);
            }
        }
    }

    /**
     * @throws \JsonException
     */
    private function processOperations(Operation $operation): Operation
    {
        $newOperation = $operation;
        $newOperation = $this->insertIbexaRequestExample($newOperation);
        $newOperation = $this->insertIbexaResponseExample($newOperation);

        return $this->insertIbexaEditionBadges($newOperation);
    }

    private function insertIbexaEditionBadges(Operation $operation): Operation
    {
        if (isset($operation->getExtensionProperties()['x-badges'])) {
            return $operation;
        }

        $badges = $this->editionBadgeFactory->getBadgesForOperation($operation);
        if (!empty($badges)) {
            $operation = $operation->withExtensionProperty('x-badges', $badges);
        }

        return $operation;
    }

    private function insertIbexaRequestExample(Operation $operation): Operation
    {
        $requestBody = $operation->getRequestBody();

        if ($requestBody === null) {
            return $operation;
        }

        $content = $requestBody->getContent();

        if ($content === null) {
            return $operation;
        }

        /** @var ArrayObject<string, mixed> $newContent */
        $newContent = new ArrayObject();
        $hasChanges = false;

        foreach ($content as $mediaType => $requestContent) {
            if (!array_key_exists('x-ibexa-example-file', $requestContent)) {
                $newContent[$mediaType] = $requestContent;
                continue;
            }

            $exampleFilePath = $this->kernel->locateResource($requestContent['x-ibexa-example-file']);
            $exampleFileContent = file_get_contents($exampleFilePath);

            if ($exampleFileContent === false) {
                throw new \RuntimeException("Failed to read example file: {$exampleFilePath}");
            }

            $isJson = $this->isJson($exampleFilePath);

            $newRequestContent = $requestContent;
            $newRequestContent['example'] = $isJson ? json_decode($exampleFileContent, true, 512, JSON_THROW_ON_ERROR) : $exampleFileContent;
            unset($newRequestContent['x-ibexa-example-file']);

            $newContent[$mediaType] = $newRequestContent;
            $hasChanges = true;
        }

        if ($hasChanges) {
            $newRequestBody = new RequestBody(
                $requestBody->getDescription(),
                $newContent,
                $requestBody->getRequired()
            );

            return $operation->withRequestBody($newRequestBody);
        }

        return $operation;
    }

    /**
     * @throws \JsonException
     */
    private function insertIbexaResponseExample(Operation $operation): Operation
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
                $isJson = $this->isJson($exampleFilePath);
                $newContent[$mediaType]['example'] = $isJson ? json_decode($exampleFileContent ?: '', true, 512, JSON_THROW_ON_ERROR) : $exampleFileContent;
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

    private function isJson(string $filePath): bool
    {
        return 'json' === array_slice(explode('.', pathinfo($filePath, PATHINFO_FILENAME)), -1, 1)[0];
    }
}
