<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\Session;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Rest\Exceptions\UnauthorizedException;
use Ibexa\Rest\Server\Controller;
use Ibexa\Rest\Server\Exceptions;
use Ibexa\Rest\Server\Security\CsrfTokenManager;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface as SecurityTokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfToken;

#[Delete(
    uriTemplate: '/user/sessions/{sessionId}',
    name: 'Delete session (logout a User)',
    openapi: new Model\Operation(
        summary: 'The user session is removed i.e. the user is logged out.',
        tags: [
            'User Session',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Cookie',
                in: 'header',
                required: true,
                description: '{sessionName}={sessionID}',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'X-CSRF-Token',
                in: 'header',
                required: true,
                description: 'The {csrfToken} needed on all unsafe HTTP methods with session.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'sessionId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'OK - session deleted.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the session does not exist.',
            ],
        ],
    ),
)]
/**
 * @internal
 */
final class SessionDeleteController extends SessionBaseController
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function deleteSessionAction(string $sessionId, Request $request): Values\DeletedUserSession|Response
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        if (!$session->isStarted() || $session->getId() !== $sessionId || !$this->hasStoredCsrfToken()) {
            return $this->logout($request);
        }

        $this->checkCsrfToken($request);

        return new Values\DeletedUserSession(
            $this->logout($request)
        );
    }
}
