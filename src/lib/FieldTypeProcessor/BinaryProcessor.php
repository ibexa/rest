<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\FieldTypeProcessor;

class BinaryProcessor extends BinaryInputProcessor
{
    /**
     * Host prefix for uris, without a leading '/'.
     *
     * @todo Refactor such transformation with a service that receives the request and has the host
     */
    protected string $hostPrefix;

    public function __construct(string $temporaryDirectory, string $hostPrefix)
    {
        parent::__construct($temporaryDirectory);
        $this->hostPrefix = $hostPrefix;
    }

    public function postProcessValueHash(mixed $outgoingValueHash): mixed
    {
        if (!is_array($outgoingValueHash)) {
            return $outgoingValueHash;
        }

        $outgoingValueHash['uri'] = $this->generateUrl($outgoingValueHash['uri']);

        // url is kept for BC, but uri is the right one
        $outgoingValueHash['url'] = $outgoingValueHash['uri'];

        return $outgoingValueHash;
    }

    /**
     * Generates a URL for $path.
     *
     * @param string $path absolute url
     */
    protected function generateUrl(string $path): string
    {
        $url = $path;
        if ($this->hostPrefix) {
            // url should start with a /
            $url = $this->hostPrefix . $url;
        }

        return $url;
    }
}
