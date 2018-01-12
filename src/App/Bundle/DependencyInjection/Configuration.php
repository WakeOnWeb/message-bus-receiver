<?php

declare(strict_types=1);

namespace WakeOnWeb\MessageBusReceiver\App\Bundle\DependencyInjection;

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
        $treeBuilder->root('wakeonweb_message_bus_receiver')
            ->children()
                ->arrayNode('buses')
                    ->beforeNormalization()
                        ->always(function ($v) {
                            foreach ($v as $busName => $busConfig) {
                                if (false === array_key_exists('bus', $busConfig)) {
                                    $v[$busName]['bus'] = $busName;
                                }
                            }

                            return $v;
                        })
                    ->end()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('bus')->isRequired()->end()
                            ->arrayNode('inputs')
                                ->isRequired()
                                ->children()
                                    ->scalarNode('controller_route')->end()
                                    ->arrayNode('amqp')
                                        ->children()
                                            ->scalarNode('message_name')->isRequired()->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('message_factory')
                                ->children()
                                    ->arrayNode('mapping')
                                        ->prototype('scalar')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
