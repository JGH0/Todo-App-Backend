<?php

namespace App\Models;

use CodeIgniter\Model;

class UserThemeModel extends Model
{
    protected $table = 'user_themes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id',
        'user_id',
        'theme_id',
        'installed_at',
        'active',
        'custom_settings',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'user_id' => 'required',
        'theme_id' => 'required',
    ];

    // Install theme for user
    public function installTheme($userId, $themeId)
    {
        return $this->insert([
            'id' => $this->generateUuid(),
            'user_id' => $userId,
            'theme_id' => $themeId,
            'installed_at' => date('Y-m-d H:i:s'),
            'active' => false,
            'custom_settings' => json_encode([]),
        ]);
    }

    // Uninstall theme for user
    public function uninstallTheme($userId, $themeId)
    {
        return $this->where('user_id', $userId)
                    ->where('theme_id', $themeId)
                    ->delete();
    }

    // Set active theme for user
    public function setActiveTheme($userId, $themeId)
    {
        // Deactivate all themes for user
        $this->where('user_id', $userId)->update(['active' => false]);

        // Activate the specified theme
        return $this->where('user_id', $userId)
                    ->where('theme_id', $themeId)
                    ->update(['active' => true]);
    }

    // Get active theme for user
    public function getActiveTheme($userId)
    {
        return $this->select('user_themes.*, marketplace_themes.*')
                    ->join('marketplace_themes', 'user_themes.theme_id = marketplace_themes.id')
                    ->where('user_themes.user_id', $userId)
                    ->where('user_themes.active', true)
                    ->get()
                    ->getRowArray();
    }

    // Get all installed themes for user
    public function getUserThemes($userId)
    {
        return $this->select('user_themes.*, marketplace_themes.*')
                    ->join('marketplace_themes', 'user_themes.theme_id = marketplace_themes.id')
                    ->where('user_themes.user_id', $userId)
                    ->orderBy('user_themes.installed_at', 'DESC')
                    ->get()
                    ->getResultArray();
    }

    // Check if theme is installed for user
    public function isInstalled($userId, $themeId)
    {
        return $this->where('user_id', $userId)
                    ->where('theme_id', $themeId)
                    ->countAllResults() > 0;
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
