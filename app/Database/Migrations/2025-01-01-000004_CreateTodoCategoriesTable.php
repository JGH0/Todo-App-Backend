<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTodoCategoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'todo_id' => [
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
        $this->forge->addPrimaryKey(['todo_id', 'category_id']);
        $this->forge->addForeignKey('todo_id', 'todos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('todo_categories');
    }

    public function down()
    {
        $this->forge->dropTable('todo_categories');
    }
}
