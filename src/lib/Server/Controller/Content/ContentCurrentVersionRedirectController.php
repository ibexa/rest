<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\TemporaryRedirect;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/objects/{contentId}/currentversion',
    openapi: new Model\Operation(
        summary: 'Get current version',
        description: 'Redirects to the current version of the content item.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.Version+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Version',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.xml.example',
                    ],
                    'application/vnd.ibexa.api.Version+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.json.example',
                    ],
                ],
            ],
            Response::HTTP_TEMPORARY_REDIRECT => [
                'description' => 'Temporary redirect.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the resource does not exist.',
            ],
        ],
    ),
)]
class ContentCurrentVersionRedirectController extends RestController
{
    public function redirectCurrentVersion(int $contentId): TemporaryRedirect
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        return new TemporaryRedirect(
            $this->router->generate(
                'ibexa.rest.load_content_in_version',
                [
                    'contentId' => $contentId,
                    'versionNumber' => $contentInfo->currentVersionNo,
                ]
            )
        );
    }
}
