<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\Controller;

use ApiPlatform\Symfony\Action\EntrypointAction;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiPlatformController extends Controller
{
    public function __construct(
        private EntrypointAction $entrypointAction,
    ) {
    }

    public function documentationAction(Request $request): Response
    {
        return $this->entrypointAction->__invoke($request);
    }
}
