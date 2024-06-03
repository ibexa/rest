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
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class RestAuthenticator extends AbstractAuthenticator
{
    private const string ACCEPT_HEADER = 'Accept';
    private const string CONTENT_TYPE_HEADER = 'Content-Type';
    private const string SESSION_HEADER_VALUE = 'application/vnd.ibexa.api.Session';
    private const string SESSION_INPUT_HEADER_VALUE = 'application/vnd.ibexa.api.SessionInput';

    public function __construct(
        private readonly Dispatcher $inputDispatcher,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return
            $request->headers->has(self::ACCEPT_HEADER) &&
            $request->headers->has(self::CONTENT_TYPE_HEADER) &&
            str_contains(
                $request->headers->get(self::ACCEPT_HEADER) ?? '',
                self::SESSION_HEADER_VALUE
            ) &&
            str_contains(
                $request->headers->get(self::CONTENT_TYPE_HEADER) ?? '',
                self::SESSION_INPUT_HEADER_VALUE
            );
    }

    public function authenticate(Request $request): Passport
    {
        $existingUserToken = $this->fetchExistingToken($request);
        if ($this->canUserFromSessionBeAuthenticated($existingUserToken)) {
            /** @phpstan-ignore-next-line */
            $existingUser = $existingUserToken->getUser();

            return $this->createAuthorizationPassport(
                /** @phpstan-ignore-next-line */
                $existingUser->getUserIdentifier(),
                /** @phpstan-ignore-next-line */
                $existingUser->getPassword()
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
        throw new UnauthorizedException($exception->getMessage());
    }

    private function fetchExistingToken(Request $request): ?TokenInterface
    {
        // If a token already exists and username is the same as the one we request authentication for,
        // then return it and mark it as coming from session.
        $previousToken = $this->tokenStorage->getToken();
        if (
            $previousToken === null ||
            $previousToken->getUsername() !== $request->attributes->get('username')
        ) {
            return null;
        }

        $previousToken->setAttribute('isFromSession', true);

        return $previousToken;
    }

    private function canUserFromSessionBeAuthenticated(?TokenInterface $existingUserToken): bool
    {
        if ($existingUserToken === null) {
            return false;
        }

        $user = $existingUserToken->getUser();
        if ($user === null || $user->getPassword() === null) {
            return false;
        }

        return true;
    }

    private function createAuthorizationPassport(string $login, string $password): Passport
    {
        return new Passport(
            new UserBadge($login),
            new PasswordCredentials($password),
        );
    }
}
