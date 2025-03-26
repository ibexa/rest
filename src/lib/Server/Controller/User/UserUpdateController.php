<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Values;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Patch(
    uriTemplate: '/user/users/{userId}',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Update User',
        description: 'Updates a User.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated User is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The UserUpdate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
                in: 'header',
                required: true,
                description: 'Performs a PATCH only if the specified ETag is the current one.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.UserUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/PATCH/UserUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.UserUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserUpdateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/PATCH/UserUpdate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - User updated.',
                'content' => [
                    'application/vnd.ibexa.api.User+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/User',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/PATCH/User.xml.example',
                    ],
                    'application/vnd.ibexa.api.User+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/PATCH/User.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update the User.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User does not exist.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - the current ETag does not match with the provided one in the If-Match header.',
            ],
        ],
    ),
)]
final class UserUpdateController extends UserBaseController
{
    public function updateUser(int $userId, Request $request): Values\RestUser
    {
        $user = $this->userService->loadUser($userId);

        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $request->getPathInfo(),
                ],
                $request->getContent()
            )
        );

        if ($updateStruct->sectionId !== null) {
            $section = $this->sectionService->loadSection($updateStruct->sectionId);
            $this->sectionService->assignSection(
                $user->getVersionInfo()->getContentInfo(),
                $section
            );
        }

        $updatedUser = $this->userService->updateUser($user, $updateStruct->userUpdateStruct);
        $updatedContentInfo = $updatedUser->getVersionInfo()->getContentInfo();

        if ($updatedContentInfo->mainLocationId === null) {
            throw new LogicException();
        }

        $mainLocation = $this->locationService->loadLocation($updatedContentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($updatedContentInfo->contentTypeId);

        return new Values\RestUser(
            $updatedUser,
            $contentType,
            $updatedContentInfo,
            $mainLocation,
            iterator_to_array($this->relationListFacade->getRelations($updatedUser->getVersionInfo())),
        );
    }
}
