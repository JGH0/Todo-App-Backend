<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserAiSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'default_provider_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => true,
            ],
            'default_model' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'e.g., gpt-4, claude-3-opus',
            ],
            'max_tokens' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 2048,
            ],
            'temperature' => [
                'type' => 'FLOAT',
                'default' => 0.7,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('default_provider_id', 'ai_providers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('user_ai_settings');
    }

    public function down()
    {
        $this->forge->dropTable('user_ai_settings');
    }
}
