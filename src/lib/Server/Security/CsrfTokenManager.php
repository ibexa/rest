<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManager as BaseCsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenStorage\NativeSessionTokenStorage;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

final class CsrfTokenManager extends BaseCsrfTokenManager
{
    private TokenStorageInterface $storage;

    private string $namespace;

    public function __construct(
        ?TokenGeneratorInterface $generator = null,
        ?TokenStorageInterface $storage = null,
        ?RequestStack $requestStack = null
    ) {
        $this->storage = $storage ?: new NativeSessionTokenStorage();
        $this->namespace = $this->resolveNamespace($requestStack);

        parent::__construct($generator, $this->storage, $this->namespace);
    }

    public function hasToken(string $tokenId): bool
    {
        return $this->storage->hasToken($this->namespace . $tokenId);
    }

    private function resolveNamespace(?RequestStack $requestStack = null): string
    {
        if ($requestStack !== null && ($request = $requestStack->getMainRequest())) {
            return $request->isSecure() ? 'https-' : '';
        }

        return !empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']) ? 'https-' : '';
    }
}
