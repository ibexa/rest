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

#[Get(
    uriTemplate: '/user/sessions/current',
    name: 'Get current session',
    openapi: new Model\Operation(
        summary: 'Get current user session, if any.',
        tags: [
            'User Session',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Cookie',
                in: 'header',
                required: true,
                description: 'Only needed for session\'s checking {sessionName}={sessionID}.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the session is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'User is currently logged in and has a valid session.',
                'content' => [
                    'application/vnd.ibexa.api.Session+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Session',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/sessions/POST/Session.xml.example',
                    ],
                    'application/vnd.ibexa.api.Session+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/SessionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/sessions/session_id/refresh/POST/Session.json.example',
                    ],
                ],
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'User does not have a valid session, or it has expired.',
            ],
        ],
    ),
)]
/**
 * @internal
 */
final class SessionCheckController extends SessionBaseController
{
    /**
     * @return \Ibexa\Rest\Server\Values\UserSession|\Symfony\Component\HttpFoundation\Response
     */
    public function checkSessionAction(Request $request)
    {
        $session = $request->getSession();
        if ($session === null || !$session->isStarted()) {
            return $this->logout($request);
        }

        $currentUser = $this->userService->loadUser(
            $this->permissionResolver->getCurrentUserReference()->getUserId()
        );

        return new Values\UserSession(
            $currentUser,
            $session->getName(),
            $session->getId(),
            $this->getCsrfToken(),
            false
        );
    }
}
