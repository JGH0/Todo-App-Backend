<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\UserModel;
use App\Models\ApiAuthKeyModel;

class AuthController extends BaseController
{
    protected $userModel;
    protected $apiAuthKeyModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->apiAuthKeyModel = new ApiAuthKeyModel();
    }

    /**
     * Handle CORS preflight requests
     * OPTIONS /api/v1/auth/*
     */
    public function options()
    {
        return $this->response
            ->setStatusCode(200)
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key');
    }

    /**
     * Register a new user
     * POST /api/v1/auth/register
     */
    public function register()
    {
        $json = $this->request->getJSON(true);

        $rules = [
            'email' => [
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'Email is required',
                    'valid_email' => 'Please provide a valid email address',
                    'is_unique' => 'This email is already registered'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[8]',
                'errors' => [
                    'required' => 'Password is required',
                    'min_length' => 'Password must be at least 8 characters long'
                ]
            ],
            'name' => [
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'Name is required',
                    'max_length' => 'Name must not exceed 255 characters'
                ]
            ],
        ];

        if (!$this->validateRequest($rules)) {
            return;
        }

        try {
            // Generate UUID for user
            $userId = $this->generateUuid();

            // Create user
            $userData = [
                'id' => $userId,
                'email' => $json['email'],
                'password_hash' => password_hash($json['password'], PASSWORD_BCRYPT),
                'name' => $json['name'],
                'avatar_url' => $json['avatar_url'] ?? null,
                'settings' => isset($json['settings']) && $json['settings'] ? json_encode($json['settings']) : json_encode(['theme' => 'light']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->userModel->insert($userData);

            // Create API key for the new user
            $apiKey = $this->apiAuthKeyModel->createKey(
                $userId,
                'Default API Key',
                ['read', 'write'],
                null
            );

            // Remove sensitive data from response
            unset($userData['password_hash']);

            return $this->successResponse([
                'user' => $userData,
                'api_key' => $apiKey['key'],
                'key_prefix' => $apiKey['prefix'],
            ], 'User registered successfully', 201);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return $this->errorResponse('Database error: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Login user and return API key
     * POST /api/v1/auth/login
     */
    public function login()
    {
        $json = $this->request->getJSON(true);

        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validateRequest($rules)) {
            return;
        }

        try {
            // Authenticate user
            $user = $this->userModel->where('email', $json['email'])->first();

            if (!$user || !password_verify($json['password'], $user['password_hash'])) {
                return $this->errorResponse('Invalid email or password', 401);
            }

            // Check if user has an existing active API key
            $existingKey = $this->apiAuthKeyModel
                ->where('user_id', $user['id'])
                ->where('is_active', true)
                ->first();

            if ($existingKey) {
                // Return existing key
                return $this->successResponse([
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'name' => $user['name'],
                    ],
                    'api_key_prefix' => $existingKey['key_prefix'],
                    'message' => 'Using existing API key',
                ], 'Login successful');
            }

            // Create new API key
            $apiKey = $this->apiAuthKeyModel->createKey(
                $user['id'],
                'Login API Key',
                ['read', 'write'],
                null
            );

            return $this->successResponse([
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                ],
                'api_key' => $apiKey['key'],
                'key_prefix' => $apiKey['prefix'],
            ], 'Login successful');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            return $this->errorResponse('Database error: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create an API key using email and password (legacy endpoint)
     * POST /api/v1/auth/api-key
     */
    public function createApiKey()
    {
        $json = $this->request->getJSON(true);

        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validateRequest($rules)) {
            return;
        }

        // Authenticate user
        $user = $this->userModel->where('email', $json['email'])->first();

        if (!$user || !password_verify($json['password'], $user['password_hash'])) {
            return $this->errorResponse('Invalid email or password', 401);
        }

        // Create API key
        $name = $json['name'] ?? 'API Key';
        $scopes = $json['scopes'] ?? ['read', 'write'];
        $expiresAt = $json['expires_at'] ?? null;

        $apiKey = $this->apiAuthKeyModel->createKey(
            $user['id'],
            $name,
            $scopes,
            $expiresAt
        );

        return $this->successResponse([
            'key' => $apiKey['key'],
            'prefix' => $apiKey['prefix'],
            'name' => $apiKey['name'],
            'scopes' => $apiKey['scopes'],
            'expires_at' => $apiKey['expires_at'],
        ], 'API key created successfully');
    }

    /**
     * Generate UUID
     */
    private function generateUuid(): string
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
