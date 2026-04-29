<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAiProvidersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
                'comment' => 'openai, anthropic, google, etc.',
            ],
            'display_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'base_url' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Override endpoint',
            ],
            'is_builtin' => [
                'type' => 'BOOLEAN',
                'default' => true,
                'comment' => 'False for user-added custom providers',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('ai_providers');
    }

    public function down()
    {
        $this->forge->dropTable('ai_providers');
    }
}
