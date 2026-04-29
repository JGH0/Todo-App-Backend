<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAiChatsTable extends Migration
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
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Generated from first message or user-set',
            ],
            'provider_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => true,
            ],
            'model_used' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Snapshot of model at chat creation',
            ],
            'system_prompt' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Optional custom system prompt for this chat',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('updated_at');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('provider_id', 'ai_providers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('ai_chats');
    }

    public function down()
    {
        $this->forge->dropTable('ai_chats');
    }
}
