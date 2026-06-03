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

    // ========================================================================
    //   Pagination & Sorting
    // ========================================================================

    /**
     * Extract pagination params from the query string.
     *
     * Returns [page, perPage].
     * Default: page=1, perPage=50. Max perPage = 200.
     */
    protected function getPaginationParams(): array
    {
        $page    = max(1, (int) $this->request->getGet('page'));
        $perPage = min(200, max(1, (int) ($this->request->getGet('per_page') ?? 50)));
        return [$page, $perPage];
    }

    /**
     * Extract allowed sort params from the query string.
     *
     * ?sort=title,-created_at → ASC on title, DESC on created_at
     * Only fields listed in $allowed will be accepted.
     */
    protected function getSortParams(array $allowed = []): array
    {
        $raw = $this->request->getGet('sort');
        if (empty($raw)) {
            return [];
        }

        $parts = explode(',', $raw);
        $sorts = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;

            $dir = 'ASC';
            if ($part[0] === '-') {
                $dir = 'DESC';
                $part = substr($part, 1);
            }

            if (in_array($part, $allowed, true)) {
                $sorts[$part] = $dir;
            }
        }

        return $sorts;
    }

    /**
     * Extract allowed filter params from the query string.
     *
     * ?status=open&favorite=1
     * Only fields listed in $allowed will be accepted.
     */
    protected function getFilterParams(array $allowed = []): array
    {
        $filters = [];

        foreach ($allowed as $field) {
            $value = $this->request->getGet($field);
            if ($value !== null && $value !== '') {
                $filters[$field] = $value;
            }
        }

        return $filters;
    }

    /**
     * Apply sorting to a model query builder.
     */
    protected function applySort($query, array $sorts): void
    {
        foreach ($sorts as $field => $dir) {
            $query->orderBy($field, $dir);
        }
    }

    /**
     * Apply filters to a model query builder (simple WHERE).
     */
    protected function applyFilters($query, array $filters): void
    {
        foreach ($filters as $field => $value) {
            $query->where($field, $value);
        }
    }

    /**
     * Build a paginated response with meta information.
     */
    protected function paginatedResponse($query, string $message = 'Success', int $statusCode = 200)
    {
        [$page, $perPage] = $this->getPaginationParams();

        $total   = $query->countAllResults(false);
        $data    = $query->get($perPage, ($page - 1) * $perPage)->getResultArray();
        $lastPage = (int) ceil($total / max($perPage, 1));

        return $this->successResponse($data, $message, $statusCode, [
            'pagination' => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $total,
                'last_page' => $lastPage,
                'has_more'  => $page < $lastPage,
            ],
        ]);
    }

    // ========================================================================
    //   Success / Error Responses
    // ========================================================================

    /**
     * Success response
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200, array $extraMeta = [])
    {
        $body = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        if (!empty($extraMeta)) {
            foreach ($extraMeta as $key => $value) {
                $body[$key] = $value;
            }
        }

        return $this->response
            ->setStatusCode($statusCode)
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key')
            ->setJSON($body);
    }

    /**
     * Error response with structured error info
     */
    protected function errorResponse(string $message, int $statusCode = 400, $errors = null)
    {
        $body = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return $this->response
            ->setStatusCode($statusCode)
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key')
            ->setJSON($body);
    }

    /**
     * Validation error shorthand (422)
     */
    protected function validationErrorResponse($errors): void
    {
        $this->errorResponse('Validation failed', 422, $errors);
    }

    /**
     * Not found shorthand (404)
     */
    protected function notFoundResponse(string $resource = 'Resource'): void
    {
        $this->errorResponse("{$resource} not found", 404);
    }

    // ========================================================================
    //   Validation
    // ========================================================================

    /**
     * Validate request data using the rules defined in a model.
     *
     * Returns true on success, sends a 422 JSON response and returns false on failure.
     */
    protected function validateWithModel(\CodeIgniter\Model $model): bool|\CodeIgniter\HTTP\ResponseInterface
    {
        $validation = \Config\Services::validation();
        $rules      = $model->getValidationRules();
        $errors     = $model->getValidationMessages();

        if (empty($rules)) {
            return true;
        }

        $validation->setRules($rules, $errors);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->errorResponse('Validation failed', 422, $validation->getErrors());
        }

        return true;
    }

    /**
     * Legacy simple validation for controllers that define rules inline.
     */
    /**
     * Validate request data against the given rules.
     *
     * @param array $rules Field => rule definition (same format as CI4 validation rules)
     *
     * @return bool|\CodeIgniter\HTTP\ResponseInterface Returns true on success,
     *   or a ResponseInterface with validation error details on failure.
     */
    protected function validateRequest(array $rules): bool|\CodeIgniter\HTTP\ResponseInterface
    {
        $validation = \Config\Services::validation();

        // Build all rules first, then set them in one call
        $allRules  = [];
        $allErrors = [];

        foreach ($rules as $field => $rule) {
            if (is_array($rule) && isset($rule['rules'])) {
                $allRules[$field]  = $rule['rules'];
                $allErrors[$field] = $rule['errors'] ?? [];
            } else {
                $allRules[$field] = $rule;
            }
        }

        if ($allRules !== []) {
            $validation->setRules($allRules, $allErrors);
        }

        if (!$validation->withRequest($this->request)->run()) {
            return $this->errorResponse('Validation failed', 422, $validation->getErrors());
        }

        return true;
    }

    // ========================================================================
    //   Activity Logging
    // ========================================================================

    /**
     * Log an activity to the activity_logs table.
     * Safe — catches and logs errors silently so the main request is never broken.
     */
    protected function logActivity(string $action, string $entityType, ?string $entityId, ?array $details = null): void
    {
        try {
            $userId    = $this->getUserId();
            $logModel  = new \App\Models\ActivityLogModel();

            $logModel->logActivity([
                'user_id'   => $userId,
                'action'    => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'details'   => $details ? json_encode($details) : null,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
    }

    // ========================================================================
    //   JWT helpers
    // ========================================================================

    /**
     * JWT secret key — should be read from env in production.
     */
    protected function getJwtSecret(): string
    {
        return $_ENV['JWT_SECRET'] ?? 'todo-app-jwt-secret-change-in-production';
    }

    /**
     * Decode a JWT from the Authorization header.
     * Returns the payload on success, null on failure.
     */
    protected function decodeJwtFromRequest(): ?array
    {
        $header = $this->request->getHeaderLine('Authorization');
        if (empty($header)) return null;

        if (strpos($header, 'Bearer ') !== 0) return null;

        $token = substr($header, 7);
        return $this->decodeJwt($token);
    }

    /**
     * Decode and verify a JWT token.
     */
    protected function decodeJwt(string $token): ?array
    {
        try {
            $key    = $this->getJwtSecret();
            $jwt    = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
            return (array) $jwt;
        } catch (\Exception $e) {
            log_message('error', 'JWT decode failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Encode a payload into a JWT token.
     */
    protected function encodeJwt(array $payload): string
    {
        $key              = $this->getJwtSecret();
        $issuedAt         = time();
        $payload['iat']   = $issuedAt;
        $payload['exp']   = $issuedAt + 3600; // 1 hour default

        return \Firebase\JWT\JWT::encode($payload, $key, 'HS256');
    }

    // ========================================================================
    //   Helpers
    // ========================================================================

    /**
     * Generate UUID v4
     */
    protected function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
