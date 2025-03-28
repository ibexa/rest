<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\Session;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/user/sessions/{sessionId}/refresh',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Refresh session (deprecated)',
        description: 'Get the session\'s User information. Deprecated as of Ibexa DXP 4.6, use GET /user/sessions/current instead.',
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
                name: 'Accept',
                in: 'header',
                required: true,
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
            Response::HTTP_OK => [
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
                'description' => 'Error - the session does not exist.',
            ],
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject(),
        ),
    ),
)]
/**
 * @internal
 */
final class SessionRefreshController extends SessionBaseController
{
    /**
     * Refresh given session.
     *
     * @deprecated 5.0.0 The "SessionController::refreshSessionAction()" method is deprecated, will be removed in the next API version. Use SessionController::checkSessionAction() instead.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function refreshSessionAction(string $sessionId, Request $request): Values\UserSession|Response
    {
        trigger_deprecation(
            'ibexa/rest',
            '4.6.7',
            sprintf('The %s() method is deprecated, will be removed in the next API version.', __METHOD__)
        );

        $session = $request->getSession();

        if (!$session->isStarted() || $session->getId() !== $sessionId || !$this->hasStoredCsrfToken()) {
            return $this->logout($request);
        }

        $this->checkCsrfToken($request);
        $currentUser = $this->userService->loadUser(
            $this->permissionResolver->getCurrentUserReference()->getUserId()
        );

        return new Values\UserSession(
            $currentUser,
            $session->getName(),
            $session->getId(),
            $request->headers->get('X-CSRF-Token') ?? '',
            false
        );
    }
}
