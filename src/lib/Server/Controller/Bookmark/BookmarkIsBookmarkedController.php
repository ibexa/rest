<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\Bookmark;

use ApiPlatform\OpenApi\Model;
use Ibexa\Bundle\Rest\ApiPlatform\Head;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Head(
    uriTemplate: '/bookmark/{locationId}',
    openapi: new Model\Operation(
        summary: 'Check if Location is bookmarked',
        description: 'Checks if the given Location is bookmarked by the current user.',
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
class BookmarkIsBookmarkedController extends RestController
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
     * Checks if a given location is bookmarked.
     *
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function isBookmarked(int $locationId): Values\OK
    {
        $location = $this->locationService->loadLocation($locationId);

        if (!$this->bookmarkService->isBookmarked($location)) {
            throw new Exceptions\NotFoundException("Location {$locationId} is not bookmarked");
        }

        return new Values\OK();
    }
}
