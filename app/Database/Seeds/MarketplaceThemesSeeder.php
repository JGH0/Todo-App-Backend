<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MarketplaceThemesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440010',
                'name' => 'default-light',
                'display_name' => 'Default Light',
                'description' => 'Clean and simple light theme',
                'author' => 'System',
                'version' => '1.0.0',
                'thumbnail_url' => null,
                'download_url' => '/themes/default-light.zip',
                'price' => 0,
                'is_published' => true,
                'metadata' => json_encode(['tags' => ['light', 'clean']]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440011',
                'name' => 'default-dark',
                'display_name' => 'Default Dark',
                'description' => 'Dark theme for night owls',
                'author' => 'System',
                'version' => '1.0.0',
                'thumbnail_url' => null,
                'download_url' => '/themes/default-dark.zip',
                'price' => 0,
                'is_published' => true,
                'metadata' => json_encode(['tags' => ['dark', 'night']]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('marketplace_themes')->insertBatch($data);
    }
}
