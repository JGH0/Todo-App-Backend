<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserThemesTable extends Migration
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
            'theme_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'installed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'active' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => 'Whether this is the user\'s currently active theme',
            ],
            'custom_settings' => [
                'type' => 'JSON',
                'null' => true,
                'default' => '{}',
                'comment' => 'User overrides for theme variables',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'theme_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('theme_id', 'marketplace_themes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_themes');
    }

    public function down()
    {
        $this->forge->dropTable('user_themes');
    }
}
