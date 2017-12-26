<?php

namespace WakeOnWeb\EventBusReceiver\Infra\Message;

use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\Common\Messaging\Message;
use Psr\Container\ContainerInterface;
use WakeOnWeb\EventBusReceiver\Domain\Message\MessageFactoryInterface;
use WakeOnWeb\EventBusReceiver\Domain\Normalizer\NormalizerRepositoryInterface;

class LazyNormalizerMessageFactory extends FQCNMessageFactory implements MessageFactoryInterface
{
    /** @var array */
    private $normalizers;

    /** @var ContainerInterface */
    private $container;

    public function __construct(array $normalizers = [], ContainerInterface $container)
    {
        $this->normalizers = $normalizers;
        $this->container = $container;
    }

    public function supports(string $messageName): bool
    {
        return array_key_exists($messageName, $this->normalizers);
    }

    public function createMessageFromArray(string $messageName, array $messageData): Message
    {
        return $this->container
            ->get($this->normalizers[$messageName])
            ->createMessageFromArray($messageName, $messageData);
    }
}
