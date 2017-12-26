<?php

declare(strict_types=1);

namespace WakeOnWeb\EventBusReceiver\App\Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use WakeOnWeb\EventBusReceiver\Infra\Message\LazyNormalizerMessageFactory;
use WakeOnWeb\EventBusReceiver\Infra\Message\MappingMessageFactory;
use WakeOnWeb\EventBusReceiver\Infra\Message\MessageFactoryAggregator;
use WakeOnWeb\EventBusReceiver\Infra\Queue\BernardReceiver;

final class WakeonwebEventBusReceiverExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach ($config['drivers'] as $driver => $driverConfig) {
            switch ($driver) {
                case 'amqp':
                    $this->loadAmqpDriver($driverConfig, $container);
                    break;
                default:
                    throw new \LogicException(sprintf('Unknown “%s“ driver', $driver));
                    break;
            }
        }

        if (array_key_exists('message_factory', $config)) {
            $this->loadMessageFactory($config['message_factory'], $container);
        }
    }

    private function loadAmqpDriver(array $config, ContainerBuilder $container): void
    {
        $definition = new Definition(BernardReceiver::class, [
            new Reference(sprintf('prooph_service_bus.%s', $config['prooph_bus'])), new Reference(sprintf('prooph_service_bus.message_factory.%s', $config['prooph_bus'])),
        ]);
        $definition->addTag('bernard.receiver', ['message' => $config['message_name']]);

        $container->setDefinition('wow.event_bus_receiver.queue.bernard.receiver', $definition);
    }

    private function loadMessageFactory(array $config, ContainerBuilder $container): void
    {
        $factories = [];

        if (false === empty($config['mapping'])) {
            $factories[] = new Definition(MappingMessageFactory::class, [$config['mapping']]);
        }

        if (false === empty($config['normalizers'])) {
            $factories[] = new Definition(LazyNormalizerMessageFactory::class, [$config['normalizers'], new Reference('service_container')]);
        }

        $container->setDefinition('wow.event_bus_receiver.message_factory', new Definition(MessageFactoryAggregator::class, [$factories]));
    }
}
