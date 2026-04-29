<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserApiKeysTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'user_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'provider_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'api_key_encrypted' => [
                'type' => 'TEXT',
                'null' => false,
                'comment' => 'Store encrypted API key',
            ],
            'label' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'e.g., Work OpenAI Key',
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'provider_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('provider_id', 'ai_providers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_api_keys');
    }

    public function down()
    {
        $this->forge->dropTable('user_api_keys');
    }
}
