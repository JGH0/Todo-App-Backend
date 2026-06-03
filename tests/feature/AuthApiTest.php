<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * AuthApiTest - Feature Tests for the Auth API
 *
 * Tests the actual JSON REST API authentication endpoints:
 *   POST /api/v1/auth/register
 *   POST /api/v1/auth/login
 *   POST /api/v1/auth/api-key
 *
 * @internal
 */
final class AuthApiTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        $db = \Config\Database::connect();
        $db->table('users')->where('email LIKE', '%@apitest.local')->delete();
        $db->table('users')->where('email LIKE', '%@authapi-test.local')->delete();
        $db->table('users')->where('email LIKE', '%@multi-test.local')->delete();

        parent::tearDown();
    }

    private function respStatus($response): int
    {
        return $response->response()->getStatusCode();
    }

    private function jsonDecode($response): ?array
    {
        $raw = $response->getJSON();
        return $raw ? json_decode($raw, true) : null;
    }

    // -----------------------------------------------------------------------
    //  Register
    // -----------------------------------------------------------------------

    public function testRegisterCreatesNewUser(): void
    {
        $email = 'reg' . uniqid() . '@apitest.local';
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/register', [
            'name'     => 'API User',
            'email'    => $email,
            'password' => 'password123',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(201, $this->respStatus($response));
        $this->assertTrue($body['success'] ?? false);
        $this->assertSame('User registered successfully', $body['message']);
        $this->assertSame('API User', $body['data']['user']['name']);
        $this->assertSame($email, $body['data']['user']['email']);
        $this->assertArrayHasKey('api_key', $body['data']);
    }

    public function testRegisterRejectsDuplicateEmail(): void
    {
        $email = 'dupe' . uniqid() . '@apitest.local';
        $this->withBodyFormat('json')->post('/api/v1/auth/register', [
            'name' => 'First', 'email' => $email, 'password' => 'password123',
        ]);
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/register', [
            'name' => 'Second', 'email' => $email, 'password' => 'password456',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(422, $this->respStatus($response));
        $this->assertFalse($body['success'] ?? true);
    }

    public function testRegisterValidatesEmailFormat(): void
    {
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/register', [
            'name' => 'Bad Email', 'email' => 'not-an-email', 'password' => 'password123',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(422, $this->respStatus($response));
        $this->assertFalse($body['success'] ?? true);
    }

    public function testRegisterRequiresName(): void
    {
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/register', [
            'email' => 'noname@apitest.local', 'password' => 'password123',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(422, $this->respStatus($response));
        $this->assertFalse($body['success'] ?? true);
    }

    public function testRegisterRequiresMinPasswordLength(): void
    {
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/register', [
            'name' => 'Short Pwd', 'email' => 'short@apitest.local', 'password' => 'short',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(422, $this->respStatus($response));
        $this->assertFalse($body['success'] ?? true);
    }

    // -----------------------------------------------------------------------
    //  Login
    // -----------------------------------------------------------------------

    public function testLoginWithValidCredentials(): void
    {
        $email = 'login' . uniqid() . '@authapi-test.local';
        $this->withBodyFormat('json')->post('/api/v1/auth/register', [
            'name' => 'Login Test', 'email' => $email, 'password' => 'password123',
        ]);

        $response = $this->withBodyFormat('json')->post('/api/v1/auth/login', [
            'email' => $email, 'password' => 'password123',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(200, $this->respStatus($response));
        $this->assertTrue($body['success'] ?? false);
        $this->assertSame('Login successful', $body['message']);
        $this->assertSame($email, $body['data']['user']['email']);
        $this->assertSame('Login Test', $body['data']['user']['name']);
        $this->assertTrue(
            isset($body['data']['api_key']) || isset($body['data']['api_key_prefix']),
            'Response should contain either api_key or api_key_prefix'
        );
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/login', [
            'email' => 'nonexistent-' . uniqid() . '@authapi-test.local',
            'password' => 'wrongpassword',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(401, $this->respStatus($response));
        $this->assertFalse($body['success'] ?? true);
        $this->assertStringContainsString('Invalid email or password', $body['message']);
    }

    public function testLoginRequiresEmail(): void
    {
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/login', [
            'password' => 'password123',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(422, $this->respStatus($response));
        $this->assertFalse($body['success'] ?? true);
    }

    public function testLoginRequiresPassword(): void
    {
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/login', [
            'email' => 'test@authapi-test.local',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(422, $this->respStatus($response));
        $this->assertFalse($body['success'] ?? true);
    }

    public function testMultipleLoginAttempts(): void
    {
        $email = 'multi' . uniqid() . '@multi-test.local';
        $this->withBodyFormat('json')->post('/api/v1/auth/register', [
            'name' => 'Multi Attempt', 'email' => $email, 'password' => 'correctPassword1',
        ]);

        $response1 = $this->withBodyFormat('json')->post('/api/v1/auth/login', [
            'email' => $email, 'password' => 'wrongPassword',
        ]);
        $this->assertSame(401, $this->respStatus($response1), 'First attempt should fail');

        $response2 = $this->withBodyFormat('json')->post('/api/v1/auth/login', [
            'email' => $email, 'password' => 'correctPassword1',
        ]);
        $body2 = $this->jsonDecode($response2);
        $this->assertSame(200, $this->respStatus($response2), 'Second attempt should succeed');
        $this->assertTrue($body2['success'] ?? false);
    }

    // -----------------------------------------------------------------------
    //  API Key endpoint
    // -----------------------------------------------------------------------

    public function testCreateApiKeyWithValidCredentials(): void
    {
        $email = 'apikey' . uniqid() . '@authapi-test.local';
        $this->withBodyFormat('json')->post('/api/v1/auth/register', [
            'name' => 'API Key Test', 'email' => $email, 'password' => 'password123',
        ]);

        $response = $this->withBodyFormat('json')->post('/api/v1/auth/api-key', [
            'email' => $email, 'password' => 'password123', 'name' => 'My New Key',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(200, $this->respStatus($response));
        $this->assertTrue($body['success'] ?? false);
        $this->assertArrayHasKey('key', $body['data']);
        $this->assertArrayHasKey('prefix', $body['data']);
    }

    // -----------------------------------------------------------------------
    //  Response format tests
    // -----------------------------------------------------------------------

    public function testResponseIsJson(): void
    {
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/login', [
            'email' => 'nobody@authapi-test.local', 'password' => 'wrong',
        ]);
        $ct = $response->response()->getHeaderLine('Content-Type');
        $this->assertStringContainsString('application/json', $ct);
    }

    public function testResponseHasCorsHeaders(): void
    {
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/login', [
            'email' => 'nobody@authapi-test.local', 'password' => 'wrong',
        ]);
        $origin = $response->response()->getHeaderLine('Access-Control-Allow-Origin');
        $this->assertNotEmpty($origin, 'CORS header should be present');
    }

    // -----------------------------------------------------------------------
    //  JWT endpoints
    // -----------------------------------------------------------------------

    public function testJwtRegisterCreatesUserAndReturnsToken(): void
    {
        $email = 'jwtreg' . uniqid() . '@authapi-test.local';
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/jwt/register', [
            'name' => 'JWT User', 'email' => $email, 'password' => 'password123',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(201, $this->respStatus($response));
        $this->assertTrue($body['success'] ?? false);
        $this->assertArrayHasKey('token', $body['data']);
    }

    public function testJwtLoginReturnsToken(): void
    {
        $email = 'jwtlogin' . uniqid() . '@authapi-test.local';
        $this->withBodyFormat('json')->post('/api/v1/auth/jwt/register', [
            'name' => 'JWT Login', 'email' => $email, 'password' => 'password123',
        ]);

        $response = $this->withBodyFormat('json')->post('/api/v1/auth/jwt/login', [
            'email' => $email, 'password' => 'password123',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(200, $this->respStatus($response));
        $this->assertTrue($body['success'] ?? false);
        $this->assertArrayHasKey('token', $body['data']);
    }
}
