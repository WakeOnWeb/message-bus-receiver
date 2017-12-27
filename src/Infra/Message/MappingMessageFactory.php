<?php

namespace WakeOnWeb\MessageBusReceiver\Infra\Message;

use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\Common\Messaging\Message;
use WakeOnWeb\MessageBusReceiver\Domain\Message\MessageFactoryInterface;

class MappingMessageFactory extends FQCNMessageFactory implements MessageFactoryInterface
{
    private $mapping;

    public function __construct(array $mapping = [])
    {
        $this->mapping = $mapping;
    }

    public function supports(string $messageName): bool
    {
        return array_key_exists($messageName, $this->mapping);
    }

    public function createMessageFromArray(string $messageName, array $messageData): Message
    {
        $messageName = $this->mapping[$messageName];
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
