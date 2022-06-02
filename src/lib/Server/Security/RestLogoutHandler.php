<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Security;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Logout handler used by REST session based logout.
 * It forces session cookie clearing.
 */
class RestLogoutHandler implements LogoutHandlerInterface
{
    /**
     * @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @param \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver
     */
    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if (!$request->attributes->get('is_rest_request')) {
            return;
        }

        $path = '/';
        $domain = null;

        $session = $this->configResolver->getParameter('session');
        if (array_key_exists('cookie_domain', $session)) {
            $domain = $session['cookie_domain'];
        }
        if (array_key_exists('cookie_path', $session)) {
            $path = $session['cookie_path'];
        }

        $response->headers->clearCookie($request->getSession()->getName(), $path, $domain);
    }
}

class_alias(RestLogoutHandler::class, 'EzSystems\EzPlatformRest\Server\Security\RestLogoutHandler');
