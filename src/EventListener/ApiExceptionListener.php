<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\PaymentFailed;
use App\Exception\PricingException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Gives the API one error shape, whatever went wrong:
 *
 *     {"message": "...", "errors": [{"field": "taxNumber", "message": "..."}]}
 *
 * Rejected input is reported as 422, a body that could not be read at all as
 * 400. Anything unforeseen is left to Symfony, so a genuine bug stays visible
 * as a stack trace instead of being flattened into an API error.
 */
#[AsEventListener]
final class ApiExceptionListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof PaymentFailed) {
            // The processor's own wording can name transaction ids and
            // internal codes, so it goes to the log and the client gets a
            // stable line.
            $this->logger->error('Payment failed: {reason}', [
                'reason' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            $event->setResponse($this->respond(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'The payment was declined by the payment provider.',
                [['field' => 'paymentProcessor', 'message' => 'The payment could not be completed.']],
            ));

            return;
        }

        if ($exception instanceof PricingException) {
            $event->setResponse($this->respond(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'The request is invalid.',
                [['field' => $exception->field(), 'message' => $exception->getMessage()]],
            ));

            return;
        }

        if (!$exception instanceof HttpExceptionInterface) {
            return;
        }

        $previous = $exception->getPrevious();
        $status = $exception->getStatusCode();

        if ($previous instanceof ValidationFailedException) {
            $event->setResponse($this->respond($status, 'The request is invalid.', $this->violations($previous)));

            return;
        }

        if ($previous instanceof PartialDenormalizationException) {
            $event->setResponse($this->respond($status, 'The request is invalid.', $this->typeErrors($previous)));

            return;
        }

        $event->setResponse($this->respond($status, $exception->getMessage()));
    }

    /**
     * @return list<array{field: string, message: string}>
     */
    private function violations(ValidationFailedException $exception): array
    {
        $errors = [];

        foreach ($exception->getViolations() as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => (string) $violation->getMessage(),
            ];
        }

        return $errors;
    }

    /**
     * Type mismatches never reach the validator. The library's own wording
     * quotes internal class names, hence the rewrite.
     *
     * @return list<array{field: string, message: string}>
     */
    private function typeErrors(PartialDenormalizationException $exception): array
    {
        $errors = [];

        foreach ($exception->getErrors() as $error) {
            $expected = implode(' or ', $error->getExpectedTypes() ?? ['a different type']);

            $errors[] = [
                'field' => $error->getPath() ?? '',
                'message' => sprintf('This value should be of type %s.', $expected),
            ];
        }

        return $errors;
    }

    /**
     * @param list<array{field: string, message: string}> $errors
     */
    private function respond(int $status, string $message, array $errors = []): JsonResponse
    {
        $payload = ['message' => $message];

        if ([] !== $errors) {
            $payload['errors'] = $errors;
        }

        return new JsonResponse($payload, $status);
    }
}
