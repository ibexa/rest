<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Bundle\Rest\DependencyInjection\Compiler\ValueObjectVisitorResolverPass;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitorResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ValueObjectVisitorResolverPassTest extends TestCase
{
    public function testProcess(): void
    {
        $visitorDefinition = new Definition();
        $visitorDefinition->addTag('ibexa.rest.output.value_object.visitor', ['type' => 'test']);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            [
                ValueObjectVisitorResolver::class => new Definition(),
                'ezpublish_rest.output.value_object_visitor.test' => $visitorDefinition,
            ]
        );

        $compilerPass = new ValueObjectVisitorResolverPass();
        $compilerPass->process($containerBuilder);

        $dispatcherMethodCalls = $containerBuilder
            ->getDefinition(ValueObjectVisitorResolver::class)
            ->getMethodCalls();
        self::assertTrue(isset($dispatcherMethodCalls[0][0]), 'Failed asserting that dispatcher has a method call');
        self::assertEquals('addVisitor', $dispatcherMethodCalls[0][0], "Failed asserting that called method is 'addVisitor'");
        self::assertInstanceOf(Reference::class, $dispatcherMethodCalls[0][1][1], 'Failed asserting that method call is to a Reference object');

        self::assertEquals('ezpublish_rest.output.value_object_visitor.test', $dispatcherMethodCalls[0][1][1]->__toString(), "Failed asserting that Referenced service is 'ezpublish_rest.output.value_object_visitor.test'");
    }
}
