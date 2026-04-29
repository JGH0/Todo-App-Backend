<?php

namespace App\Models;

use CodeIgniter\Model;

class UserApiKeyModel extends Model
{
    protected $table = 'user_api_keys';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id',
        'user_id',
        'provider_id',
        'api_key_encrypted',
        'label',
        'is_active',
        'created_at',
        'last_used_at',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'user_id' => 'required',
        'provider_id' => 'required',
        'api_key_encrypted' => 'required',
    ];

    // Save or update API key for user and provider
    public function saveApiKey($userId, $providerId, $encryptedKey, $label = null)
    {
        $existing = $this->where('user_id', $userId)
                         ->where('provider_id', $providerId)
                         ->first();

        if ($existing) {
            return $this->update($existing['id'], [
                'api_key_encrypted' => $encryptedKey,
                'label' => $label,
                'is_active' => true,
                'last_used_at' => null,
            ]);
        } else {
            return $this->insert([
                'id' => $this->generateUuid(),
                'user_id' => $userId,
                'provider_id' => $providerId,
                'api_key_encrypted' => $encryptedKey,
                'label' => $label,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'last_used_at' => null,
            ]);
        }
    }

    // Get API key for user and provider
    public function getApiKey($userId, $providerId)
    {
        return $this->where('user_id', $userId)
                    ->where('provider_id', $providerId)
                    ->where('is_active', true)
                    ->first();
    }

    // Get all API keys for user
    public function getUserApiKeys($userId)
    {
        return $this->select('user_api_keys.*, ai_providers.name as provider_name, ai_providers.display_name')
                    ->join('ai_providers', 'user_api_keys.provider_id = ai_providers.id')
                    ->where('user_api_keys.user_id', $userId)
                    ->orderBy('user_api_keys.created_at', 'DESC')
                    ->get()
                    ->getResultArray();
    }

    // Deactivate API key
    public function deactivateApiKey($userId, $providerId)
    {
        return $this->where('user_id', $userId)
                    ->where('provider_id', $providerId)
                    ->update(['is_active' => false]);
    }

    // Update last used timestamp
    public function updateLastUsed($userId, $providerId)
    {
        return $this->where('user_id', $userId)
                    ->where('provider_id', $providerId)
                    ->update(['last_used_at' => date('Y-m-d H:i:s')]);
    }

    private function generateUuid()
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
