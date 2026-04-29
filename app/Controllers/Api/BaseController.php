<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class BaseController extends ResourceController
{
    /**
     * Get the authenticated user from the request
     */
    protected function getUser(): ?array
    {
        return $this->request->user ?? null;
    }

    /**
     * Get the authenticated user ID
     */
    protected function getUserId(): ?string
    {
        $user = $this->getUser();
        return $user['id'] ?? null;
    }

    /**
     * Success response
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200)
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key')
            ->setJSON([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ]);
    }

    /**
     * Error response
     */
    protected function errorResponse(string $message, int $statusCode = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->response
            ->setStatusCode($statusCode)
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key')
            ->setJSON($response);
    }

    /**
     * Validate request data
     */
    protected function validateRequest(array $rules): bool
    {
        $validation = \Config\Services::validation();

        // Handle both old format (string) and new format (array with rules/errors)
        foreach ($rules as $field => $rule) {
            if (is_array($rule) && isset($rule['rules'])) {
                $validation->setRules([$field => $rule['rules']], $rule['errors'] ?? []);
            } else {
                $validation->setRule($field, $field, $rule);
            }
        }

        if (!$validation->withRequest($this->request)->run()) {
            $this->errorResponse('Validation failed', 422, $validation->getErrors());
            return false;
        }

        return true;
    }
}
