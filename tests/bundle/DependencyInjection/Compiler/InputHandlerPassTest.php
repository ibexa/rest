<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Bundle\Rest\DependencyInjection\Compiler\InputHandlerPass;
use Ibexa\Rest\Input\Dispatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class InputHandlerPassTest extends TestCase
{
    public function testProcess()
    {
        $visitorDefinition = new Definition();
        $visitorDefinition->addTag('ibexa.rest.input.handler', ['format' => 'test']);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            [
                Dispatcher::class => new Definition(),
                'ezpublish_rest.input.handler.test' => $visitorDefinition,
            ]
        );

        $compilerPass = new InputHandlerPass();
        $compilerPass->process($containerBuilder);

        $dispatcherMethodCalls = $containerBuilder
            ->getDefinition(Dispatcher::class)
            ->getMethodCalls();
        self::assertTrue(isset($dispatcherMethodCalls[0][0]), 'Failed asserting that dispatcher has a method call');
        self::assertEquals('addHandler', $dispatcherMethodCalls[0][0], "Failed asserting that called method is 'addParser'");
        self::assertInstanceOf(Reference::class, $dispatcherMethodCalls[0][1][1], 'Failed asserting that method call is to a Reference object');

        self::assertEquals('ezpublish_rest.input.handler.test', $dispatcherMethodCalls[0][1][1]->__toString(), "Failed asserting that Referenced service is 'ezpublish_rest.input.handler.test'");
    }
}

class_alias(InputHandlerPassTest::class, 'EzSystems\EzPlatformRestBundle\Tests\DependencyInjection\Compiler\InputHandlerPassTest');
