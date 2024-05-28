<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Security\Authenticator;

use Ibexa\Contracts\Rest\Exceptions\UnauthorizedException;
use Ibexa\Rest\Input\Dispatcher;
use Ibexa\Rest\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class RestAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly Dispatcher $inputDispatcher,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return
            $request->headers->has('Accept') &&
            str_contains($request->headers->get('Accept') ?? '', 'application/vnd.ibexa.api.Session');
    }

    public function authenticate(Request $request): Passport
    {
        /** @var \Ibexa\Rest\Server\Values\SessionInput $sessionInput */
        $sessionInput = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $login = $sessionInput->login;
        $password = $sessionInput->password;

        $request->attributes->set('username', $login);
        $request->attributes->set('password', $password);

        return new Passport(
            new UserBadge($sessionInput->login),
            new PasswordCredentials($password),
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        return null;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new UnauthorizedException($exception->getMessage());
    }
}
