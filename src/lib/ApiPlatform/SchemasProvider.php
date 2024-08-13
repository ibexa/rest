<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\ApiPlatform;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

final class SchemasProvider implements SchemasProviderInterface
{
    /**
     * @param array<string> $files
     */
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly array $files,
    ) {
    }

    public function getSchemas(): array
    {
        $allSchemas = [];

        foreach ($this->files as $fileName) {
            $filePath = $this->kernel->locateResource($fileName);
            $schemas = Yaml::parseFile($filePath);

            if (isset($schemas['schemas'])) {
                $allSchemas = array_merge($allSchemas, $schemas['schemas']);
            }
        }

        return $allSchemas;
    }
}
