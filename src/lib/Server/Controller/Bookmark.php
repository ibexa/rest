<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller;

use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\OK;
use Ibexa\Rest\Value;
use Ibexa\Rest\Value as RestValue;
use Symfony\Component\HttpFoundation\Request;

class Bookmark extends RestController
{
    /**
     * @var BookmarkService
     */
    protected $bookmarkService;

    /**
     * @var LocationService
     */
    protected $locationService;

    /**
     * Bookmark constructor.
     *
     * @param BookmarkService $bookmarkService
     * @param LocationService $locationService
     */
    public function __construct(
        BookmarkService $bookmarkService,
        LocationService $locationService
    ) {
        $this->bookmarkService = $bookmarkService;
        $this->locationService = $locationService;
    }

    /**
     * Add given location to bookmarks.
     *
     * @param Request $request
     * @param int $locationId
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     *
     * @return Value
     */
    public function createBookmark(
        Request $request,
        int $locationId
    ): RestValue {
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
     * @param Request $request
     * @param int $locationId
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     *
     * @return Value
     */
    public function deleteBookmark(
        Request $request,
        int $locationId
    ): RestValue {
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
     * @param Request $request
     * @param int $locationId
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     *
     * @return OK
     */
    public function isBookmarked(
        Request $request,
        int $locationId
    ): OK {
        $location = $this->locationService->loadLocation($locationId);

        if (!$this->bookmarkService->isBookmarked($location)) {
            throw new Exceptions\NotFoundException("Location {$locationId} is not bookmarked");
        }

        return new OK();
    }

    /**
     * List bookmarked locations.
     *
     * @param Request $request
     *
     * @throws InvalidArgumentException
     *
     * @return Value
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

class_alias(Bookmark::class, 'EzSystems\EzPlatformRest\Server\Controller\Bookmark');
