<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class ApiExceptionListener
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    #[AsEventListener]
    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Let validation handler deal with 422
        if ($exception instanceof HttpExceptionInterface) {
            return;
        }

        // Log full error internally
        $this->logger->error(
            $exception->getMessage(),
            ['exception' => $exception]
        );

        // Return safe response to client
        $event->setResponse(
            new JsonResponse(
                ['error' => 'Internal server error'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            )
        );
    }
}
