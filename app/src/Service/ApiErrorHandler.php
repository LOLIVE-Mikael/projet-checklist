<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ApiErrorHandler
{
    public function createErrorResponse(\Exception $e, int $statusCode): JsonResponse
    {
        $errorDetails = [
            'status' => $statusCode,
            'detail' => sprintf($e->getMessage()),
            'title' => 'An error occurred',
            'type' => '/errors/' . $statusCode,
            'violations' => []
        ];

        return new JsonResponse($errorDetails, $statusCode);
    }

    public function createValidationErrorResponse(ConstraintViolationListInterface $violations): JsonResponse
    {
        $errorsArray = [];
        foreach ($violations as $violation) {
            $errorsArray[] = [
                'propertyPath' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
                'code' => $violation->getCode()
            ];
        }

        $errorDetails = [
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'violations' => $errorsArray,
            'detail' => $this->formatDetailMessage($violations),
            'type' => '/validation_errors/' . $errorsArray[0]['code'],
            'title' => 'An error occurred'
        ];
        return new JsonResponse($errorDetails, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function formatDetailMessage(ConstraintViolationListInterface $violations): string
    {
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
        }

        return implode(', ', $messages);
    }

    public function createCustomErrorResponse(
        int $statusCode, 
        string $message, 
        string $title = 'Error', 
        string $type = null
    ): JsonResponse {
        $errorDetails = [
            'status' => $statusCode,
            'detail' => $message,
            'title' => $title,
            'type' => $type ? $type : '/errors/' . $statusCode
        ];

        return new JsonResponse($errorDetails, $statusCode);
    }

}
