<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    /**
     * Handles kernel exceptions and converts them into JSON responses.
     *
     * @param ExceptionEvent $event The event containing the exception.
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpExceptionInterface) {
            $response = new JsonResponse([
                'error' => $exception->getMessage(),
            ], $exception->getStatusCode());
        } else {
            $response = new JsonResponse([
                'error' => 'An unexpected error occurred',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}
