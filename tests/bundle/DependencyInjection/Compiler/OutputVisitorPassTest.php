<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Bundle\Rest\DependencyInjection\Compiler\OutputVisitorPass;
use Ibexa\Rest\Server\View\AcceptHeaderVisitorDispatcher;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class OutputVisitorPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new OutputVisitorPass());
    }

    public function testProcess(): void
    {
        $stringRegexp = '(^.*/.*$)';
        $stringDefinition = new Definition();
        $stringDefinition->addTag(
            'ibexa.rest.output.visitor',
            ['regexps' => 'ezpublish_rest.output.visitor.test.regexps']
        );
        $this->setParameter('ezpublish_rest.output.visitor.test.regexps', [$stringRegexp]);
        $this->setDefinition('ezpublish_rest.output.visitor.test_string', $stringDefinition);

        $arrayRegexp = '(^application/json$)';
        $arrayDefinition = new Definition();
        $arrayDefinition->addTag('ibexa.rest.output.visitor', ['regexps' => [$arrayRegexp]]);
        $this->setDefinition('ezpublish_rest.output.visitor.test_array', $arrayDefinition);

        $this->setDefinition(AcceptHeaderVisitorDispatcher::class, new Definition());

        $this->compile();

        $visitorsInOrder = $this->getVisitorsInRegistrationOrder();

        self::assertEquals('ezpublish_rest.output.visitor.test_string', $visitorsInOrder[0]);
        self::assertEquals('ezpublish_rest.output.visitor.test_array', $visitorsInOrder[1]);
        $this->assertContainerBuilderHasService('ezpublish_rest.output.visitor.test_string');
        $this->assertContainerBuilderHasService('ezpublish_rest.output.visitor.test_array');
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(AcceptHeaderVisitorDispatcher::class, 'addVisitor', [
            $stringRegexp,
            new Reference('ezpublish_rest.output.visitor.test_string'),
        ]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(AcceptHeaderVisitorDispatcher::class, 'addVisitor', [
            $arrayRegexp,
            new Reference('ezpublish_rest.output.visitor.test_array'),
        ]);
    }

    public function testPriority(): void
    {
        $definitions = [
            'high' => [
                'regexps' => ['(^.*/.*$)'],
                'priority' => 10,
            ],
            'low' => [
                'regexps' => ['(^application/.*$)'],
                'priority' => -10,
            ],
            'normal_defined' => [
                'regexps' => ['(^application/json$)'],
                'priority' => 0,
            ],
            'normal' => [
                'regexps' => ['(^application/xml$)'],
            ],
        ];

        $expectedPriority = [
            'high',
            'normal_defined',
            'normal',
            'low',
        ];

        $this->setDefinition(AcceptHeaderVisitorDispatcher::class, new Definition());

        foreach ($definitions as $name => $data) {
            $definition = new Definition();
            $definition->addTag('ibexa.rest.output.visitor', $data);
            $this->setDefinition('ezpublish_rest.output.visitor.test_' . $name, $definition);
        }

        $this->compile();

        $visitorsInOrder = $this->getVisitorsInRegistrationOrder();

        foreach ($expectedPriority as $index => $priority) {
            self::assertEquals('ezpublish_rest.output.visitor.test_' . $priority, $visitorsInOrder[$index]);
        }
    }

    /**
     * @return array<string>
     */
    protected function getVisitorsInRegistrationOrder(): array
    {
        $calls = $this->container->getDefinition(AcceptHeaderVisitorDispatcher::class)->getMethodCalls();

        return array_map(static function ($call): string {
            return (string) $call[1][1];
        }, $calls);
    }
}
