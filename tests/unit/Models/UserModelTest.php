<?php

namespace Tests\Unit\Models;

use App\Models\UserModel;
use PHPUnit\Framework\TestCase;

/**
 * UserModelTest - Unit Tests for the UserModel
 *
 * Tests CRUD operations on the users table.
 * Note: UserModel uses $useAutoIncrement = false, so all inserts
 * must provide a UUID string as the 'id' field.
 *
 * Uses plain PHPUnit TestCase to avoid CI4 test infrastructure
 * interfering with the real database.
 *
 * @internal
 */
final class UserModelTest extends TestCase
{
    private \mysqli $mysqli;

    protected function setUp(): void
    {
        parent::setUp();

        // Init CI4 environment
        // CI4 bootstrap is already loaded via phpunit.xml.dist

        $this->mysqli = new \mysqli('127.0.0.1', 'root', '', 'TodoApp', 3306);

        if ($this->mysqli->connect_error) {
            $this->fail('MySQL connection failed: ' . $this->mysqli->connect_error);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test users
        $this->mysqli->query("DELETE FROM users WHERE email LIKE '%@usermodel-test.local'");
        $this->mysqli->close();
        parent::tearDown();
    }

    /**
     * Generate a simple UUID for test records
     */
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

    public function testUserCanBeCreated(): void
    {
        $userModel = new UserModel();

        $data = [
            'id'            => $this->generateUuid(),
            'email'         => 'create-' . uniqid() . '@usermodel-test.local',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name'          => 'Test User',
        ];

        $id = $userModel->insert($data);
        $this->assertNotNull($id);

        $user = $userModel->find($id);
        $this->assertNotNull($user);
        $this->assertSame($data['email'], $user['email']);
    }

    public function testUserCanBeFoundByEmail(): void
    {
        $userModel = new UserModel();

        $email = 'find-' . uniqid() . '@usermodel-test.local';
        $data = [
            'id'            => $this->generateUuid(),
            'email'         => $email,
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name'          => 'Find User',
        ];

        $userModel->insert($data);

        $user = $userModel->where('email', $email)->first();

        $this->assertNotNull($user);
        $this->assertSame($email, $user['email']);
        $this->assertSame('Find User', $user['name']);
    }

    public function testDuplicateEmailIsRejected(): void
    {
        $userModel = new UserModel();

        $email = 'dupe-' . uniqid() . '@usermodel-test.local';

        $data = [
            'id'            => $this->generateUuid(),
            'email'         => $email,
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name'          => 'First User',
        ];

        $userModel->insert($data);

        $duplicateData = [
            'id'            => $this->generateUuid(),
            'email'         => $email,
            'password_hash' => password_hash('password456', PASSWORD_DEFAULT),
            'name'          => 'Second User',
        ];

        $result = $userModel->insert($duplicateData);

        $this->assertFalse($result);
    }

    public function testUserCanBeUpdated(): void
    {
        $userModel = new UserModel();

        $id = $this->generateUuid();
        $email = 'update-' . uniqid() . '@usermodel-test.local';
        $data = [
            'id'            => $id,
            'email'         => $email,
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name'          => 'Original Name',
        ];

        $userModel->insert($data);
        $userModel->update($id, ['name' => 'Updated Name']);

        $updated = $userModel->find($id);
        $this->assertSame('Updated Name', $updated['name']);
    }

    public function testUserCanBeDeleted(): void
    {
        $userModel = new UserModel();

        $id = $this->generateUuid();
        $email = 'delete-' . uniqid() . '@usermodel-test.local';
        $data = [
            'id'            => $id,
            'email'         => $email,
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name'          => 'Delete User',
        ];

        $userModel->insert($data);
        $userModel->delete($id);

        $found = $userModel->find($id);
        $this->assertNull($found);
    }

    public function testAllUsersCanBeRetrieved(): void
    {
        $userModel = new UserModel();

        for ($i = 1; $i <= 3; $i++) {
            $email = "user-{$i}-" . uniqid() . '@usermodel-test.local';
            $userModel->insert([
                'id'            => $this->generateUuid(),
                'email'         => $email,
                'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                'name'          => "User {$i}",
            ]);
        }

        $users = $userModel->findAll();
        $this->assertGreaterThanOrEqual(3, count($users));
    }

    public function testPasswordHashIsValid(): void
    {
        $userModel = new UserModel();
        $password = 'mysecurepassword123';

        $email = 'hash-' . uniqid() . '@usermodel-test.local';
        $data = [
            'id'            => $this->generateUuid(),
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name'          => 'Hash Test',
        ];

        $userModel->insert($data);
        $user = $userModel->where('email', $email)->first();

        $this->assertTrue(password_verify($password, $user['password_hash']));
        $this->assertFalse(password_verify('wrongpassword', $user['password_hash']));
    }
}
