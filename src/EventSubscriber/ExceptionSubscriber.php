<?php

namespace App\EventSubscriber;

use App\Exception\ApiException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onExceptionEvent',
        ];
    }

    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        /**
         * 1️⃣ Domain / API exceptions
         */
        if ($exception instanceof ApiException) {
            $event->setResponse(new JsonResponse([
                'error' => [
                    'code' => $exception->getErrorCode(),
                    'message' => $exception->getMessage(),
                ],
            ], $exception->getStatusCode()));
            return;
        }

        /**
         * 2️⃣ Validation errors (MapRequestPayload)
         */
        if (
            $exception instanceof UnprocessableEntityHttpException &&
            $exception->getPrevious() instanceof ValidationFailedException
        ) {
            /** @var ValidationFailedException $previous */
            $previous = $exception->getPrevious();
        
            $errors = [];
        
            foreach ($previous->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }
        
            $event->setResponse(new JsonResponse([
                'errors' => $errors,
            ], 422));
        
            return;
        }        

        /**
         * 3️⃣ Fallback (500)
         */
        $event->setResponse(new JsonResponse([
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => 'Internal server error',
            ],
        ], 500));
    }
}
