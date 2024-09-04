<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller;

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
#[Post(
    uriTemplate: '/user/sessions/{sessionId}/refresh',
    name: 'Refresh session (deprecated)',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapiContext: ['requestBody' => false],
    openapi: new Model\Operation(
        summary: 'Get the session\'s User information. Deprecated as of Ibexa DXP 4.6, use GET /user/sessions/current instead.',
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
    ),
)]
/**
 * @internal
 */
final class SessionController extends Controller
{
    public function __construct(
        private readonly PermissionResolver $permissionResolver,
        private readonly UserService $userService,
        private readonly CsrfTokenManager $csrfTokenManager,
        private readonly SecurityTokenStorageInterface $securityTokenStorage,
        private readonly string $csrfTokenIntention,
        private readonly ConfigResolverInterface $configResolver,
    ) {
    }

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

    public function checkSessionAction(Request $request): Values\UserSession|Response
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
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

    /**
     * Refresh given session.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
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

    private function hasStoredCsrfToken(): bool
    {
        return $this->csrfTokenManager->hasToken($this->csrfTokenIntention);
    }

    /**
     * Checks the presence / validity of the CSRF token.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException if the token is missing or invalid
     */
    private function checkCsrfToken(Request $request): void
    {
        if (!$request->headers->has('X-CSRF-Token')) {
            throw $this->createInvalidCsrfTokenException($request);
        }

        $csrfToken = new CsrfToken(
            $this->csrfTokenIntention,
            $request->headers->get('X-CSRF-Token')
        );

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw $this->createInvalidCsrfTokenException($request);
        }
    }

    private function getCsrfToken(): string
    {
        return $this->csrfTokenManager->getToken($this->csrfTokenIntention)->getValue();
    }

    private function createInvalidCsrfTokenException(Request $request): UnauthorizedException
    {
        return new UnauthorizedException('Missing or invalid CSRF token');
    }

    private function logout(Request $request): Response
    {
        $path = '/';
        $domain = null;

        $session = $this->configResolver->getParameter('session');
        if (array_key_exists('cookie_domain', $session)) {
            $domain = $session['cookie_domain'];
        }

        if (array_key_exists('cookie_path', $session)) {
            $path = $session['cookie_path'];
        }

        $response = new Response();
        $requestSession = $request->getSession();

        $response->headers->clearCookie(
            $requestSession->getName(),
            $path,
            $domain
        );

        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $requestSession->clear();

        return $response;
    }
}
