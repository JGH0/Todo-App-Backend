<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRecurringTasksTable extends Migration
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
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'schedule' => [
                'type' => 'ENUM',
                'constraint' => ['daily', 'weekly', 'monthly', 'custom'],
                'null' => false,
            ],
            'custom_days' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Array of days e.g., ["mon","wed","fri"] when schedule=custom',
            ],
            'favorite' => [
                'type' => 'BOOLEAN',
                'default' => false,
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('recurring_tasks');
    }

    public function down()
    {
        $this->forge->dropTable('recurring_tasks');
    }
}
