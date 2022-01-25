<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Bundle\Rest\DependencyInjection\Compiler\InputParserPass;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class InputParserPassTest extends TestCase
{
    public function testProcess()
    {
        $visitorDefinition = new Definition();
        $visitorDefinition->addTag(
            'ibexa.rest.input.parser',
            ['mediaType' => 'application/vnd.ibexa.api.UnitTest']
        );

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            [
                ParsingDispatcher::class => new Definition(),
                'ezpublish_rest.input.parser.unit_test' => $visitorDefinition,
            ]
        );

        $compilerPass = new InputParserPass();
        $compilerPass->process($containerBuilder);

        $dispatcherMethodCalls = $containerBuilder
            ->getDefinition(ParsingDispatcher::class)
            ->getMethodCalls();
        self::assertTrue(isset($dispatcherMethodCalls[0][0]), 'Failed asserting that dispatcher has a method call');
        self::assertEquals('addParser', $dispatcherMethodCalls[0][0], "Failed asserting that called method is 'addParser'");
        self::assertInstanceOf(Reference::class, $dispatcherMethodCalls[0][1][1], 'Failed asserting that method call is to a Reference object');

        self::assertEquals('ezpublish_rest.input.parser.unit_test', $dispatcherMethodCalls[0][1][1]->__toString(), "Failed asserting that Referenced service is 'ezpublish_rest.input.parser.unit_test'");
    }
}

class_alias(InputParserPassTest::class, 'EzSystems\EzPlatformRestBundle\Tests\DependencyInjection\Compiler\InputParserPassTest');
