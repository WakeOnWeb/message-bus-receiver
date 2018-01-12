<?php

namespace WakeOnWeb\MessageBusReceiver\UI\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\ServiceBus\MessageBus;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RouteInputController
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

    public function indexAction(Request $request)
    {
        $content = $request->getContent();
        $messageData = json_decode($content, true);

        if (false === is_array($messageData)) {
            throw new BadRequestHttpException("Cannot unserialize content $content");
        }

        $message = $this->messageFactory->createMessageFromArray(
            $messageData['message_name'],
            $messageData
        );

        $this->messageBus->dispatch($message);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
