<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\ApiPlatform;

use Ibexa\Rest\ApiPlatform\SchemasProviderInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

abstract class FileFinderSchemaProvider implements SchemasProviderInterface
{
    abstract protected function getFinder(): Finder;

    public function getSchemas(): array
    {
        $allSchemas = [];

        foreach ($this->getFinder() as $filePath) {
            $schemas = Yaml::parseFile($filePath->getRealPath());

            foreach ($schemas['schemas'] ?? [] as $schemaName => $schema) {
                if (isset($allSchemas[$schemaName])) {
                    throw new \LogicException(sprintf(
                        'Schema "%s" is already defined (tried to redefining in in "%s")',
                        $schemaName,
                        $filePath,
                    ));
                }
                $allSchemas[$schemaName] = $schema;
            }
        }

        return $allSchemas;
    }
}
