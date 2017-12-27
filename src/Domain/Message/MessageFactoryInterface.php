<?php

namespace WakeOnWeb\MessageBusReceiver\Domain\Message;

use Prooph\Common\Messaging\MessageFactory;

interface MessageFactoryInterface extends MessageFactory
{
    public function supports(string $messageName): bool;
}
