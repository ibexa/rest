<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
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
                throw new UnauthorizedException('authorization', 'The current user is not authenticated.');
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
            throw new UnauthorizedException('Invalid login or password', $request->getPathInfo());
        } catch (AccessDeniedException $e) {
            throw new UnauthorizedException($e->getMessage(), $request->getPathInfo());
        }
    }

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

    /**
     * Refresh given session.
     *
     * @deprecated 4.6.7 The "SessionController::refreshSessionAction()" method is deprecated, will be removed in the next API version. Use SessionController::checkSessionAction() instead.
     *
     * @return \Ibexa\Rest\Server\Values\UserSession|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function refreshSessionAction(string $sessionId, Request $request)
    {
        trigger_deprecation(
            'ibexa/rest',
            '4.6.7',
            sprintf('The %s() method is deprecated, will be removed in the next API version.', __METHOD__)
        );

        $session = $request->getSession();

        if ($session === null || !$session->isStarted() || $session->getId() !== $sessionId || !$this->hasStoredCsrfToken()) {
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
     * @return \Ibexa\Rest\Server\Values\DeletedUserSession|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function deleteSessionAction(string $sessionId, Request $request)
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
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException if the token is missing or invalid
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
        return new UnauthorizedException(
            'Missing or invalid CSRF token',
            $request->getMethod() . ' ' . $request->getPathInfo()
        );
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

        $response->setStatusCode(404);
        $requestSession->clear();

        return $response;
    }
}
