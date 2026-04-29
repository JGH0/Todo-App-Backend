<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRecurringTaskCategoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'recurring_task_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'category_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
        ]);
        $this->forge->addPrimaryKey(['recurring_task_id', 'category_id']);
        $this->forge->addForeignKey('recurring_task_id', 'recurring_tasks', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('recurring_task_categories');
    }

    public function down()
    {
        $this->forge->dropTable('recurring_task_categories');
    }
}
