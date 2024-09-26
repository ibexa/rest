<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\Session;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
