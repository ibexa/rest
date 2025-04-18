<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/groups/{path}/users',
    openapi: new Model\Operation(
        summary: 'Load Users of Group',
        description: 'Loads the Users of the Group with the given ID.',
        tags: [
            'User Group',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'UserList - If set, the User list returned in XML or JSON format. UserRefList - If set, the link list of Users returned in XML or JSON format.',
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
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - the Users of the Group with the given ID.',
                'content' => [
                    'application/vnd.ibexa.api.UserList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserList',
                        ],
                    ],
                    'application/vnd.ibexa.api.UserList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserList',
                        ],
                    ],
                    'application/vnd.ibexa.api.UserRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/GET/UserRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/id/users/GET/UserRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read User Groups.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User Group does not exist.',
            ],
        ],
    ),
)]
final class UserGroupUsersListController extends UserBaseController
{
    /**
     * Loads the users of the group with the given path.
     */
    public function loadUsersFromGroup(string $groupPath, Request $request): RestValue
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $users = $this->userService->loadUsersOfUserGroup(
            $userGroup,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25,
            Language::ALL
        );

        $restUsers = [];
        foreach ($users as $user) {
            $userContentInfo = $user->getVersionInfo()->getContentInfo();

            if ($userContentInfo->mainLocationId === null) {
                throw new LogicException();
            }

            $userLocation = $this->locationService->loadLocation($userContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userContentInfo->contentTypeId);

            $restUsers[] = new Values\RestUser(
                $user,
                $contentType,
                $userContentInfo,
                $userLocation,
                iterator_to_array($this->relationListFacade->getRelations($user->getVersionInfo())),
            );
        }

        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.userlist') {
            return new Values\CachedValue(
                new Values\UserList($restUsers, $request->getPathInfo()),
                ['locationId' => $userGroupLocation->id]
            );
        }

        return new Values\CachedValue(
            new Values\UserRefList($restUsers, $request->getPathInfo()),
            ['locationId' => $userGroupLocation->id]
        );
    }
}
