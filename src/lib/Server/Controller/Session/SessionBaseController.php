<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\Session;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Rest\Exceptions\UnauthorizedException;
use Ibexa\Rest\Server\Controller;
use Ibexa\Rest\Server\Security\CsrfTokenManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface as SecurityTokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class SessionBaseController extends Controller
{
    public function __construct(
        protected readonly PermissionResolver $permissionResolver,
        protected readonly UserService $userService,
        protected readonly CsrfTokenManager $csrfTokenManager,
        protected readonly SecurityTokenStorageInterface $securityTokenStorage,
        protected readonly string $csrfTokenIntention,
        protected readonly ConfigResolverInterface $configResolver,
    ) {
    }

    protected function hasStoredCsrfToken(): bool
    {
        return $this->csrfTokenManager->hasToken($this->csrfTokenIntention);
    }

    /**
     * Checks the presence / validity of the CSRF token.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException if the token is missing or invalid
     */
    protected function checkCsrfToken(Request $request): void
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

    protected function getCsrfToken(): string
    {
        return $this->csrfTokenManager->getToken($this->csrfTokenIntention)->getValue();
    }

    protected function createInvalidCsrfTokenException(Request $request): UnauthorizedException
    {
        return new UnauthorizedException('Missing or invalid CSRF token');
    }

    protected function logout(Request $request): Response
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
