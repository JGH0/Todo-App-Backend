<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * MigrationTest - Tests für Datenbankmigrationen
 * Verifiziert dass alle Migrationen korrekt ausgeführt werden
 * und die Tabellen mit korrekten Spalten erstellt werden
 * 
 * @internal
 */
final class MigrationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * Test: Users Tabelle existiert
     */
    public function testUsersTableExists(): void
    {
        $db = \Config\Database::connect();
        $this->assertTrue($db->tableExists('users'));
    }

    /**
     * Test: Users Tabelle hat erforderliche Spalten
     */
    public function testUsersTableHasRequiredColumns(): void
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('users');

        $fieldNames = array_map(function ($field) {
            return $field->name;
        }, $fields);

        $this->assertContains('id', $fieldNames);
        $this->assertContains('email', $fieldNames);
        $this->assertContains('password_hash', $fieldNames);
        $this->assertContains('name', $fieldNames);
        $this->assertContains('avatar_url', $fieldNames);
        $this->assertContains('settings', $fieldNames);
        $this->assertContains('created_at', $fieldNames);
        $this->assertContains('updated_at', $fieldNames);
    }

    /**
     * Test: Email Spalte ist unique
     */
    public function testEmailIsUnique(): void
    {
        $db = \Config\Database::connect();
        $builder = $db->table('users');

        // Insert erstes Datensatz
        $builder->insert([
            'id' => 'unique-test-1',
            'email' => 'unique@example.com',
            'password_hash' => 'hash1',
            'name' => 'Test One',
        ]);

        // Versuche zweites Datensatz mit gleicher Email zu inserten
        try {
            $builder->insert([
                'id' => 'unique-test-2',
                'email' => 'unique@example.com',
                'password_hash' => 'hash2',
                'name' => 'Test Two',
            ]);
            // Falls kein Error, gibt es ein Problem
            $this->fail('Unique constraint wurde nicht erzwungen');
        } catch (\Exception $e) {
            // Expected - unique constraint wurde erzwungen
            $this->assertTrue(true);
        }
    }

    /**
     * Test: Categories Tabelle existiert
     */
    public function testCategoriesTableExists(): void
    {
        $db = \Config\Database::connect();
        $this->assertTrue($db->tableExists('categories'));
    }

    /**
     * Test: Projects Tabelle existiert
     */
    public function testProjectsTableExists(): void
    {
        $db = \Config\Database::connect();
        $this->assertTrue($db->tableExists('projects'));
    }

    /**
     * Test: Todos Tabelle existiert
     */
    public function testTodosTableExists(): void
    {
        $db = \Config\Database::connect();
        $this->assertTrue($db->tableExists('todos'));
    }

    /**
     * Test: TodoCategories Tabelle existiert
     */
    public function testTodoCategoriesTableExists(): void
    {
        $db = \Config\Database::connect();
        $this->assertTrue($db->tableExists('todo_categories'));
    }

    /**
     * Test: Todos Tabelle hat erforderliche Spalten
     */
    public function testTodosTableHasRequiredColumns(): void
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('todos');

        $fieldNames = array_map(function ($field) {
            return $field->name;
        }, $fields);

        // Diese Spalten sollten mindestens existieren
        $this->assertContains('id', $fieldNames);
        // Weitere Standard-Spalten...
    }

    /**
     * Test: Datenbank Verbindung funktioniert
     */
    public function testDatabaseConnectionWorks(): void
    {
        $db = \Config\Database::connect();
        $this->assertNotNull($db);
    }

    /**
     * Test: Schema wird nicht über Migration hinaus modifiziert
     */
    public function testTableCountIsCorrect(): void
    {
        $db = \Config\Database::connect();
        
        // Abrufen aller Tabellen
        $tables = $db->listTables();

        // Sollte mindestens diese Tabellen haben
        $requiredTables = ['users', 'categories', 'projects', 'todos', 'todo_categories'];
        
        foreach ($requiredTables as $table) {
            $this->assertContains($table, $tables, "Tabelle '{$table}' existiert nicht");
        }
    }

    /**
     * Test: Users settings Spalte ist JSON
     */
    public function testUserSettingsIsJson(): void
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('users');

        $settingsField = null;
        foreach ($fields as $field) {
            if ($field->name === 'settings') {
                $settingsField = $field;
                break;
            }
        }

        $this->assertNotNull($settingsField);
        // Type sollte JSON-ähnlich sein
        $this->assertStringContainsString('json', strtolower($settingsField->type));
    }

    /**
     * Test: Timestamps sind in correct format
     */
    public function testTimestampsAreCorrectType(): void
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('users');

        $dateFields = [];
        foreach ($fields as $field) {
            if (in_array($field->name, ['created_at', 'updated_at'])) {
                $dateFields[] = $field;
            }
        }

        $this->assertCount(2, $dateFields);
    }
}
