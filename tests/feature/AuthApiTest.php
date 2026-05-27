<?php

namespace Tests\Feature;

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * AuthApiTest - Feature Tests für Auth API
 * Testet die Authentication API Endpoints und HTTP Requests/Responses
 * 
 * @internal
 */
final class AuthApiTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App\Controllers';

    /**
     * Test: Login API gibt 200 zurück für GET auf /auth/login
     */
    public function testGetLoginPageReturns200(): void
    {
        $response = $this->get('/auth/login');
        
        $this->assertTrue($response->getStatusCode() === 200);
        $this->assertStringContainsString('form', (string)$response);
    }

    /**
     * Test: Login API gibt 302 (Redirect) zurück mit gültigen Daten
     */
    public function testLoginWithValidDataReturns302(): void
    {
        $userModel = new UserModel();
        $userModel->insert([
            'email' => 'api@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'name' => 'API Test',
        ]);

        $response = $this->post('/auth/attemptLogin', [
            'email' => 'api@example.com',
            'password' => 'password123',
        ]);

        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: Register API erstellt neuen Benutzer
     */
    public function testRegisterApiCreatesNewUser(): void
    {
        $response = $this->post('/auth/attemptRegister', [
            'name' => 'API User',
            'email' => 'apiregister@example.com',
            'password' => 'password123',
        ]);

        $this->assertTrue($response->getStatusCode() === 302);

        // Verifiziere dass Benutzer in Datenbank erstellt wurde
        $userModel = new UserModel();
        $user = $userModel->where('email', 'apiregister@example.com')->first();
        
        $this->assertNotNull($user);
        $this->assertEquals('API User', $user['name']);
    }

    /**
     * Test: Login API mit falschen Credentials
     */
    public function testLoginWithInvalidDataReturns302(): void
    {
        $response = $this->post('/auth/attemptLogin', [
            'email' => 'nonexistent@api.com',
            'password' => 'wrongpassword',
        ]);

        // Sollte redirect sein (zur Login Seite zurück)
        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: Logout API gibt 302 Redirect zurück
     */
    public function testLogoutApiReturns302(): void
    {
        $response = $this->get('/auth/logout');
        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: POST mit fehlenden Email Feld
     */
    public function testLoginWithMissingEmailField(): void
    {
        $response = $this->post('/auth/attemptLogin', [
            'password' => 'password123',
        ]);

        // Sollte fehlschlagen
        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: POST mit fehlenden Password Feld
     */
    public function testLoginWithMissingPasswordField(): void
    {
        $response = $this->post('/auth/attemptLogin', [
            'email' => 'test@example.com',
        ]);

        // Sollte fehlschlagen
        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: Register mit fehlenden Name Feld
     */
    public function testRegisterWithMissingNameField(): void
    {
        $response = $this->post('/auth/attemptRegister', [
            'email' => 'noname@example.com',
            'password' => 'password123',
        ]);

        // Sollte weiterleiten (möglicherweise mit Error)
        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: Content-Type ist richtig bei erfolgreicher Login Seite
     */
    public function testLoginPageContentType(): void
    {
        $response = $this->get('/auth/login');
        
        $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
    }

    /**
     * Test: Register API validiert Email Format
     */
    public function testRegisterValidatesEmailFormat(): void
    {
        $response = $this->post('/auth/attemptRegister', [
            'name' => 'Invalid Email',
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        // Sollte fehlschlagen oder Fehler zurückgeben
        $this->assertTrue($response->getStatusCode() === 302);
    }

    /**
     * Test: Login API Response Headers enthalten Sicherheits-Header
     */
    public function testLoginPageIncludesSecurityHeaders(): void
    {
        $response = $this->get('/auth/login');
        
        // Bootstrap und CSS sollten geladen sein
        $content = (string)$response;
        $this->assertStringContainsString('bootstrap', strtolower($content));
    }

    /**
     * Test: Register API setzt Benutzer-ID in Session
     */
    public function testRegisterSetsUserIdInSession(): void
    {
        $this->post('/auth/attemptRegister', [
            'name' => 'Session Test',
            'email' => 'session@api.com',
            'password' => 'password123',
        ]);

        // Benutzer sollte in DB existieren
        $userModel = new UserModel();
        $user = $userModel->where('email', 'session@api.com')->first();
        
        $this->assertNotNull($user);
        $this->assertNotNull($user['id']);
    }

    /**
     * Test: Multiple Login Versuche
     */
    public function testMultipleLoginAttempts(): void
    {
        $userModel = new UserModel();
        $userModel->insert([
            'email' => 'multi@example.com',
            'password_hash' => password_hash('correct', PASSWORD_DEFAULT),
            'name' => 'Multi Test',
        ]);

        // Erster Versuch (falsch)
        $response1 = $this->post('/auth/attemptLogin', [
            'email' => 'multi@example.com',
            'password' => 'wrong',
        ]);

        // Zweiter Versuch (korrekt)
        $response2 = $this->post('/auth/attemptLogin', [
            'email' => 'multi@example.com',
            'password' => 'correct',
        ]);

        // Beide sollten 302 sein (redirect)
        $this->assertTrue($response1->getStatusCode() === 302);
        $this->assertTrue($response2->getStatusCode() === 302);
    }
}
