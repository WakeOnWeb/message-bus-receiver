<?php

namespace WakeOnWeb\MessageBusReceiver\Infra\Queue;

use Bernard\Message\PlainMessage;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\MessageBus;
use WakeOnWeb\MessageBusReceiver\Domain\Target\TargetRepositoryInterface;

class BernardReceiver
{
    /** var MessageBus */
    private $messageBus;

    /** var MessageFactory */
    private $messageFactory;

    public function __construct(MessageBus $messageBus, MessageFactory $messageFactory)
    {
        $this->messageBus = $messageBus;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param PlainMessage $message
     */
    public function __invoke(PlainMessage $message)
    {
        $messageData = $message['message'];

        $message = $this->messageFactory->createMessageFromArray(
            $messageData['message_name'],
            $messageData
        );

        $this->messageBus->dispatch($message);
    }

}
