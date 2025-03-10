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
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use LogicException;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/users/{userId}',
    openapi: new Model\Operation(
        summary: 'Load User',
        description: 'Loads User with the given ID.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the User is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-None-Match',
                in: 'header',
                required: true,
                description: 'ETag',
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
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - the User with the given ID.',
                'content' => [
                    'application/vnd.ibexa.api.User+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/GET/User.xml.example',
                    ],
                    'application/vnd.ibexa.api.User+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/GET/User.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Users.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User does not exist.',
            ],
        ],
    ),
)]
final class UserLoadByIdController extends UserBaseController
{
    public function loadUser(int $userId): RestValue
    {
        $user = $this->userService->loadUser($userId, Language::ALL);

        $userContentInfo = $user->getVersionInfo()->getContentInfo();
        $contentType = $this->contentTypeService->loadContentType($userContentInfo->contentTypeId);

        if ($userContentInfo->mainLocationId === null) {
            throw new LogicException();
        }

        try {
            $userMainLocation = $this->locationService->loadLocation($userContentInfo->mainLocationId);
            $relations = iterator_to_array($this->relationListFacade->getRelations($user->getVersionInfo()));
        } catch (UnauthorizedException $e) {
            // TODO: Hack for special case to allow current logged in user to load him/here self (but not relations)
            if ($user->id == $this->permissionResolver->getCurrentUserReference()->getUserId()) {
                $userMainLocation = $this->repository->sudo(
                    function () use ($userContentInfo) {
                        return $this->locationService->loadLocation($userContentInfo->mainLocationId);
                    }
                );
                // user may not have permissions to read related content, for security reasons do not use sudo().
                $relations = [];
            } else {
                throw $e;
            }
        }

        return new Values\CachedValue(
            new Values\RestUser(
                $user,
                $contentType,
                $userContentInfo,
                $userMainLocation,
                $relations
            ),
            ['locationId' => $userContentInfo->mainLocationId]
        );
    }
}
