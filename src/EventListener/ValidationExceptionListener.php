<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ValidationExceptionListener
{
    #[AsEventListener]
    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Only handle validation errors coming from MapRequestPayload
        if (!$exception instanceof UnprocessableEntityHttpException) {
            return;
        }

        $previous = $exception->getPrevious();

        if (!$previous instanceof ValidationFailedException) {
            return;
        }

        $errors = [];

        foreach ($previous->getViolations() as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        $event->setResponse(
            new JsonResponse(
                ['errors' => $errors],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            )
        );
    }
}
