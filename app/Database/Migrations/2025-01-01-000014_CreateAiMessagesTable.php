<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAiMessagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'chat_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['user', 'assistant', 'system'],
                'null' => false,
            ],
            'content' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'tokens_used' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Optional token count for billing/analysis',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('chat_id');
        $this->forge->addForeignKey('chat_id', 'ai_chats', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ai_messages');
    }

    public function down()
    {
        $this->forge->dropTable('ai_messages');
    }
}
