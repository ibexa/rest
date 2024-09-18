<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Trash;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use Webmozart\Assert\Assert;

class LocationTrashController extends RestController
{
    public function __construct(
        protected TrashService $trashService,
        protected LocationService $locationService
    ) {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function trashLocation(string $locationPath): RestValue
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath),
        );

        $trashItem = $this->trashService->trash($location);

        if ($trashItem === null) {
            return new Values\NoContent();
        }

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_trash_item',
                ['trashItemId' => $trashItem->getId()],
            ),
        );
    }

    private function extractLocationIdFromPath(string $path): int
    {
        $pathParts = explode('/', $path);
        $lastPart = array_pop($pathParts);

        Assert::integerish($lastPart);

        return (int)$lastPart;
    }
}
