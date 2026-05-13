<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\UserModel;
use App\Models\ApiAuthKeyModel;

class UserController extends BaseController
{
    protected $userModel;
    protected $apiAuthKeyModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->apiAuthKeyModel = new ApiAuthKeyModel();
    }

    /**
     * Get user profile
     * GET /api/v1/user/profile
     */
    public function profile()
    {
        $userId = $this->getUserId();
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        // Remove sensitive data
        unset($user['password_hash']);

        return $this->successResponse($user, 'Profile retrieved successfully');
    }

    /**
     * Update user profile
     * PUT /api/v1/user/profile
     */
    public function updateProfile()
    {
        $userId = $this->getUserId();
        $json = $this->request->getJSON(true);

        $allowedFields = ['name', 'avatar_url', 'settings'];
        $updateData = array_intersect_key($json, array_flip($allowedFields));

        if (empty($updateData)) {
            return $this->errorResponse('No valid fields to update');
        }

        $this->userModel->update($userId, $updateData);
        $user = $this->userModel->find($userId);
        unset($user['password_hash']);

        return $this->successResponse($user, 'Profile updated successfully');
    }

    /**
     * List user's API keys
     * GET /api/v1/user/api-keys
     */
    public function listApiKeys()
    {
        $userId = $this->getUserId();
        $apiKeys = $this->apiAuthKeyModel->getByUser($userId);

        // Remove sensitive data
        foreach ($apiKeys as &$key) {
            unset($key['key_hash']);
        }

        return $this->successResponse($apiKeys, 'API keys retrieved successfully');
    }

    /**
     * Create a new API key
     * POST /api/v1/user/api-keys
     */
    public function createApiKey()
    {
        $userId = $this->getUserId();
        $json = $this->request->getJSON(true);

        $name = $json['name'] ?? 'API Key';
        $scopes = $json['scopes'] ?? ['read', 'write'];
        $expiresAt = $json['expires_at'] ?? null;

        $apiKey = $this->apiAuthKeyModel->createKey(
            $userId,
            $name,
            $scopes,
            $expiresAt
        );

        return $this->successResponse([
            'id' => $apiKey['id'],
            'key' => $apiKey['key'],
            'prefix' => $apiKey['prefix'],
            'name' => $apiKey['name'],
            'scopes' => $apiKey['scopes'],
            'expires_at' => $apiKey['expires_at'],
        ], 'API key created successfully');
    }

    /**
     * Revoke an API key
     * DELETE /api/v1/user/api-keys/{id}
     */
    public function revokeApiKey($id)
    {
        $userId = $this->getUserId();
        $apiKey = $this->apiAuthKeyModel->find($id);

        if (!$apiKey || $apiKey['user_id'] !== $userId) {
            return $this->errorResponse('API key not found', 404);
        }

        $this->apiAuthKeyModel->revokeKey($id);

        return $this->successResponse(null, 'API key revoked successfully');
    }
}
