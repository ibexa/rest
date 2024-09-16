<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Exceptions\ContentFieldValidationException as RESTContentFieldValidationException;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\RestContentCreateStruct;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[Get(
    uriTemplate: '/content/objects',
    name: 'Load content by remote ID',
    openapi: new Model\Operation(
        summary: 'Loads content item for a given remote ID.',
        tags: [
            'Objects',
        ],
        parameters: [
        ],
        responses: [
            Response::HTTP_TEMPORARY_REDIRECT => [
                'description' => 'Temporary redirect.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content with the given remote ID does not exist.',
            ],
        ],
    ),
)]
class ContentRedirectController extends RestController
{
    /**
     * Loads a content info by remote ID.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     *
     * @return \Ibexa\Rest\Server\Values\TemporaryRedirect
     */
    public function redirectContent(Request $request)
    {
        if (!$request->query->has('remoteId')) {
            throw new BadRequestException("'remoteId' parameter is required.");
        }

        $contentInfo = $this->repository->getContentService()->loadContentInfoByRemoteId(
            $request->query->get('remoteId')
        );

        return new Values\TemporaryRedirect(
            $this->router->generate(
                'ibexa.rest.load_content',
                [
                    'contentId' => $contentInfo->id,
                ]
            )
        );
    }
}
