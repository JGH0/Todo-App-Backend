<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityLogsTable extends Migration
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
                'null' => true,
                'comment' => 'Nullable for anonymous events',
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
                'comment' => 'e.g., todo_created, login, theme_installed',
            ],
            'entity_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'todo, category, project, etc.',
            ],
            'entity_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => true,
            ],
            'details' => [
                'type' => 'JSON',
                'null' => true,
                'default' => '{}',
                'comment' => 'before/after values, metadata',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey('action');
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('activity_logs');
    }

    public function down()
    {
        $this->forge->dropTable('activity_logs');
    }
}
