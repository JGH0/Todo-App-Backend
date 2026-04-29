<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiAuthKeyModel extends Model
{
    protected $table = 'api_auth_keys';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id',
        'user_id',
        'key_hash',
        'key_prefix',
        'name',
        'scopes',
        'expires_at',
        'last_used_at',
        'last_used_ip',
        'is_active',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $updatedField = null;

    protected $validationRules = [
        'user_id' => 'required',
        'key_hash' => 'required',
        'key_prefix' => 'required|max_length[20]',
    ];

    /**
     * Generate a new API key
     */
    public function generateKey(): string
    {
        return 'todo_' . bin2hex(random_bytes(32));
    }

    /**
     * Create a new API key for a user
     */
    public function createKey(string $userId, ?string $name = null, ?array $scopes = null, ?string $expiresAt = null): array
    {
        $key = $this->generateKey();
        $keyHash = hash('sha256', $key);
        $keyPrefix = substr($key, 0, 8);

        $data = [
            'id' => $this->generateUuid(),
            'user_id' => $userId,
            'key_hash' => $keyHash,
            'key_prefix' => $keyPrefix,
            'name' => $name,
            'scopes' => $scopes ? json_encode($scopes) : null,
            'expires_at' => $expiresAt,
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->insert($data);

        return [
            'id' => $data['id'],
            'key' => $key,
            'prefix' => $keyPrefix,
            'name' => $name,
            'scopes' => $scopes,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Validate an API key and return the associated user
     */
    public function validateKey(string $key): ?array
    {
        $keyHash = hash('sha256', $key);
        
        $authKey = $this->where('key_hash', $keyHash)
            ->where('is_active', true)
            ->first();

        if (!$authKey) {
            return null;
        }

        // Check if key has expired
        if ($authKey['expires_at'] && strtotime($authKey['expires_at']) < time()) {
            return null;
        }

        // Update last used information
        $this->update($authKey['id'], [
            'last_used_at' => date('Y-m-d H:i:s'),
            'last_used_ip' => $this->getClientIp(),
        ]);

        // Get the user
        $userModel = new UserModel();
        $user = $userModel->find($authKey['user_id']);

        if (!$user) {
            return null;
        }

        return [
            'user' => $user,
            'auth_key' => $authKey,
        ];
    }

    /**
     * Get all API keys for a user
     */
    public function getByUser(string $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Revoke an API key
     */
    public function revokeKey(string $keyId): bool
    {
        return $this->update($keyId, ['is_active' => false]);
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): ?string
    {
        try {
            $request = \Config\Services::request();
            return $request->getIPAddress();
        } catch (\Exception $e) {
            return null;
        }
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
