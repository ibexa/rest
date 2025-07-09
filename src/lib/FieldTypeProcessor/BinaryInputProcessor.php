<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\FieldTypeProcessor;

use Ibexa\Contracts\Rest\FieldTypeProcessor;

abstract class BinaryInputProcessor extends FieldTypeProcessor
{
    protected string $temporaryDirectory;

    public function __construct(string $temporaryDirectory)
    {
        $this->temporaryDirectory = $temporaryDirectory;
    }

    public function preProcessValueHash(mixed $incomingValueHash): mixed
    {
        if (isset($incomingValueHash['data'])) {
            $tempFile = tempnam($this->temporaryDirectory, 'eZ_REST_BinaryFile');

            file_put_contents(
                $tempFile,
                base64_decode($incomingValueHash['data'])
            );

            unset($incomingValueHash['data']);
            $incomingValueHash['inputUri'] = $tempFile;

            register_shutdown_function('unlink', $tempFile);
        }

        return $incomingValueHash;
    }
}
