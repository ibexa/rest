<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\Controller;

use ApiPlatform\Symfony\Action\EntrypointAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiPlatformController extends AbstractController
{
    public function __construct(
        private readonly EntrypointAction $entrypointAction,
    ) {
    }

    public function documentationAction(Request $request): Response
    {
        return $this->entrypointAction->__invoke($request);
    }
}
