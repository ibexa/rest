<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/content/types/view',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Filter content types',
        description: 'Executes a query and returns a View including the results. The View input reflects the criteria model of the public PHP API.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'The view in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The view input in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentTypeViewInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeViewInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/POST/ContentTypeCreateView.xml.example',
                ],
                'application/vnd.ibexa.api.ContentTypeViewInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeViewInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/POST/ContentTypeCreateView.xml.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfoList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/GET/ContentTypeInfoList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfoListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/GET/ContentTypeInfoList.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
        ],
    ),
)]
final class ContentTypeCreateViewController extends RestController
{
    public function __construct(
        protected readonly ContentTypeService $contentTypeService
    ) {
    }

    public function createView(Request $request): Values\ContentTypeList
    {
        /** @var \Ibexa\Rest\Server\Values\ContentTypeRestViewInput $viewInput */
        $viewInput = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $contentTypes = $this->contentTypeService->findContentTypes($viewInput->query);

        return new Values\ContentTypeList(
            $contentTypes->getContentTypes(),
            '',
        );
    }
}
