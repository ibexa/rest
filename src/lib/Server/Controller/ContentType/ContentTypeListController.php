<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType as APIContentType;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/types',
    name: 'List content types',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
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
            $limit = $request->query->getInt('limit');
            if ($limit <= 0) {
                throw new BadRequestException('wrong value for limit parameter');
            }
        }
        $contentTypes = $this->getContentTypeList();
        $sort = $request->query->getString('sort');
        if ($request->query->has('orderby')) {
            $orderby = $request->query->getString('orderby');
            $this->sortContentTypeList($contentTypes, $orderby, $sort);
        }
        $offset = $request->query->getInt('offset');
        $return->contentTypes = array_slice((array)$contentTypes, $offset, $limit);

        return $return;
    }

    /**
     * Loads a content type by its identifier.
     */
    public function loadContentTypeByIdentifier(Request $request): APIContentType
    {
        return $this->contentTypeService->loadContentTypeByIdentifier(
            $request->query->getString('identifier'),
            Language::ALL
        );
    }

    /**
     * Loads a content type by its remote ID.
     */
    public function loadContentTypeByRemoteId(Request $request): APIContentType
    {
        return $this->contentTypeService->loadContentTypeByRemoteId(
            $request->query->getString('remoteId'),
            Language::ALL,
        );
    }

    /**
     * @param iterable<ContentType> &$contentTypes
     *
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     */
    protected function sortContentTypeList(iterable &$contentTypes, string $orderby, string $sort = 'asc'): void
    {
        $contentTypes = (array)$contentTypes;

        switch ($orderby) {
            case 'name':
                if ($sort === 'asc' || $sort === '') {
                    usort(
                        $contentTypes,
                        static function (APIContentType $contentType1, APIContentType $contentType2): int {
                            return strcasecmp($contentType1->identifier, $contentType2->identifier);
                        }
                    );
                } elseif ($sort === 'desc') {
                    usort(
                        $contentTypes,
                        static function (APIContentType $contentType1, APIContentType $contentType2): int {
                            return strcasecmp($contentType1->identifier, $contentType2->identifier) * -1;
                        }
                    );
                } else {
                    throw new BadRequestException('wrong value for sort parameter');
                }
                break;
            case 'lastmodified':
                if ($sort === 'asc' || $sort === '') {
                    usort(
                        $contentTypes,
                        static function (APIContentType $timeObj3, APIContentType $timeObj4): int {
                            $timeObj3 = strtotime($timeObj3->modificationDate->format('Y-m-d H:i:s'));
                            $timeObj4 = strtotime($timeObj4->modificationDate->format('Y-m-d H:i:s'));

                            return $timeObj3 > $timeObj4 ? -1 : 1;
                        }
                    );
                } elseif ($sort === 'desc') {
                    usort(
                        $contentTypes,
                        static function (APIContentType $timeObj3, APIContentType $timeObj4): int {
                            $timeObj3 = strtotime($timeObj3->modificationDate->format('Y-m-d H:i:s'));
                            $timeObj4 = strtotime($timeObj4->modificationDate->format('Y-m-d H:i:s'));

                            return $timeObj3 < $timeObj4 ? -1 : 1;
                        }
                    );
                } else {
                    throw new BadRequestException('wrong value for sort parameter');
                }
                break;
            default:
                throw new BadRequestException('wrong value for orderby parameter');
        }
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[]
     */
    protected function getContentTypeList()
    {
        $contentTypes = [];
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            $contentTypesIterable = $this->contentTypeService->loadContentTypes($contentTypeGroup, Language::ALL);
            $contentTypesArray = [];
            foreach ($contentTypesIterable as $contentType) {
                $contentTypesArray[] = $contentType;
            }
            $contentTypes = array_merge(
                $contentTypes,
                $contentTypesArray,
            );
        }

        return $contentTypes;
    }
}
