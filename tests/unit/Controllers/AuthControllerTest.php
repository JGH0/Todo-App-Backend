<?php

namespace Tests\Unit\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * AuthControllerTest - Feature tests for the Auth API controller
 *
 * Tests the JSON REST API endpoints through the full HTTP stack.
 *
 * @internal
 */
final class AuthControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        $db = \Config\Database::connect();
        $db->table('users')->where('email LIKE', '%@authctrl-test.local')->delete();
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

    public function testRegisterCreatesUserAndReturnsApiKey(): void
    {
        $email = 'apikey' . uniqid() . '@authctrl-test.local';
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/register', [
            'name' => 'Controller Test', 'email' => $email, 'password' => 'password123',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(201, $this->respStatus($response));
        $this->assertTrue($body['success']);
        $this->assertSame('Controller Test', $body['data']['user']['name']);
        $this->assertSame($email, $body['data']['user']['email']);
        $this->assertArrayNotHasKey('password_hash', $body['data']['user']);
    }

    public function testRegisterWithWeakPasswordFails(): void
    {
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/register', [
            'name' => 'Weak Pwd', 'email' => 'weak@authctrl-test.local', 'password' => '1234567',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(422, $this->respStatus($response));
        $this->assertFalse($body['success']);
    }

    public function testLoginReturnsExistingApiKey(): void
    {
        $email = 'existingkey' . uniqid() . '@authctrl-test.local';
        $regResp = $this->withBodyFormat('json')->post('/api/v1/auth/register', [
            'name' => 'Existing Key', 'email' => $email, 'password' => 'password123',
        ]);
        $regBody = $this->jsonDecode($regResp);
        $this->assertNotNull($regBody, 'Registration should succeed');
        $originalKeyPrefix = $regBody['data']['key_prefix'];

        $loginResp = $this->withBodyFormat('json')->post('/api/v1/auth/login', [
            'email' => $email, 'password' => 'password123',
        ]);
        $loginBody = $this->jsonDecode($loginResp);

        $this->assertSame(200, $this->respStatus($loginResp));
        $this->assertTrue($loginBody['success']);
        $this->assertSame($originalKeyPrefix, $loginBody['data']['api_key_prefix']);
    }

    public function testLoginWithInvalidEmailFormat(): void
    {
        $response = $this->withBodyFormat('json')->post('/api/v1/auth/login', [
            'email' => 'not-an-email', 'password' => 'password123',
        ]);
        $body = $this->jsonDecode($response);

        $this->assertSame(422, $this->respStatus($response));
        $this->assertFalse($body['success']);
    }
}
