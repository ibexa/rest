<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Location;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LocationSubtreeCopyController extends LocationBaseController
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function copy(string $locationPath, Request $request): Values\ResourceCreated
    {
        $locationId = $this->extractLocationIdFromPath($locationPath);
        $location = $this->locationService->loadLocation($locationId);

        $destinationLocation = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent(),
            ),
        );

        $newLocation = $this->locationService->copySubtree($location, $destinationLocation);

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($newLocation->pathString, '/'),
                ],
            )
        );
    }

    /**
     * Copies a subtree to a new destination.
     *
     * @param string $locationPath
     *
     * @return \Ibexa\Rest\Server\Values\ResourceCreated
     */
    public function copySubtree($locationPath, Request $request)
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );

        $destinationLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath(
                $this->requestParser->parseHref(
                    $request->headers->get('Destination'),
                    'locationPath'
                )
            )
        );

        $newLocation = $this->locationService->copySubtree($location, $destinationLocation);

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($newLocation->pathString, '/'),
                ]
            )
        );
    }
}
