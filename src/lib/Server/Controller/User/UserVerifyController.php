<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use ApiPlatform\OpenApi\Model;
use Ibexa\Bundle\Rest\ApiPlatform\Head;
use Ibexa\Contracts\Rest\Exceptions\NotFoundException;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Head(
    uriTemplate: '/user/users',
    openapi: new Model\Operation(
        summary: 'Verify Users',
        description: 'Verifies if there are Users matching given filter.',
        tags: [
            'User',
        ],
        parameters: [
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - verifies if there are Users matching the given filter.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - there are no visibile Users matching the filter.',
            ],
        ],
    ),
)]
final class UserVerifyController extends UserBaseController
{
    /**
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     */
    public function verifyUsers(Request $request): Values\OK
    {
        // We let the NotFoundException loadUsers throws if there are no results pass.
        $this->loadUsers($request)->users;

        return new Values\OK();
    }
}
