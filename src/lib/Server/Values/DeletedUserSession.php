<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;
use Symfony\Component\HttpFoundation\Response;

final class DeletedUserSession extends RestValue
{
    public Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }
}
