<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\Bookmark;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/bookmark/{locationId}',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Create bookmark',
        description: 'Add given Location to bookmarks of the current user.',
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
        requestBody: new Model\RequestBody(
            content: new \ArrayObject(),
        ),
    ),
)]
class BookmarkCreateController extends RestController
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
     * Add given location to bookmarks.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function createBookmark(int $locationId): RestValue|Values\Conflict
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
}
