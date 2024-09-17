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
    uriTemplate: '/user/groups/{path}',
    name: 'Update User Group',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates a User Group. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'User Group',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new User Group is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The UserGroupUpdate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
                in: 'header',
                required: true,
                description: 'Performs the PATCH only if the specified ETag is the current one. Otherwise a 412 is returned.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'path',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.UserGroupUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserGroupUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/PATCH/UserGroupUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.UserGroupUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserGroupUpdateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/PATCH/UserGroupUpdate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - updated User Group.',
                'content' => [
                    'application/vnd.ibexa.api.UserGroup+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroup',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/subgroups/POST/UserGroup.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserGroup+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/subgroups/POST/UserGroup.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update the User Group.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error -	if the current ETag does not match with the one provided in the If-Match header.',
            ],
        ],
    ),
)]
final class UserGroupUpdateController extends UserBaseController
{
    public function updateUserGroup(string $groupPath, Request $request): Values\RestUserGroup
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

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
                $userGroup->getVersionInfo()->getContentInfo(),
                $section
            );
        }

        $updatedGroup = $this->userService->updateUserGroup($userGroup, $updateStruct->userGroupUpdateStruct);
        $contentType = $this->contentTypeService->loadContentType(
            $updatedGroup->getVersionInfo()->getContentInfo()->contentTypeId
        );

        return new Values\RestUserGroup(
            $updatedGroup,
            $contentType,
            $updatedGroup->getVersionInfo()->getContentInfo(),
            $userGroupLocation,
            $this->contentService->loadRelations($updatedGroup->getVersionInfo())
        );
    }
}
