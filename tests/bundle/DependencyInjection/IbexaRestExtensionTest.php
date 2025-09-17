<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\DependencyInjection;

use Ibexa\Bundle\Rest\DependencyInjection\EditionBadgesProcessor;
use Ibexa\Bundle\Rest\DependencyInjection\IbexaRestExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

final class IbexaRestExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new IbexaRestExtension(),
        ];
    }

    public function testProcessingEditionBadgesConfiguration(): void
    {
        $this->container->setParameter(
            EditionBadgesProcessor::BADGES_CONFIG_PARAMETER_NAME,
            [
                'foo' => ['name' => 'Foo', 'color' => '#ff0000'],
                'bar' => ['name' => 'Bar', 'edition' => '#00ff00'],
            ]
        );

        $this->load(
            [
                'badges' => [
                    ['tag' => 'Foo', 'editions' => ['foo']],
                    ['tag' => 'Bar', 'editions' => ['foo', 'bar']],
                ],
            ]
        );

        $this->assertContainerBuilderHasParameter(
            EditionBadgesProcessor::TAG_EDITION_MAP_PARAMETER_NAME,
            [
                'Foo' => ['foo'],
                'Bar' => ['foo', 'bar'],
            ]
        );
    }
}
