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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\InteractiveAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * @internal
 *
 * This is mandatory for proper REST API authentication, it's used within security.firewalls.ibexa_rest.custom_authenticators configuration key.
 */
final class RestAuthenticator extends AbstractAuthenticator implements InteractiveAuthenticatorInterface
{
    private const string LOGIN_ROUTE = 'ibexa.rest.create_session';

    public function __construct(
        private readonly Dispatcher $inputDispatcher,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === self::LOGIN_ROUTE;
    }

    public function authenticate(Request $request): Passport
    {
        $existingUserToken = $this->fetchExistingToken($request);
        if (null !== $existingUserToken && $this->canUserFromSessionBeAuthenticated($existingUserToken)) {
            $existingUser = $existingUserToken->getUser();

            return $this->createAuthorizationPassport(
                $existingUser->getUserIdentifier(),
                // @todo not sure how to refactor this
                ''
            );
        }

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

        return $this->createAuthorizationPassport($login, $password);
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
        throw new UnauthorizedException($exception->getMessageKey());
    }

    public function isInteractive(): bool
    {
        return true;
    }

    private function fetchExistingToken(Request $request): ?TokenInterface
    {
        // If a token already exists and username is the same as the one we request authentication for,
        // then return it and mark it as coming from session.
        $previousToken = $this->tokenStorage->getToken();
        if (
            $previousToken === null ||
            $previousToken->getUserIdentifier() !== $request->attributes->get('username')
        ) {
            return null;
        }

        $previousToken->setAttribute('isFromSession', true);

        return $previousToken;
    }

    /**
     * @phpstan-assert-if-true !null $existingUserToken->getUser()
     */
    private function canUserFromSessionBeAuthenticated(?TokenInterface $existingUserToken): bool
    {
        return $existingUserToken?->getUser() !== null;
    }

    private function createAuthorizationPassport(string $login, string $password): Passport
    {
        return new Passport(
            new UserBadge($login),
            new PasswordCredentials($password),
        );
    }
}
