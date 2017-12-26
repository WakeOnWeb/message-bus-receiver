<?php

namespace WakeOnWeb\EventBusReceiver\Infra\Queue;

use Bernard\Message\PlainMessage;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\EventBus;
use WakeOnWeb\EventBusReceiver\Domain\Target\TargetRepositoryInterface;

class BernardReceiver
{
    /** var EventBus */
    private $eventBus;

    /** var MessageFactory */
    private $messageFactory;

    public function __construct(EventBus $eventBus, MessageFactory $messageFactory)
    {
        $this->eventBus = $eventBus;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param PlainMessage $message
     */
    public function __invoke(PlainMessage $message)
    {
        $messageData = $message['event'];

        $event = $this->messageFactory->createMessageFromArray(
            $messageData['message_name'],
            $messageData
        );

        $this->eventBus->dispatch($event);
    }

}
