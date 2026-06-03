# Test-Suite Dokumentation

## Übersicht

Diese Test-Suite bietet umfassende Tests für die Todo-App Backend Applikation. Sie besteht aus Unit Tests, Feature Tests und Database Tests.

## Test-Struktur

```
tests/
├── unit/
│   ├── Controllers/
│   │   └── AuthControllerTest.php      # Auth Controller Tests
│   ├── Models/
│   │   └── UserModelTest.php           # User Model Tests
│   └── HealthTest.php                  # Basis-Health Checks
├── feature/
│   └── AuthApiTest.php                 # API Integration Tests
├── database/
│   ├── MigrationTest.php               # Database Migration Tests
│   └── ExampleDatabaseTest.php         # Example Tests
├── _support/                           # Test Support Files
│   ├── Database/
│   ├── Libraries/
│   └── Models/
└── session/                            # Session Tests

```

## Tests ausführen

### Alle Tests ausführen
```bash
cd /Users/yanis/BFOTodo/Todo-App-Backend
php vendor/bin/phpunit
```

### Nur Auth Controller Tests
```bash
php vendor/bin/phpunit tests/unit/Controllers/AuthControllerTest.php
```

### Nur User Model Tests
```bash
php vendor/bin/phpunit tests/unit/Models/UserModelTest.php
```

### Nur API Tests
```bash
php vendor/bin/phpunit tests/feature/AuthApiTest.php
```

### Nur Database Migration Tests
```bash
php vendor/bin/phpunit tests/database/MigrationTest.php
```

### Mit Coverage Report
```bash
php vendor/bin/phpunit --coverage-html build/logs/coverage
```

## Test-Kategorien

### 1. Unit Tests - Auth Controller (`tests/unit/Controllers/AuthControllerTest.php`)

Testet die Core-Logik des Auth Controllers:

**Tests:**
- ✅ `testLoginPageLoads` - Login Seite wird angezeigt
- ✅ `testLoginWithValidCredentials` - Login mit korrekten Daten
- ✅ `testLoginWithInvalidCredentials` - Login mit falschen Daten
- ✅ `testRegisterWithValidData` - Registrierung mit gültigen Daten
- ✅ `testRegisterWithDuplicateEmail` - Doppelte Email wird verhindert
- ✅ `testLogout` - Logout Funktionalität
- ✅ `testPasswordIsHashed` - Passwort wird gehasht
- ✅ `testLoginRequiresEmail` - Email ist erforderlich
- ✅ `testRegisterRequiresEmail` - Email bei Registrierung erforderlich
- ✅ `testLoginWithInvalidEmail` - Ungültiges Email Format

**Beispiel Ausführung:**
```bash
php vendor/bin/phpunit tests/unit/Controllers/AuthControllerTest.php::AuthControllerTest::testLoginWithValidCredentials
```

### 2. Unit Tests - User Model (`tests/unit/Models/UserModelTest.php`)

Testet die Benutzermodell-Operationen:

**Tests:**
- ✅ `testUserCanBeCreated` - Benutzer erstellen
- ✅ `testUserCanBeFoundByEmail` - Benutzer nach Email finden
- ✅ `testDuplicateEmailIsRejected` - Doppelte Email ablehnen
- ✅ `testUserCanBeUpdated` - Benutzer aktualisieren
- ✅ `testUserCanBeDeleted` - Benutzer löschen
- ✅ `testAllUsersCanBeRetrieved` - Alle Benutzer abrufen
- ✅ `testPasswordHashIsValid` - Passwort Hash Validierung

**Beispiel Ausführung:**
```bash
php vendor/bin/phpunit tests/unit/Models/UserModelTest.php
```

### 3. Feature Tests - Auth API (`tests/feature/AuthApiTest.php`)

Testet API Endpoints und HTTP Responses:

**Tests:**
- ✅ `testGetLoginPageReturns200` - Login Seite Status Code
- ✅ `testLoginWithValidDataReturns302` - Login Redirect
- ✅ `testRegisterApiCreatesNewUser` - Registrierung erstellt Benutzer
- ✅ `testLoginWithInvalidDataReturns302` - Fehlerhafte Login Redirect
- ✅ `testLogoutApiReturns302` - Logout Redirect
- ✅ `testLoginWithMissingEmailField` - Fehlende Email Feld
- ✅ `testLoginWithMissingPasswordField` - Fehlende Password Feld
- ✅ `testRegisterWithMissingNameField` - Fehlende Name Feld
- ✅ `testLoginPageContentType` - Content-Type Header
- ✅ `testRegisterValidatesEmailFormat` - Email Format Validierung
- ✅ `testLoginPageIncludesSecurityHeaders` - Sicherheits-Header
- ✅ `testRegisterSetsUserIdInSession` - Session User ID
- ✅ `testMultipleLoginAttempts` - Mehrfache Login Versuche

**Beispiel Ausführung:**
```bash
php vendor/bin/phpunit tests/feature/AuthApiTest.php::AuthApiTest::testLoginWithValidDataReturns302
```

### 4. Database Tests - Migrations (`tests/database/MigrationTest.php`)

Testet Datenbankmigrationen und Schema:

**Tests:**
- ✅ `testUsersTableExists` - Users Tabelle existiert
- ✅ `testUsersTableHasRequiredColumns` - Erforderliche Spalten vorhanden
- ✅ `testEmailIsUnique` - Email Unique Constraint
- ✅ `testCategoriesTableExists` - Categories Tabelle
- ✅ `testProjectsTableExists` - Projects Tabelle
- ✅ `testTodosTableExists` - Todos Tabelle
- ✅ `testTodoCategoriesTableExists` - TodoCategories Tabelle
- ✅ `testTodosTableHasRequiredColumns` - Todos Spalten
- ✅ `testDatabaseConnectionWorks` - DB Verbindung
- ✅ `testTableCountIsCorrect` - Tabellenzahl
- ✅ `testUserSettingsIsJson` - Settings JSON Type
- ✅ `testTimestampsAreCorrectType` - Timestamp Spalten

**Beispiel Ausführung:**
```bash
php vendor/bin/phpunit tests/database/MigrationTest.php
```

## Test-Konventionen

### Naming Konvention
- Test-Klassen: `{Feature}Test.php` (z.B. `AuthControllerTest.php`)
- Test-Methoden: `test{Scenario}` (z.B. `testLoginWithValidCredentials`)
- Namespace: `Tests\\{Category}\\{Feature}` (z.B. `Tests\\Unit\\Controllers`)

### Struktur
```php
/**
 * Test: Beschreibung was getestet wird
 */
public function testFeatureName(): void
{
    // Arrange - Setup Daten
    $userData = ['email' => 'test@example.com', ...];
    
    // Act - Aktion ausführen
    $response = $this->post('/auth/login', $userData);
    
    // Assert - Ergebnis verifizieren
    $this->assertTrue($response->getStatusCode() === 302);
}
```

## Test-Traits

### DatabaseTestTrait
Ermöglicht Datenbankzugriff in Tests:
```php
use DatabaseTestTrait;

protected $seed = UserSeeder::class; // Optional: Daten seeden
```

### FeatureTestTrait
Ermöglicht HTTP Requests in Tests:
```php
use FeatureTestTrait;

$response = $this->get('/path');
$response = $this->post('/path', $data);
$response = $this->put('/path', $data);
$response = $this->delete('/path');
```

## Datenbank in Tests

### Automatisches Rollback
Tests verwenden automatisch Transaktionen, die nach jedem Test gerollt werden:
```php
class AuthControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    // Daten werden automatisch nach jedem Test gelöscht
}
```

### Daten Seeding
```php
class MigrationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    
    protected $seed = UserSeeder::class; // Lädt vor jedem Test
}
```

## Assertions häufig verwendet

```php
// Grundlegende Assertions
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertNull($value);
$this->assertNotNull($value);

// Vergleiche
$this->assertEquals($expected, $actual);
$this->assertNotEquals($expected, $actual);

// Collections
$this->assertCount($count, $array);
$this->assertContains($needle, $haystack);

// Strings
$this->assertStringContainsString($needle, $haystack);
$this->assertStringStartsWith($prefix, $string);

// Response
$this->assertTrue($response->getStatusCode() === 200);
```

## Fehlerbehandlung in Tests

### Datenbank Fehler
```php
try {
    // Operation die Fehler verursachen könnte
    $this->post('/auth/attemptRegister', $data);
} catch (\Exception $e) {
    $this->assertTrue(true); // Expected Error
}
```

### HTTP Status Codes
```php
$this->assertTrue($response->getStatusCode() === 302);  // Redirect
$this->assertTrue($response->getStatusCode() === 200);  // OK
$this->assertTrue($response->getStatusCode() === 400);  // Bad Request
$this->assertTrue($response->getStatusCode() === 404);  // Not Found
$this->assertTrue($response->getStatusCode() === 500);  // Server Error
```

## Best Practices

### ✅ Do's
- ✅ Tests sollten unabhängig voneinander sein
- ✅ Verwende aussagekräftige Test-Namen
- ✅ Ein Test pro Scenario/Feature
- ✅ Verwende Arrange-Act-Assert Pattern
- ✅ Test Edge Cases und Error Conditions
- ✅ Verwende Fixtures/Seeders für Testdaten

### ❌ Dont's
- ❌ Tests sollten nicht voneinander abhängig sein
- ❌ Keine Tests mit zufälligen Daten
- ❌ Keine Long-Running Tests (< 1 Sekunde pro Test)
- ❌ Keine Tests die externe APIs aufrufen
- ❌ Keine Tests die Datei-Operationen durchführen

## Continuous Integration

Tests können in CI/CD Pipelines integriert werden:

```yaml
# .github/workflows/tests.yml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Run Tests
        run: php vendor/bin/phpunit
```

## Performance

**Aktuelle Test-Zusammenfassung:**
- Gesamt Tests: ~40 Tests
- Durchschnittliche Dauer: < 5 Sekunden
- Coverage Ziel: > 80%

## Troubleshooting

### Tests schlagen fehl mit "Database not found"
```bash
# Stelle sicher dass .env konfiguriert ist
cp env.example .env

# Führe Migrationen aus
php spark migrate
```

### CSRF Token Fehler
Tests werden automatisch mit CSRF Protection gehändelt durch `FeatureTestTrait`

### Session wird nicht persistent
Sessions werden zwischen Requests in Feature Tests automatisch beibehalten

## Zukünftige Verbesserungen

- [ ] API Response Body Assertions
- [ ] Performance Benchmarks
- [ ] Integration Tests für komplexe Workflows
- [ ] E2E Tests mit Selenium
- [ ] Load Tests
- [ ] Security Tests

---

**Für weitere Fragen oder Probleme:**
Dokumentation: [CodeIgniter Testing Guide](https://codeigniter.com/user_guide/testing/)
