<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/bookmark',
    name: 'List of bookmarks',
    openapi: new Model\Operation(
        summary: 'Lists bookmarked Locations for the current user.',
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
#[Post(
    uriTemplate: '/bookmark/{locationId}',
    name: 'Create bookmark',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapiContext: ['requestBody' => false],
    openapi: new Model\Operation(
        summary: 'Add given Location to bookmarks of the current user.',
        tags: [
            'Bookmark',
        ],
        parameters: [
            new Model\Parameter(
                name: 'locationId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Created.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to given Location.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the given Location does not exist.',
            ],
            Response::HTTP_CONFLICT => [
                'description' => 'Error - Location is already bookmarked.',
            ],
        ],
    ),
)]
#[Head(
    uriTemplate: '/bookmark/{locationId}',
    name: 'Check if Location is bookmarked',
    openapi: new Model\Operation(
        summary: 'Checks if the given Location is bookmarked by the current user.',
        tags: [
            'Bookmark',
        ],
        parameters: [
            new Model\Parameter(
                name: 'locationId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - bookmarked.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized for the given Location.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the given Location does not exist / is not bookmarked.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/bookmark/{locationId}',
    name: 'Delete bookmark',
    openapi: new Model\Operation(
        summary: 'Deletes the given Location from bookmarks of the current user.',
        tags: [
            'Bookmark',
        ],
        parameters: [
            new Model\Parameter(
                name: 'locationId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'Deleted - no content.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized for the given Location.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the given Location does not exist / is not bookmarked.',
            ],
        ],
    ),
)]
class Bookmark extends RestController
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\BookmarkService
     */
    protected $bookmarkService;

    /**
     * @var \Ibexa\Contracts\Core\Repository\LocationService
     */
    protected $locationService;

    /**
     * Bookmark constructor.
     *
     * @param \Ibexa\Contracts\Core\Repository\BookmarkService $bookmarkService
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     */
    public function __construct(BookmarkService $bookmarkService, LocationService $locationService)
    {
        $this->bookmarkService = $bookmarkService;
        $this->locationService = $locationService;
    }

    /**
     * Add given location to bookmarks.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $locationId
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     *
     * @return \Ibexa\Rest\Value
     */
    public function createBookmark(Request $request, int $locationId): RestValue
    {
        $location = $this->locationService->loadLocation($locationId);

        try {
            $this->bookmarkService->createBookmark($location);

            return new Values\ResourceCreated(
                $this->router->generate(
                    'ibexa.rest.is_bookmarked',
                    [
                        'locationId' => $locationId,
                    ]
                )
            );
        } catch (InvalidArgumentException $e) {
            return new Values\Conflict();
        }
    }

    /**
     * Deletes a given bookmark.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $locationId
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     *
     * @return \Ibexa\Rest\Value
     */
    public function deleteBookmark(Request $request, int $locationId): RestValue
    {
        $location = $this->locationService->loadLocation($locationId);

        try {
            $this->bookmarkService->deleteBookmark($location);

            return new Values\NoContent();
        } catch (InvalidArgumentException $e) {
            throw new Exceptions\NotFoundException("Location {$locationId} is not bookmarked");
        }
    }

    /**
     * Checks if given location is bookmarked.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $locationId
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     *
     * @return \Ibexa\Rest\Server\Values\OK
     */
    public function isBookmarked(Request $request, int $locationId): Values\OK
    {
        $location = $this->locationService->loadLocation($locationId);

        if (!$this->bookmarkService->isBookmarked($location)) {
            throw new Exceptions\NotFoundException("Location {$locationId} is not bookmarked");
        }

        return new Values\OK();
    }

    /**
     * List bookmarked locations.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     *
     * @return \Ibexa\Rest\Value
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

    /**
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58.
     *
     * @param string $path
     *
     * @return mixed
     */
    private function extractLocationIdFromPath(string $path)
    {
        $pathParts = explode('/', $path);

        return array_pop($pathParts);
    }
}
