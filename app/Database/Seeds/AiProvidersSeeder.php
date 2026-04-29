<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AiProvidersSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'name' => 'openai',
                'display_name' => 'OpenAI',
                'base_url' => 'https://api.openai.com/v1',
                'is_builtin' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440002',
                'name' => 'anthropic',
                'display_name' => 'Anthropic',
                'base_url' => 'https://api.anthropic.com',
                'is_builtin' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440003',
                'name' => 'google',
                'display_name' => 'Google AI',
                'base_url' => 'https://generativelanguage.googleapis.com/v1',
                'is_builtin' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('ai_providers')->insertBatch($data);
    }
}
