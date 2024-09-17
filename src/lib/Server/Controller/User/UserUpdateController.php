<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions as ApiExceptions;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\User\User as RepositoryUser;
use Ibexa\Contracts\Core\Repository\Values\User\UserGroupRoleAssignment;
use Ibexa\Contracts\Core\Repository\Values\User\UserRoleAssignment;
use Ibexa\Contracts\Rest\Exceptions\NotFoundException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

#[Patch(
    uriTemplate: '/user/users/{userId}',
    name: 'Update User',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates a User.',
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
        $mainLocation = $this->locationService->loadLocation($updatedContentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($updatedContentInfo->contentTypeId);

        return new Values\RestUser(
            $updatedUser,
            $contentType,
            $updatedContentInfo,
            $mainLocation,
            $this->contentService->loadRelations($updatedUser->getVersionInfo())
        );
    }
}
