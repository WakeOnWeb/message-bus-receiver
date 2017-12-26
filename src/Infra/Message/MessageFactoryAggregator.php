<?php

namespace WakeOnWeb\EventBusReceiver\Infra\Message;

use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\Common\Messaging\Message;
use WakeOnWeb\EventBusReceiver\Domain\Message\MessageFactoryInterface;
use WakeOnWeb\EventBusReceiver\Domain\Normalizer\NormalizerRepositoryInterface;

class MessageFactoryAggregator extends FQCNMessageFactory
{
    /**
     * @var MessageFactoryInterface[]
     */
    private $factories = [];

    public function __construct(array $factories = [])
    {
        foreach ($factories as $factory) {
            $this->addFactory($factory);
        }
    }

    public function createMessageFromArray(string $messageName, array $messageData): Message
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($messageName)) {
                return $factory->createMessageFromArray($messageName, $messageData);
            }
        }

        return parent::createMessageFromArray($messageName, $messageData);
    }

    private function addFactory(MessageFactoryInterface $messageFactory)
    {
        $this->factories[] = $messageFactory;
    }
}
