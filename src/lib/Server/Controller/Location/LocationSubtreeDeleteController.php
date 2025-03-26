<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Location;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/locations/{path}',
    openapi: new Model\Operation(
        summary: 'Delete subtree',
        description: 'Deletes the complete subtree for the given path. Every content item which does not have any other Location is deleted. Otherwise the deleted Location is removed from the content item. The children are recursively deleted.',
        tags: [
            'Location',
        ],
        parameters: [
            new Model\Parameter(
                name: 'path',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this subtree.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Location with the given ID does not exist.',
            ],
        ],
    ),
)]
class LocationSubtreeDeleteController extends LocationBaseController
{
    /**
     * Deletes a location.
     *
     * @param string $locationPath
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteSubtree($locationPath)
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );
        $this->locationService->deleteLocation($location);

        return new Values\NoContent();
    }
}
