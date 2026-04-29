<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiAuthKeysTable extends Migration
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
            'key_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
                'comment' => 'SHA-256 hash of the API key',
            ],
            'key_prefix' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'comment' => 'First 8 characters for identification',
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'User-friendly name for the key',
            ],
            'scopes' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Array of allowed scopes (e.g., ["read", "write"])',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Optional expiration date',
            ],
            'last_used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_used_ip' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => 'IPv4 or IPv6 address',
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('key_hash');
        $this->forge->addKey('is_active');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('api_auth_keys');
    }

    public function down()
    {
        $this->forge->dropTable('api_auth_keys');
    }
}
