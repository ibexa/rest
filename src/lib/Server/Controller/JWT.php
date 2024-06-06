<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller;

use Ibexa\Rest\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class JWT extends RestController
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function createToken(Request $request): ?RedirectResponse
    {
        if ($request->headers->get('Content-Type') === 'application/json') {
            return null;
        }

        return new RedirectResponse(
            $this->urlGenerator->generate('ibexa.rest.create_token'),
            307,
            [
                'Content-Type' => 'application/json',
            ]
        );
    }
}
