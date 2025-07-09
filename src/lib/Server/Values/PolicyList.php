<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * Policy list view model.
 */
class PolicyList extends RestValue
{
    /**
     * Policies.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\User\Policy[]
     */
    public array $policies;

    /**
     * Path which was used to fetch the list of policies.
     */
    public string $path;

    /**
     * Construct.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Policy[] $policies
     */
    public function __construct(array $policies, string $path)
    {
        $this->policies = $policies;
        $this->path = $path;
    }
}
