# Test Examples - Praktische Beispiele

## Quick Start

### 1. Erstes Test ausführen

```bash
cd /Users/yanis/BFOTodo/Todo-App-Backend

# Alle Tests ausführen
php vendor/bin/phpunit

# Nur einen Test ausführen
php vendor/bin/phpunit tests/unit/Controllers/AuthControllerTest.php::AuthControllerTest::testLoginPageLoads
```

### 2. Einzelnen Test ausführen

```bash
# Login Test
php vendor/bin/phpunit tests/unit/Controllers/AuthControllerTest.php::AuthControllerTest::testLoginWithValidCredentials

# Registration Test
php vendor/bin/phpunit tests/unit/Controllers/AuthControllerTest.php::AuthControllerTest::testRegisterWithValidData
```

## Beispiel Unit Tests

### Test: Benutzer Login

```php
public function testLoginWithValidCredentials(): void
{
    // 1. Arrange - Testdaten vorbereiten
    $userModel = new UserModel();
    $userData = [
        'email' => 'test@example.com',
        'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
        'name' => 'Test User',
    ];
    $userModel->insert($userData);

    // 2. Act - Login durchführen
    $response = $this->post('/auth/attemptLogin', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    // 3. Assert - Ergebnis überprüfen
    $this->assertTrue($response->getStatusCode() === 302); // Redirect
}
```

**Ausführen:**
```bash
php vendor/bin/phpunit tests/unit/Controllers/AuthControllerTest.php::AuthControllerTest::testLoginWithValidCredentials
```

### Test: Benutzer Registrierung

```php
public function testRegisterWithValidData(): void
{
    // 1. Arrange
    $newUserData = [
        'name' => 'Neuer User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
    ];

    // 2. Act - Registrierung durchführen
    $response = $this->post('/auth/attemptRegister', $newUserData);

    // 3. Assert - Überprüfungen
    $this->assertTrue($response->getStatusCode() === 302);
    
    // Benutzer in DB existiert
    $userModel = new UserModel();
    $user = $userModel->where('email', 'newuser@example.com')->first();
    $this->assertNotNull($user);
    $this->assertEquals('Neuer User', $user['name']);
}
```

**Ausführen:**
```bash
php vendor/bin/phpunit tests/unit/Controllers/AuthControllerTest.php::AuthControllerTest::testRegisterWithValidData
```

### Test: Doppelte Email ablehnen

```php
public function testRegisterWithDuplicateEmail(): void
{
    // 1. Arrange - Erstes Konto
    $this->post('/auth/attemptRegister', [
        'name' => 'User One',
        'email' => 'duplicate@example.com',
        'password' => 'password123',
    ]);

    // 2. Act - Zweites Konto mit gleicher Email
    $response = $this->post('/auth/attemptRegister', [
        'name' => 'User Two',
        'email' => 'duplicate@example.com',
        'password' => 'password456',
    ]);

    // 3. Assert - Sollte fehlschlagen
    $this->assertTrue($response->getStatusCode() === 302);
}
```

**Ausführen:**
```bash
php vendor/bin/phpunit tests/unit/Controllers/AuthControllerTest.php::AuthControllerTest::testRegisterWithDuplicateEmail
```

## Beispiel Model Tests

### Test: Benutzer erstellen

```php
public function testUserCanBeCreated(): void
{
    $userModel = new UserModel();
    
    $data = [
        'email' => 'create@example.com',
        'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
        'name' => 'Create Test',
    ];

    $id = $userModel->insert($data);
    
    $this->assertIsNotNull($id);
    $this->assertNotEmpty($id);
}
```

### Test: Passwort wird korrekt gehasht

```php
public function testPasswordHashIsValid(): void
{
    $userModel = new UserModel();
    $password = 'mypassword123';
    
    $data = [
        'email' => 'hash@example.com',
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'name' => 'Hash Test',
    ];
    
    $userModel->insert($data);
    $user = $userModel->where('email', 'hash@example.com')->first();
    
    // Korrekt Passwort sollte matchen
    $this->assertTrue(password_verify($password, $user['password_hash']));
    
    // Falsches Passwort sollte nicht matchen
    $this->assertFalse(password_verify('wrongpassword', $user['password_hash']));
}
```

## Beispiel API Tests

### Test: Login API Response

```php
public function testGetLoginPageReturns200(): void
{
    // 1. Act - GET Request
    $response = $this->get('/auth/login');
    
    // 2. Assert - Status Code und Content
    $this->assertTrue($response->getStatusCode() === 200);
    $this->assertStringContainsString('form', (string)$response);
    $this->assertStringContainsString('email', (string)$response);
}
```

**Ausführen:**
```bash
php vendor/bin/phpunit tests/feature/AuthApiTest.php::AuthApiTest::testGetLoginPageReturns200
```

### Test: API mit mehreren Requests

```php
public function testMultipleLoginAttempts(): void
{
    // 1. Arrange
    $userModel = new UserModel();
    $userModel->insert([
        'email' => 'multi@example.com',
        'password_hash' => password_hash('correct', PASSWORD_DEFAULT),
        'name' => 'Multi Test',
    ]);

    // 2. Act - Erster Versuch (falsch)
    $response1 = $this->post('/auth/attemptLogin', [
        'email' => 'multi@example.com',
        'password' => 'wrong',
    ]);

    // 3. Act - Zweiter Versuch (korrekt)
    $response2 = $this->post('/auth/attemptLogin', [
        'email' => 'multi@example.com',
        'password' => 'correct',
    ]);

    // 4. Assert - Beide sollten redirecten
    $this->assertTrue($response1->getStatusCode() === 302);
    $this->assertTrue($response2->getStatusCode() === 302);
}
```

## Beispiel Database Migration Tests

### Test: Tabelle existiert

```php
public function testUsersTableExists(): void
{
    $db = \Config\Database::connect();
    $this->assertTrue($db->tableExists('users'));
}
```

### Test: Spalten existieren

```php
public function testUsersTableHasRequiredColumns(): void
{
    $db = \Config\Database::connect();
    $fields = $db->getFieldData('users');

    $fieldNames = array_map(function ($field) {
        return $field->name;
    }, $fields);

    // Diese Spalten sollten alle existieren
    $this->assertContains('id', $fieldNames);
    $this->assertContains('email', $fieldNames);
    $this->assertContains('password_hash', $fieldNames);
    $this->assertContains('name', $fieldNames);
    $this->assertContains('created_at', $fieldNames);
    $this->assertContains('updated_at', $fieldNames);
}
```

**Ausführen:**
```bash
php vendor/bin/phpunit tests/database/MigrationTest.php::MigrationTest::testUsersTableHasRequiredColumns
```

## Test Output Beispiele

### Erfolgreiche Tests
```bash
$ php vendor/bin/phpunit tests/unit/Controllers/AuthControllerTest.php

PHPUnit 10.5.63 by Sebastian Bergmann and contributors.

......................                                                20 / 20 (100%)

Time: 00:02.345, Memory: 8.00 MB

OK (20 tests, 40 assertions)
```

### Test mit Fehler
```bash
$ php vendor/bin/phpunit tests/unit/Controllers/AuthControllerTest.php::AuthControllerTest::testLoginWithValidCredentials

PHPUnit 10.5.63 by Sebastian Bergmann and contributors.

F                                                                     1 / 1 (100%)

Time: 00:00.512, Memory: 6.00 MB

FAILURES!
Tests: 1, Assertions: 1, Failures: 1

FAIL: AuthControllerTest::testLoginWithValidCredentials
Expected Status Code 302 but got 200
```

## Häufige Assertions in Tests

### HTTP Status Codes prüfen
```php
$this->assertTrue($response->getStatusCode() === 200);  // OK
$this->assertTrue($response->getStatusCode() === 302);  // Redirect
$this->assertTrue($response->getStatusCode() === 400);  // Bad Request
$this->assertTrue($response->getStatusCode() === 404);  // Not Found
$this->assertTrue($response->getStatusCode() === 500);  // Server Error
```

### Datenbank Assertions
```php
$this->assertNotNull($user);                           // Benutzer existiert
$this->assertEquals('test@example.com', $user['email']); // Email stimmt
$this->assertCount(5, $users);                          // 5 Benutzer
```

### String Assertions
```php
$this->assertStringContainsString('form', (string)$response); // Enthält
$this->assertStringStartsWith('Hello', 'Hello World');        // Beginnt mit
$this->assertStringEndsWith('World', 'Hello World');          // Endet mit
```

## Test Patterns

### Pattern 1: Arrange-Act-Assert
```php
public function testFeature(): void
{
    // ARRANGE - Setup
    $data = ['email' => 'test@example.com'];
    
    // ACT - Aktion
    $result = $this->post('/path', $data);
    
    // ASSERT - Überprüfung
    $this->assertTrue($result->getStatusCode() === 302);
}
```

### Pattern 2: Given-When-Then
```php
public function testFeature(): void
{
    // GIVEN - Initial State
    $user = $this->createUser('test@example.com');
    
    // WHEN - Action
    $response = $this->loginUser($user);
    
    // THEN - Verify
    $this->assertTrue($response->getStatusCode() === 302);
}
```

### Pattern 3: Setup-Exercise-Verify
```php
public function testFeature(): void
{
    // SETUP - Prepare
    $userModel = new UserModel();
    
    // EXERCISE - Execute
    $id = $userModel->insert($userData);
    
    // VERIFY - Assert
    $this->assertNotNull($id);
}
```

## Debugging Tests

### Verbose Output
```bash
php vendor/bin/phpunit -v tests/unit/Controllers/AuthControllerTest.php
```

### Mit Debug Output
```php
public function testLoginWithValidCredentials(): void
{
    // ... test code ...
    
    echo "Response Status: " . $response->getStatusCode();
    var_dump($response->getBody());
}
```

### Mit xdebug
```bash
XDEBUG_CONFIG="idekey=xdebug" php vendor/bin/phpunit tests/unit/Controllers/AuthControllerTest.php
```

## Nächste Schritte

1. **Coverage Report generieren:**
   ```bash
   php vendor/bin/phpunit --coverage-html build/logs/coverage
   ```

2. **Tests mit CI/CD integrieren**

3. **Mehr Tests hinzufügen für:**
   - Task Management Features
   - Category Management
   - Project Management
   - Recurring Tasks

4. **Performance Tests**

5. **Security Tests**
