<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/groups/root',
    name: 'Get root User Group',
    openapi: new Model\Operation(
        summary: 'Redirects to the root User Group.',
        tags: [
            'User Group',
        ],
        parameters: [
        ],
        responses: [
            Response::HTTP_MOVED_PERMANENTLY => [
                'description' => 'Moved permanently.',
            ],
        ],
    ),
)]
final class UserGroupOfRootLoadController extends UserBaseController
{
    /**
     * Redirects to the root user group.
     */
    public function loadRootUserGroup(): Values\PermanentRedirect
    {
        //@todo Replace hardcoded value with one loaded from settings
        return new Values\PermanentRedirect(
            $this->router->generate('ibexa.rest.load_user_group', ['groupPath' => '/1/5'])
        );
    }
}
