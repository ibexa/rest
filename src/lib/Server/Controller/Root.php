<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Rest\Exceptions\NotFoundException;
use Ibexa\Rest\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/',
    openapi: new Model\Operation(
        summary: 'List of root resources',
        description: 'Lists the root resources of the Ibexa Platform installation.',
        tags: [
            'Root',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the list is return in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.Root+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Root',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/GET/Root.xml.example',
                    ],
                    'application/vnd.ibexa.api.Root+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RootWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/GET/Root.json.example',
                    ],
                ],
            ],
        ],
    ),
)]
/**
 * Root controller.
 */
class Root extends RestController
{
    /**
     * @var \Ibexa\Rest\Server\Service\RootResourceBuilderInterface
     */
    private $rootResourceBuilder;

    public function __construct($rootResourceBuilder)
    {
        $this->rootResourceBuilder = $rootResourceBuilder;
    }

    /**
     * List the root resources of the Ibexa installation.
     *
     * @return \Ibexa\Rest\Values\Root
     */
    public function loadRootResource()
    {
        return $this->rootResourceBuilder->buildRootResource();
    }

    /**
     * Catch-all for REST requests.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     */
    public function catchAll(): never
    {
        throw new NotFoundException('No such route');
    }
}
