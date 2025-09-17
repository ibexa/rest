<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Controller;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Core\MVC\Symfony\Security\Authentication\AuthenticatorInterface;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller;
use Ibexa\Rest\Server\Exceptions;
use Ibexa\Rest\Server\Security\CsrfTokenManager;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class SessionController extends Controller
{
    private ?AuthenticatorInterface $authenticator;

    private ?CsrfTokenManager $csrfTokenManager;

    private string $csrfTokenIntention;

    private PermissionResolver $permissionResolver;

    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    private UserService $userService;

    public function __construct(
        $tokenIntention,
        PermissionResolver $permissionResolver,
        UserService $userService,
        ?AuthenticatorInterface $authenticator = null,
        ?CsrfTokenManager $csrfTokenManager = null,
    ) {
        $this->authenticator = $authenticator;
        $this->csrfTokenIntention = $tokenIntention;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
    }

    /**
     * Creates a new session based on the credentials provided as POST parameters.
     *
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException If the login or password are incorrect or invalid CSRF
     *
     * @return Values\UserSession|Values\Conflict
     */
    public function createSessionAction(Request $request)
    {
        /** @var $sessionInput \Ibexa\Rest\Server\Values\SessionInput */
        $sessionInput = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );
        $request->attributes->set('username', $sessionInput->login);
        $request->attributes->set('password', (string) $sessionInput->password);

        try {
            $session = $request->getSession();
            $token = $this->getAuthenticator()->authenticate($request);
            $csrfToken = $this->getCsrfToken();

            return new Values\UserSession(
                $token->getUser()->getAPIUser(),
                $session->getName(),
                $session->getId(),
                $csrfToken,
                !$token->hasAttribute('isFromSession')
            );
        } catch (Exceptions\UserConflictException $e) {
            // Already logged in with another user, this will be converted to HTTP status 409
            return new Values\Conflict();
        } catch (AuthenticationException $e) {
            $this->getAuthenticator()->logout($request);
            throw new UnauthorizedException('Invalid login or password', $request->getPathInfo());
        } catch (AccessDeniedException $e) {
            $this->getAuthenticator()->logout($request);
            throw new UnauthorizedException($e->getMessage(), $request->getPathInfo());
        }
    }

    /**
     * Refresh given session.
     *
     * @deprecated 4.6.7 The "SessionController::refreshSessionAction()" method is deprecated, will be removed in 5.0. Use SessionController::checkSessionAction() instead.
     *
     * @param string $sessionId
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\UserSession
     */
    public function refreshSessionAction($sessionId, Request $request)
    {
        trigger_deprecation(
            'ibexa/rest',
            '4.6.7',
            sprintf('The %s() method is deprecated, will be removed in 5.0.', __METHOD__)
        );

        $session = $request->getSession();

        if ($session === null || !$session->isStarted() || $session->getId() != $sessionId || !$this->hasStoredCsrfToken()) {
            $response = $this->getAuthenticator()->logout($request);
            $response->setStatusCode(404);

            return $response;
        }

        $this->checkCsrfToken($request);
        $currentUser = $this->userService->loadUser(
            $this->permissionResolver->getCurrentUserReference()->getUserId()
        );

        return new Values\UserSession(
            $currentUser,
            $session->getName(),
            $session->getId(),
            $request->headers->get('X-CSRF-Token'),
            false
        );
    }

    /**
     * @return \Ibexa\Rest\Server\Values\UserSession|\Symfony\Component\HttpFoundation\Response
     */
    public function checkSessionAction(Request $request)
    {
        $session = $request->getSession();

        if ($session === null || !$session->isStarted()) {
            $response = $this->getAuthenticator()->logout($request);
            $response->setStatusCode(404);

            return $response;
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
     * Deletes given session.
     *
     * @param string $sessionId
     *
     * @return Values\DeletedUserSession
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     */
    public function deleteSessionAction($sessionId, Request $request)
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();
        if (!$session->isStarted() || $session->getId() != $sessionId || !$this->hasStoredCsrfToken()) {
            $response = $this->getAuthenticator()->logout($request);
            $response->setStatusCode(404);

            return $response;
        }

        $this->checkCsrfToken($request);

        return new Values\DeletedUserSession($this->getAuthenticator()->logout($request));
    }

    /**
     * Tests if a CSRF token is stored.
     *
     * @return bool
     */
    private function hasStoredCsrfToken()
    {
        if ($this->csrfTokenManager === null) {
            return true;
        }

        return $this->csrfTokenManager->hasToken($this->csrfTokenIntention);
    }

    /**
     * Checks the presence / validity of the CSRF token.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException if the token is missing or invalid
     */
    private function checkCsrfToken(Request $request)
    {
        if ($this->csrfTokenManager === null) {
            return;
        }

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

    /**
     * Returns the csrf token for REST. The token is generated if it doesn't exist.
     *
     * @return string the csrf token, or an empty string if csrf check is disabled
     */
    private function getCsrfToken()
    {
        if ($this->csrfTokenManager === null) {
            return '';
        }

        return $this->csrfTokenManager->getToken($this->csrfTokenIntention)->getValue();
    }

    private function getAuthenticator(): ?AuthenticatorInterface
    {
        if (null === $this->authenticator) {
            throw new \RuntimeException(
                sprintf(
                    "No %s instance injected. Ensure 'ibexa.rest.session' is configured under your firewall",
                    AuthenticatorInterface::class
                )
            );
        }

        return $this->authenticator;
    }

    private function createInvalidCsrfTokenException(Request $request): UnauthorizedException
    {
        return new UnauthorizedException(
            'Missing or invalid CSRF token',
            $request->getMethod() . ' ' . $request->getPathInfo()
        );
    }
}

class_alias(SessionController::class, 'EzSystems\EzPlatformRest\Server\Controller\SessionController');
