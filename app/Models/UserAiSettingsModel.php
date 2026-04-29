<?php

namespace App\Models;

use CodeIgniter\Model;

class UserAiSettingsModel extends Model
{
    protected $table = 'user_ai_settings';
    protected $primaryKey = 'user_id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'default_provider_id',
        'default_model',
        'max_tokens',
        'temperature',
        'updated_at',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'user_id' => 'required',
        'max_tokens' => 'permit_empty|integer|greater_than[0]',
        'temperature' => 'permit_empty|numeric|greater_than_equal_to[0]|less_than_equal_to[2]',
    ];

    // Get or create settings for user
    public function getSettings($userId)
    {
        $settings = $this->find($userId);

        if (!$settings) {
            // Create default settings
            $this->insert([
                'user_id' => $userId,
                'default_provider_id' => null,
                'default_model' => null,
                'max_tokens' => 2048,
                'temperature' => 0.7,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $settings = $this->find($userId);
        }

        return $settings;
    }

    // Update settings for user
    public function updateSettings($userId, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($userId, $data);
    }

    // Get settings with provider info
    public function getSettingsWithProvider($userId)
    {
        return $this->select('user_ai_settings.*, ai_providers.name as provider_name, ai_providers.display_name')
                    ->join('ai_providers', 'user_ai_settings.default_provider_id = ai_providers.id', 'left')
                    ->where('user_ai_settings.user_id', $userId)
                    ->get()
                    ->getRowArray();
    }
}
