<?php

namespace Tests\Unit\Models;

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * UserModelTest - Unit Tests für das UserModel
 * Testet die Benutzerdatenbankoperationen
 * 
 * @internal
 */
final class UserModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App\Models';

    /**
     * Test: Benutzer kann erstellt werden
     */
    public function testUserCanBeCreated(): void
    {
        $userModel = new UserModel();
        
        $data = [
            'email' => 'user@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name' => 'Test User',
        ];

        $id = $userModel->insert($data);
        $this->assertIsNotNull($id);
    }

    /**
     * Test: Benutzer kann nach Email gefunden werden
     */
    public function testUserCanBeFoundByEmail(): void
    {
        $userModel = new UserModel();
        
        $data = [
            'email' => 'find@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name' => 'Find User',
        ];
        
        $userModel->insert($data);
        
        $user = $userModel->where('email', 'find@example.com')->first();
        
        $this->assertNotNull($user);
        $this->assertEquals('find@example.com', $user['email']);
        $this->assertEquals('Find User', $user['name']);
    }

    /**
     * Test: Doppelte Email wird verhindert
     */
    public function testDuplicateEmailIsRejected(): void
    {
        $userModel = new UserModel();
        
        $data = [
            'email' => 'duplicate@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name' => 'First User',
        ];
        
        $userModel->insert($data);
        
        $duplicateData = [
            'email' => 'duplicate@example.com',
            'password_hash' => password_hash('password456', PASSWORD_DEFAULT),
            'name' => 'Second User',
        ];
        
        $result = $userModel->insert($duplicateData);
        
        // Sollte false zurückgeben wegen Validierungsfehler
        $this->assertFalse($result);
    }

    /**
     * Test: Benutzer kann aktualisiert werden
     */
    public function testUserCanBeUpdated(): void
    {
        $userModel = new UserModel();
        
        $data = [
            'email' => 'update@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name' => 'Original Name',
        ];
        
        $id = $userModel->insert($data);
        
        $updateData = [
            'name' => 'Updated Name',
        ];
        
        $userModel->update($id, $updateData);
        
        $updated = $userModel->find($id);
        $this->assertEquals('Updated Name', $updated['name']);
    }

    /**
     * Test: Benutzer kann gelöscht werden
     */
    public function testUserCanBeDeleted(): void
    {
        $userModel = new UserModel();
        
        $data = [
            'email' => 'delete@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name' => 'Delete User',
        ];
        
        $id = $userModel->insert($data);
        $userModel->delete($id);
        
        $found = $userModel->find($id);
        $this->assertNull($found);
    }

    /**
     * Test: Alle Benutzer können abgerufen werden
     */
    public function testAllUsersCanBeRetrieved(): void
    {
        $userModel = new UserModel();
        
        // Insert mehrere Benutzer
        for ($i = 1; $i <= 3; $i++) {
            $userModel->insert([
                'email' => "user{$i}@example.com",
                'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                'name' => "User {$i}",
            ]);
        }
        
        $users = $userModel->findAll();
        $this->assertCount(3, $users);
    }

    /**
     * Test: Passwort Hash ist gültig
     */
    public function testPasswordHashIsValid(): void
    {
        $userModel = new UserModel();
        $password = 'mysecurepassword123';
        
        $data = [
            'email' => 'hash@example.com',
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name' => 'Hash Test',
        ];
        
        $userModel->insert($data);
        $user = $userModel->where('email', 'hash@example.com')->first();
        
        $this->assertTrue(password_verify($password, $user['password_hash']));
        $this->assertFalse(password_verify('wrongpassword', $user['password_hash']));
    }
}
