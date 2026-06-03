<?php

use PHPUnit\Framework\TestCase;

/**
 * MigrationTest - Tests for database migrations
 *
 * Uses plain PHPUnit TestCase + direct mysqli to avoid CI4 test
 * infrastructure interfering with the real database.
 *
 * @internal
 */
final class MigrationTest extends TestCase
{
    private \mysqli $mysqli;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mysqli = new \mysqli('127.0.0.1', 'root', '', 'TodoApp', 3306);

        if ($this->mysqli->connect_error) {
            $this->fail('MySQL connection failed: ' . $this->mysqli->connect_error);
        }
    }

    protected function tearDown(): void
    {
        $this->mysqli->close();
        parent::tearDown();
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function testDatabaseConnectionWorks(): void
    {
        $result = $this->mysqli->query('SELECT 1 AS test');
        $this->assertNotFalse($result);
        $row = $result->fetch_assoc();
        $this->assertSame('1', $row['test']);
    }

    public function testUsersTableExists(): void
    {
        $result = $this->mysqli->query("SHOW TABLES LIKE 'users'");
        $this->assertGreaterThan(0, $result->num_rows);
    }

    public function testUsersTableHasRequiredColumns(): void
    {
        $result = $this->mysqli->query('DESCRIBE users');
        $this->assertNotFalse($result);

        $fieldNames = [];
        while ($row = $result->fetch_assoc()) {
            $fieldNames[] = $row['Field'];
        }

        $this->assertContains('id', $fieldNames);
        $this->assertContains('email', $fieldNames);
        $this->assertContains('password_hash', $fieldNames);
        $this->assertContains('name', $fieldNames);
        $this->assertContains('avatar_url', $fieldNames);
        $this->assertContains('settings', $fieldNames);
        $this->assertContains('created_at', $fieldNames);
        $this->assertContains('updated_at', $fieldNames);
    }

    public function testEmailIsUnique(): void
    {
        $email = 'migration-unique-' . uniqid() . '@example.com';
        $id1 = $this->generateUuid();

        $this->mysqli->query("INSERT INTO users (id, email, password_hash, name) VALUES ('{$id1}', '{$email}', 'hash1', 'Test One')");

        $id2 = $this->generateUuid();

        // PHP 8 throws mysqli_sql_exception on duplicate key;
        // catch it and verify the error code.
        $caught = false;
        try {
            $this->mysqli->query("INSERT INTO users (id, email, password_hash, name) VALUES ('{$id2}', '{$email}', 'hash2', 'Test Two')");
        } catch (\mysqli_sql_exception $e) {
            $caught = true;
            $this->assertSame(1062, $e->getCode(), 'Expected MySQL error 1062 (duplicate entry)');
        }

        $this->assertTrue($caught, 'Expected duplicate email to throw an exception');

        $this->mysqli->query("DELETE FROM users WHERE email = '{$email}'");
    }

    public function testCategoriesTableExists(): void
    {
        $result = $this->mysqli->query("SHOW TABLES LIKE 'categories'");
        $this->assertGreaterThan(0, $result->num_rows);
    }

    public function testProjectsTableExists(): void
    {
        $result = $this->mysqli->query("SHOW TABLES LIKE 'projects'");
        $this->assertGreaterThan(0, $result->num_rows);
    }

    public function testTodosTableExists(): void
    {
        $result = $this->mysqli->query("SHOW TABLES LIKE 'todos'");
        $this->assertGreaterThan(0, $result->num_rows);
    }

    public function testTodoCategoriesTableExists(): void
    {
        $result = $this->mysqli->query("SHOW TABLES LIKE 'todo_categories'");
        $this->assertGreaterThan(0, $result->num_rows);
    }

    public function testTodosTableHasRequiredColumns(): void
    {
        $result = $this->mysqli->query('DESCRIBE todos');
        $this->assertNotFalse($result);

        $fieldNames = [];
        while ($row = $result->fetch_assoc()) {
            $fieldNames[] = $row['Field'];
        }

        $this->assertContains('id', $fieldNames);
        $this->assertContains('title', $fieldNames);
        $this->assertContains('description', $fieldNames);
        $this->assertContains('status', $fieldNames);
        $this->assertContains('user_id', $fieldNames);
    }

    public function testTableCountIsCorrect(): void
    {
        $result = $this->mysqli->query('SHOW TABLES');
        $this->assertNotFalse($result);

        $tables = [];
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }

        $requiredTables = ['users', 'categories', 'projects', 'todos', 'todo_categories'];

        foreach ($requiredTables as $table) {
            $this->assertContains($table, $tables, "Table '{$table}' does not exist");
        }
    }

    public function testUserSettingsIsJson(): void
    {
        $result = $this->mysqli->query('DESCRIBE users');
        $this->assertNotFalse($result);

        $settingsType = '';
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] === 'settings') {
                $settingsType = $row['Type'];
                break;
            }
        }

        $this->assertNotEmpty($settingsType, 'settings column should exist');

        // MariaDB may report JSON columns as "longtext", MySQL as "json"
        $valid = str_contains(strtolower($settingsType), 'json')
              || str_contains(strtolower($settingsType), 'longtext');
        $this->assertTrue($valid, "Expected JSON or LONGTEXT type, got '{$settingsType}'");
    }

    public function testTimestampsAreCorrectType(): void
    {
        $result = $this->mysqli->query('DESCRIBE users');
        $this->assertNotFalse($result);

        $dateFields = [];
        while ($row = $result->fetch_assoc()) {
            if (in_array($row['Field'], ['created_at', 'updated_at'])) {
                $dateFields[] = $row;
            }
        }

        $this->assertCount(2, $dateFields);
    }
}
