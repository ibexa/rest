<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\Bookmark;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/bookmark',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'List of bookmarks',
        description: 'Lists bookmarked Locations for the current user.',
        tags: [
            'Bookmark',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.BookmarkList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/BookmarkList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/bookmark/GET/BookmarkList.xml.example',
                    ],
                    'application/vnd.ibexa.api.BookmarkList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/BookmarkListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/bookmark/GET/BookmarkList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to list bookmarks.',
            ],
        ],
    ),
)]
class BookmarkListController extends RestController
{
    protected BookmarkService $bookmarkService;

    protected LocationService $locationService;

    /**
     * Bookmark constructor.
     */
    public function __construct(BookmarkService $bookmarkService, LocationService $locationService)
    {
        $this->bookmarkService = $bookmarkService;
        $this->locationService = $locationService;
    }

    /**
     * List bookmarked locations.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function loadBookmarks(Request $request): RestValue
    {
        $offset = $request->query->getInt('offset', 0);
        $limit = $request->query->getInt('limit', 25);

        $restLocations = [];
        $bookmarks = $this->bookmarkService->loadBookmarks($offset, $limit);
        foreach ($bookmarks as $bookmark) {
            $restLocations[] = new Values\RestLocation(
                $bookmark,
                $this->locationService->getLocationChildCount($bookmark)
            );
        }

        return new Values\BookmarkList($bookmarks->totalCount, $restLocations);
    }
}
