<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;
use Symfony\Component\HttpFoundation\Response;

class DeletedUserSession extends RestValue
{
    /**
     * Response generated by RestAuthenticator.
     *
     * @see \Ibexa\Core\MVC\Symfony\Security\Authentication\AuthenticatorInterface::logout()
     *
     * @var \Symfony\Component\HttpFoundation\Response
     */
    public $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }
}

class_alias(DeletedUserSession::class, 'EzSystems\EzPlatformRest\Server\Values\DeletedUserSession');
