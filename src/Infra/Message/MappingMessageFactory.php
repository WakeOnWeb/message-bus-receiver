<?php

namespace WakeOnWeb\MessageBusReceiver\Infra\Message;

use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\Common\Messaging\Message;
use Psr\Container\ContainerInterface;
use Prooph\Common\Messaging\MessageFactory;
use WakeOnWeb\MessageBusReceiver\Domain\Message\MessageFactoryInterface;

class MappingMessageFactory extends FQCNMessageFactory implements MessageFactory
{
    private $mapping;

    /** @var ContainerInterface */
    private $container;

    public function __construct(array $mapping = [], ContainerInterface $container)
    {
        $this->mapping = $mapping;
        $this->container = $container;
    }

    public function createMessageFromArray(string $messageName, array $messageData): Message
    {
        if (false === array_key_exists($messageName, $this->mapping)) {
            return parent::createMessageFromArray($messageName, $messageData);
        }

        $messageName = $this->mapping[$messageName];

        // this is a service.
        if (strpos($messageName, '@') === 0) {
            return $this->container
                ->get(substr($messageName, 1))
                ->createMessageFromArray($messageName, $messageData);
        }

        unset($messageData['message_name']);

        if (is_array($messageData['created_at'])) {
            $messageData['created_at'] = $this->createDateTimeImmutableFromArray($messageData['created_at']);
        }

        return parent::createMessageFromArray($messageName, $messageData);
    }

    private function createDateTimeImmutableFromArray(array $date): \DateTimeImmutable
    {
        if (false === array_key_exists('date', $date) || false === array_key_exists('timezone', $date)) {
            throw new \InvalidArgumentException('date in messageData cannot be transformed to a \DateTimeImmutable instance.');
        }

        return new \DateTimeImmutable(
            $date['date'],
            new \DateTimezone($date['timezone'])
        );
    }
}
