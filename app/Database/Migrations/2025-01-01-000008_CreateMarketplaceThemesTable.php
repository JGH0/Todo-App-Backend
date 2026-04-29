<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMarketplaceThemesTable extends Migration
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
                'constraint' => 255,
                'null' => false,
            ],
            'display_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'author' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'version' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'thumbnail_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'download_url' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'is_published' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
                'default' => '{}',
                'comment' => 'tags, screenshots, etc.',
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
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('marketplace_themes');
    }

    public function down()
    {
        $this->forge->dropTable('marketplace_themes');
    }
}
