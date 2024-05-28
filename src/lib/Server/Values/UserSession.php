<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Rest\Value as RestValue;

final class UserSession extends RestValue
{
    public User $user;

    public string $sessionName;

    public string $sessionId;

    public string $csrfToken;

    public bool $exists;

    public bool $created;

    public function __construct(
        User $user,
        string $sessionName,
        string $sessionId,
        string $csrfToken,
        bool $created
    ) {
        $this->user = $user;
        $this->sessionName = $sessionName;
        $this->sessionId = $sessionId;
        $this->csrfToken = $csrfToken;
        $this->created = $created;
    }
}
