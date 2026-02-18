<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\DependencyInjection;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends SiteAccessConfiguration
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(IbexaRestExtension::EXTENSION_NAME);

        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->booleanNode('strict_mode')
                    ->defaultValue('%kernel.debug%')
                    ->info('Throw exceptions for missing normalizers.')
                ->end()
                ->arrayNode('badges')
                    ->info('Mapping of REST endpoint tag to Ibexa edition, used to render badges in the REST API documentation.')
                    ->arrayPrototype()
                        ->children()
                            ->stringNode('tag')->isRequired()->end()
                            ->arrayNode('editions')->isRequired()->stringPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        $this->addRestRootResourcesSection($rootNode);

        return $treeBuilder;
    }

    private function addRestRootResourcesSection(ArrayNodeDefinition $rootNode): void
    {
        $systemNode = $this->generateScopeBaseNode($rootNode);
        $systemNode
            ->arrayNode('rest_root_resources')
                ->prototype('array')
                    ->children()
                        ->scalarNode('mediaType')->isRequired()->end()
                        ->scalarNode('href')->isRequired()->end()
                    ->end()
                ->end()
            ->end();
    }
}
