<?php

namespace WakeOnWeb\MessageBusReceiver\Infra\Queue;

use Bernard\BernardEvents;
use Bernard\Event\RejectEnvelopeEvent;
use Bernard\QueueFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RejectListener implements EventSubscriberInterface
{
    private $queueFactory;

    public function __construct(QueueFactory $queueFactory)
    {
        $this->queueFactory = $queueFactory;
    }

    public function onBernardReject(RejectEnvelopeEvent $event)
    {
        $queue = $event->getQueue();
        $envelope = $event->getEnvelope();

        $errorQueue = $this->queueFactory->create((string) $queue.'.error');
        $errorQueue->enqueue($envelope);

        $queue->acknowledge($envelope);
    }

    public static function getSubscribedEvents()
    {
        return array(
            BernardEvents::REJECT => 'onBernardReject',
        );
    }
}
