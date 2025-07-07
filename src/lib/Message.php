<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest;

/**
 * Simple response struct.
 */
class Message
{
    /**
     * @var array<string, mixed>
     */
    public array $headers;

    public string $body;

    public int $statusCode;

    public function __construct(array $headers = [], string $body = '', int $statusCode = 200)
    {
        $this->headers = $headers;
        $this->body = $body;
        $this->statusCode = $statusCode;
    }
}
