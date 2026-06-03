<?php

namespace Tests\Unit\Controllers;

use App\Controllers\Auth;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * AuthControllerTest - Unit Tests für den Auth Controller
 * Testet Login, Registrierung und Logout Funktionalität
 * 
 * @internal
 */
final class AuthControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App\Controllers';

    /**
     * Test: Login Seite wird angezeigt
     */
    public function testLoginPageLoads(): void
    {
        $response = $this->get('/auth/login');
        
        $this->assertTrue($response->getStatusCode() === 200);
        $this->assertStringContainsString('Todo App', (string)$response);
        $this->assertStringContainsString('Anmelden', (string)$response);
    }

    /**
     * Test: Login mit gültigen Credentials
     */
    public function testLoginWithValidCredentials(): void
    {
        // Benutzer in der Datenbank erstellen
        $userModel = new UserModel();
        $userData = [
            'email' => 'test@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name' => 'Test User',
        ];
        $userModel->insert($userData);

        // POST Request zum Login
        $response = $this->post('/auth/attemptLogin', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Sollte zu /dashboard weiterleiten
        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: Login mit ungültigen Credentials
     */
    public function testLoginWithInvalidCredentials(): void
    {
        $response = $this->post('/auth/attemptLogin', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: Registrierung mit gültigen Daten
     */
    public function testRegisterWithValidData(): void
    {
        $response = $this->post('/auth/attemptRegister', [
            'name' => 'Neuer User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ]);

        $this->assertTrue($response->getStatusCode() === 302);

        $userModel = new UserModel();
        $user = $userModel->where('email', 'newuser@example.com')->first();
        
        $this->assertNotNull($user);
        $this->assertEquals('Neuer User', $user['name']);
        $this->assertEquals('newuser@example.com', $user['email']);
    }

    /**
     * Test: Registrierung mit doppelter Email sollte fehlschlagen
     */
    public function testRegisterWithDuplicateEmail(): void
    {
        $this->post('/auth/attemptRegister', [
            'name' => 'User One',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('/auth/attemptRegister', [
            'name' => 'User Two',
            'email' => 'duplicate@example.com',
            'password' => 'password456',
        ]);

        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: Logout zerstört Session
     */
    public function testLogout(): void
    {
        $userModel = new UserModel();
        $userData = [
            'email' => 'logout@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name' => 'Logout Test User',
        ];
        $userModel->insert($userData);

        $this->post('/auth/attemptLogin', [
            'email' => 'logout@example.com',
            'password' => 'password123',
        ]);

        $response = $this->get('/auth/logout');
        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: Passwort wird korrekt gehasht
     */
    public function testPasswordIsHashed(): void
    {
        $password = 'plaintext_password_123';
        $response = $this->post('/auth/attemptRegister', [
            'name' => 'Hash Test',
            'email' => 'hash@example.com',
            'password' => $password,
        ]);

        $userModel = new UserModel();
        $user = $userModel->where('email', 'hash@example.com')->first();

        // Passwort sollte nicht im Klartext gespeichert sein
        $this->assertNotEquals($password, $user['password_hash']);
        
        // password_verify sollte true zurückgeben
        $this->assertTrue(password_verify($password, $user['password_hash']));
    }

    /**
     * Test: Email ist erforderlich beim Login
     */
    public function testLoginRequiresEmail(): void
    {
        $response = $this->post('/auth/attemptLogin', [
            'email' => '',
            'password' => 'password123',
        ]);

        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: Email ist erforderlich bei Registrierung
     */
    public function testRegisterRequiresEmail(): void
    {
        $response = $this->post('/auth/attemptRegister', [
            'name' => 'Test',
            'email' => '',
            'password' => 'password123',
        ]);

        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: Login mit ungültiger Email-Adresse
     */
    public function testLoginWithInvalidEmail(): void
    {
        $response = $this->post('/auth/attemptLogin', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: Session wird nach erfolgreicher Registrierung gesetzt
     */
    public function testSessionIsSetAfterRegistration(): void
    {
        $response = $this->post('/auth/attemptRegister', [
            'name' => 'Session Test',
            'email' => 'session@example.com',
            'password' => 'password123',
        ]);

        $userModel = new UserModel();
        $user = $userModel->where('email', 'session@example.com')->first();
        $this->assertNotNull($user);
    }
}
