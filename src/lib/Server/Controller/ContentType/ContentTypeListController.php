<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType as APIContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/types',
    name: 'List content types',
    openapi: new Model\Operation(
        summary: 'Returns a list of content types.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the list of content type info objects or content types (including Field definitions) is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns a list of content types.',
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeInfoList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfoList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/GET/ContentTypeInfoList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeInfoList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfoListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/GET/ContentTypeInfoList.json.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeList',
                        ],
                    ],
                    'application/vnd.ibexa.api.ContentTypeList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeListWrapper',
                        ],
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read the content types.',
            ],
        ],
    ),
)]
class ContentTypeListController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Returns a list of content types.
     *
     * @return \Ibexa\Rest\Server\Values\ContentTypeList|\Ibexa\Rest\Server\Values\ContentTypeInfoList
     */
    public function listContentTypes(Request $request)
    {
        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.contenttypelist') {
            $return = new Values\ContentTypeList([], $request->getPathInfo());
        } else {
            $return = new Values\ContentTypeInfoList([], $request->getPathInfo());
        }

        if ($request->query->has('identifier')) {
            $return->contentTypes = [$this->loadContentTypeByIdentifier($request)];

            return $return;
        }

        if ($request->query->has('remoteId')) {
            $return->contentTypes = [
                $this->loadContentTypeByRemoteId($request),
            ];

            return $return;
        }

        $limit = null;
        if ($request->query->has('limit')) {
            $limit = (int)$request->query->get('limit', null);
            if ($limit <= 0) {
                throw new BadRequestException('wrong value for limit parameter');
            }
        }
        $contentTypes = $this->getContentTypeList();
        $sort = $request->query->get('sort');
        if ($request->query->has('orderby')) {
            $orderby = $request->query->get('orderby');
            $this->sortContentTypeList($contentTypes, $orderby, $sort);
        }
        $offset = $request->query->get('offset', 0);
        $return->contentTypes = array_slice($contentTypes, $offset, $limit);

        return $return;
    }

    /**
     * Loads a content type by its identifier.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByIdentifier(Request $request)
    {
        return $this->contentTypeService->loadContentTypeByIdentifier(
            $request->query->get('identifier'),
            Language::ALL
        );
    }

    /**
     * Loads a content type by its remote ID.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByRemoteId(Request $request)
    {
        return $this->contentTypeService->loadContentTypeByRemoteId(
            $request->query->get('remoteId'),
            Language::ALL
        );
    }

    /**
     * @param array &$contentTypes
     * @param string $orderby
     *
     * @return mixed
     *
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     */
    protected function sortContentTypeList(array &$contentTypes, $orderby, $sort = 'asc')
    {
        switch ($orderby) {
            case 'name':
                if ($sort === 'asc' || $sort === null) {
                    usort(
                        $contentTypes,
                        static function (APIContentType $contentType1, APIContentType $contentType2) {
                            return strcasecmp($contentType1->identifier, $contentType2->identifier);
                        }
                    );
                } elseif ($sort === 'desc') {
                    usort(
                        $contentTypes,
                        static function (APIContentType $contentType1, APIContentType $contentType2) {
                            return strcasecmp($contentType1->identifier, $contentType2->identifier) * -1;
                        }
                    );
                } else {
                    throw new BadRequestException('wrong value for sort parameter');
                }
                break;
            case 'lastmodified':
                if ($sort === 'asc' || $sort === null) {
                    usort(
                        $contentTypes,
                        static function ($timeObj3, $timeObj4) {
                            $timeObj3 = strtotime($timeObj3->modificationDate->format('Y-m-d H:i:s'));
                            $timeObj4 = strtotime($timeObj4->modificationDate->format('Y-m-d H:i:s'));

                            return $timeObj3 > $timeObj4;
                        }
                    );
                } elseif ($sort === 'desc') {
                    usort(
                        $contentTypes,
                        static function ($timeObj3, $timeObj4) {
                            $timeObj3 = strtotime($timeObj3->modificationDate->format('Y-m-d H:i:s'));
                            $timeObj4 = strtotime($timeObj4->modificationDate->format('Y-m-d H:i:s'));

                            return $timeObj3 < $timeObj4;
                        }
                    );
                } else {
                    throw new BadRequestException('wrong value for sort parameter');
                }
                break;
            default:
                throw new BadRequestException('wrong value for orderby parameter');
                break;
        }
    }

    /**
     * @return ContentType[]
     */
    protected function getContentTypeList()
    {
        $contentTypes = [];
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            $contentTypes = array_merge(
                $contentTypes,
                $this->contentTypeService->loadContentTypes($contentTypeGroup, Language::ALL)
            );
        }

        return $contentTypes;
    }
}
