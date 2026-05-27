<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\TodoModel;
use App\Models\CategoryModel;
use App\Models\ProjectModel;
use App\Models\UserModel;

/**
 * Model Unit Tests
 *
 * Tests the Todo App Backend models directly.
 * Requires a working MySQL database with migrations applied.
 *
 * @internal
 */
final class ModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;
    protected $refresh = false;

    private static string $userId     = '';
    private static string $todoId     = '';
    private static string $categoryId = '';
    private static string $projectId  = '';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Pick the first real user from the database
        $userModel = new UserModel();
        $user = $userModel->first();

        if ($user) {
            self::$userId = $user['id'];
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (empty(self::$userId)) {
            $this->markTestSkipped('No users in database. Run migrations and register a user first.');
        }
    }

    // ========================================================================
    //  TODO MODEL
    // ========================================================================

    public function testTodoModelInsertAndFind(): void
    {
        $model = new TodoModel();

        $data = [
            'id'       => $this->uuid(),
            'user_id'  => self::$userId,
            'title'    => 'Test todo',
            'status'   => 'open',
            'due_date' => '2025-12-31',
        ];

        $this->assertNotFalse($model->insert($data), 'Todo insert should succeed');

        self::$todoId = $data['id'];

        $found = $model->find($data['id']);
        $this->assertNotNull($found, 'Todo should be findable');
        $this->assertSame('Test todo', $found['title']);
    }

    public function testTodoModelValidation(): void
    {
        $model = new TodoModel();

        $result = $model->insert([
            'id'      => $this->uuid(),
            'user_id' => self::$userId,
        ]);

        $this->assertFalse($result, 'Insert without title should fail');
        $this->assertNotEmpty($model->errors());
    }

    public function testTodoModelUpdate(): void
    {
        $model = new TodoModel();

        $updated = $model->update(self::$todoId, [
            'status' => 'completed',
        ]);

        $this->assertNotFalse($updated);

        $found = $model->find(self::$todoId);
        $this->assertNotNull($found);
        $this->assertSame('completed', $found['status']);
    }

    public function testTodoModelFindByUser(): void
    {
        $model = new TodoModel();

        $results = $model->where('user_id', self::$userId)->findAll();
        $this->assertGreaterThanOrEqual(1, count($results));
    }

    public function testTodoModelDelete(): void
    {
        $model = new TodoModel();
        $model->delete(self::$todoId);

        $found = $model->find(self::$todoId);
        $this->assertNull($found, 'Todo should be deleted');
    }

    // ========================================================================
    //  CATEGORY MODEL
    // ========================================================================

    public function testCategoryModelInsertAndFind(): void
    {
        $model = new CategoryModel();

        $data = [
            'id'      => $this->uuid(),
            'user_id' => self::$userId,
            'name'    => 'Work',
            'color'   => '#3B82F6',
        ];

        $this->assertNotFalse($model->insert($data));

        self::$categoryId = $data['id'];

        $found = $model->find($data['id']);
        $this->assertNotNull($found);
        $this->assertSame('Work', $found['name']);
        $this->assertSame('#3B82F6', $found['color']);
    }

    public function testCategoryValidationMissingColor(): void
    {
        $model = new CategoryModel();

        $result = $model->insert([
            'id'      => $this->uuid(),
            'user_id' => self::$userId,
            'name'    => 'No Color',
        ]);

        $this->assertFalse($result);
    }

    public function testCategoryValidationInvalidColor(): void
    {
        $model = new CategoryModel();

        $result = $model->insert([
            'id'      => $this->uuid(),
            'user_id' => self::$userId,
            'name'    => 'Bad Color',
            'color'   => 'not-a-hex',
        ]);

        $this->assertFalse($result);
    }

    public function testCategoryModelUpdate(): void
    {
        $model = new CategoryModel();
        $model->update(self::$categoryId, ['name' => 'Updated Work']);

        $found = $model->find(self::$categoryId);
        $this->assertSame('Updated Work', $found['name']);
    }

    public function testCategoryModelDelete(): void
    {
        $model = new CategoryModel();
        $model->delete(self::$categoryId);

        $found = $model->find(self::$categoryId);
        $this->assertNull($found);
    }

    // ========================================================================
    //  PROJECT MODEL
    // ========================================================================

    public function testProjectModelInsertAndFind(): void
    {
        $model = new ProjectModel();

        $data = [
            'id'      => $this->uuid(),
            'user_id' => self::$userId,
            'name'    => 'Test Project',
            'color'   => '#8B5CF6',
        ];

        $this->assertNotFalse($model->insert($data));

        self::$projectId = $data['id'];

        $found = $model->find($data['id']);
        $this->assertNotNull($found);
        $this->assertSame('Test Project', $found['name']);
    }

    public function testProjectModelUpdate(): void
    {
        $model = new ProjectModel();
        $model->update(self::$projectId, [
            'description' => 'A test project description',
        ]);

        $found = $model->find(self::$projectId);
        $this->assertSame('A test project description', $found['description']);
    }

    public function testProjectModelDelete(): void
    {
        $model = new ProjectModel();
        $model->delete(self::$projectId);

        $found = $model->find(self::$projectId);
        $this->assertNull($found);
    }

    // ========================================================================
    //  HELPERS
    // ========================================================================

    private function uuid(): string
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
}
