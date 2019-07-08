<?php

declare(strict_types=1);

namespace WakeOnWeb\MessageBusReceiver\App\Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use WakeOnWeb\MessageBusReceiver\Infra\Message\MappingMessageFactory;
use WakeOnWeb\MessageBusReceiver\Infra\Queue\BernardReceiver;
use WakeOnWeb\MessageBusReceiver\Infra\Queue\RejectListener;
use WakeOnWeb\MessageBusReceiver\UI\Controller\RouteInputController;

final class WakeonwebMessageBusReceiverExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach ($config['buses'] as $busConfig) {
            $this->loadBus($busConfig['bus'], $busConfig, $container);
        }
    }

    private function loadBus(string $busName, array $busConfig, ContainerBuilder $container): void
    {
        foreach ($busConfig['inputs'] as $busInput => $inputConfig) {
            switch ($busInput) {
                case 'amqp':
                    $this->loadAmqpInput($busName, $inputConfig, $container);
                    break;
                case 'controller_route':
                    $definition = new Definition(RouteInputController::class, [
                        new Reference("prooph_service_bus.$busName"),
                        new Reference("prooph_service_bus.message_factory.$busName"),
                    ]);
                    $definition->setPublic(true);

                    $container->setDefinition("wakeonweb.message_bus_receiver.$busName.route_input", $definition);

                    break;
                default:
                    throw new \LogicException("Bus input $busInput not supported");
                    break;
            }

            if (array_key_exists('message_factory', $busConfig)) {
                $this->loadMessageFactory($busName, $busConfig['message_factory'], $container);
            }
        }
    }

    private function loadAmqpInput(string $busName, array $config, ContainerBuilder $container): void
    {
        $definition = new Definition(BernardReceiver::class, [
            new Reference("prooph_service_bus.$busName"), new Reference("prooph_service_bus.message_factory.$busName"),
        ]);
        $definition->addTag('bernard.receiver', ['message' => $config['message_name']]);
        $definition->setPublic(true);

        $container->setDefinition("wow.message_bus_receiver.$busName.queue.bernard.receiver", $definition);

        if ($config['move_to_error_queue_on_error']) {
            $definition = new Definition(RejectListener::class, [new Reference('bernard.queue_factory')]);
            $definition->addTag('kernel.event_subscriber');
            $definition->setPublic(true);

            $container->setDefinition("wow.message_bus_receiver.$busName.reject_listener", $definition);
        }

    }

    private function loadMessageFactory(string $busName, array $config, ContainerBuilder $container): void
    {
        $container->setDefinition(
            "wow.message_bus_receiver.$busName.message_factory",
            new Definition(MappingMessageFactory::class, [$config['mapping'], new Reference('service_container')])
        );
    }
}
