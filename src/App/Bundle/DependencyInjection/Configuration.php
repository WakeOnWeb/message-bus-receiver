<?php

declare(strict_types=1);

namespace WakeOnWeb\EventBusReceiver\App\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('wakeonweb_event_bus_receiver')
            ->children()
                ->arrayNode('drivers')
                    ->children()
                        ->arrayNode('amqp')
                            ->children()
                                ->scalarNode('message_name')->defaultValue('EventBusExternalMessage')->cannotBeEmpty()->end()
                                ->scalarNode('prooph_bus')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('message_factory')
                    ->validate()
                        ->ifTrue(function($v) {
                            $mappingKeys = array_keys($v['mapping']);
                            $normalizerKeys = array_keys($v['normalizers']);

                            return false === empty(array_intersect($mappingKeys, $normalizerKeys));
                        })
                        ->thenInvalid('You defined an event id in MAPPING and NORMALIZERS, you must choose ...')
                    ->end()
                    ->children()
                        ->arrayNode('mapping')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                        ->arrayNode('normalizers')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
