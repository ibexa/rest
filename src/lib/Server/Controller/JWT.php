<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller;

use Ibexa\Rest\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\Request;

final class JWT extends RestController
{
    public function createToken(Request $request): void
    {
        //empty method for Symfony json_login authenticator which is used by Lexik/JWT under the hood
        // for more detail refer to: https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/index.html#symfony-5-3-and-higher
    }
}
