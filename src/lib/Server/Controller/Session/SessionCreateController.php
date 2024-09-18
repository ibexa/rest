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
use Ibexa\Contracts\Rest\Exceptions\UnauthorizedException;
use Ibexa\Rest\Server\Exceptions;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[Post(
    uriTemplate: '/user/sessions',
    name: 'Create session (login a User)',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Performs a login for the user or checks if session exists and returns the session and session cookie. The client will need to remember both session name/ID and CSRF token as this is for security reasons not exposed via GET.',
        tags: [
            'User Session',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the session is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The SessionInput schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
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
                name: 'X-CSRF-Token',
                in: 'header',
                required: true,
                description: 'Only needed for session\'s checking. The {csrfToken} needed on all unsafe HTTP methods with session.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.SessionInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/SessionInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/sessions/POST/SessionInput.xml.example',
                ],
                'application/vnd.ibexa.api.SessionInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/SessionInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/sessions/POST/SessionInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'Session already exists.',
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
            Response::HTTP_CREATED => [
                'description' => 'Session is created.',
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
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the authorization failed.',
            ],
            Response::HTTP_CONFLICT => [
                'description' => 'Error - header contained a session cookie but different user was authorized.',
            ],
        ],
    ),
)]
/**
 * @internal
 */
final class SessionCreateController extends SessionBaseController
{
    /**
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function createSessionAction(Request $request): RestValue
    {
        try {
            $session = $request->getSession();
            $csrfToken = $this->getCsrfToken();
            $token = $this->securityTokenStorage->getToken();

            if ($token === null) {
                throw new UnauthorizedException('The current user is not authenticated.');
            }

            /** @var \Ibexa\Core\MVC\Symfony\Security\User $user */
            $user = $token->getUser();

            return new Values\UserSession(
                $user->getAPIUser(),
                $session->getName(),
                $session->getId(),
                $csrfToken,
                !$token->hasAttribute('isFromSession')
            );
        } catch (Exceptions\UserConflictException $e) {
            // Already logged in with another user, this will be converted to HTTP status 409
            return new Values\Conflict();
        } catch (AuthenticationException $e) {
            throw new UnauthorizedException('Invalid login or password');
        } catch (AccessDeniedException $e) {
            throw new UnauthorizedException($e->getMessage());
        }
    }
}
