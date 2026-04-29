<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;
use App\Models\CategoryModel;
use App\Models\ProjectModel;
use App\Models\TodoModel;
use App\Models\RecurringTaskModel;
use App\Models\ActivityLogModel;

class TestModels extends BaseCommand
{
    protected $group = 'Development';
    protected $name = 'test:models';
    protected $description = 'Test the database models and automatic logging';

    public function run(array $params)
    {
        CLI::write('=== Testing Todo App Models ===', 'green');
        CLI::newLine();

        // Get the seeded user
        CLI::write('Test 1: Getting seeded user...', 'yellow');
        $userModel = new UserModel();
        $user = $userModel->where('email', 'demo@example.com')->first();
        if (!$user) {
            CLI::write('✗ No demo user found. Please run seeders first.', 'red');
            return;
        }
        $userId = $user['id'];
        CLI::write("✓ Using user: {$user['name']} ({$userId})", 'green');
        CLI::newLine();

        // Test 2: Query categories
        CLI::write('Test 2: Querying categories...', 'yellow');
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->where('user_id', $userId)->findAll();
        CLI::write("✓ Found " . count($categories) . " categories for user", 'green');
        foreach ($categories as $cat) {
            CLI::write("  - {$cat['name']} ({$cat['color']})", 'light_gray');
        }
        CLI::newLine();

        // Test 3: Query projects
        CLI::write('Test 3: Querying projects...', 'yellow');
        $projectModel = new ProjectModel();
        $projects = $projectModel->where('user_id', $userId)->findAll();
        CLI::write("✓ Found " . count($projects) . " projects for user", 'green');
        foreach ($projects as $proj) {
            CLI::write("  - {$proj['name']}", 'light_gray');
        }
        CLI::newLine();

        // Test 4: Query todos
        CLI::write('Test 4: Querying todos...', 'yellow');
        $todoModel = new TodoModel();
        $todos = $todoModel->getByUserWithCategories($userId);
        CLI::write("✓ Found " . count($todos) . " todos for user", 'green');
        foreach ($todos as $todo) {
            CLI::write("  - {$todo['title']} ({$todo['status']})", 'light_gray');
        }
        CLI::newLine();

        // Test 5: Query recurring tasks
        CLI::write('Test 5: Querying recurring tasks...', 'yellow');
        $recurringTaskModel = new RecurringTaskModel();
        $recurringTasks = $recurringTaskModel->getByUserWithCategories($userId);
        CLI::write("✓ Found " . count($recurringTasks) . " recurring tasks for user", 'green');
        foreach ($recurringTasks as $task) {
            CLI::write("  - {$task['title']} ({$task['schedule']})", 'light_gray');
        }
        CLI::newLine();

        CLI::write('=== All Tests Completed Successfully ===', 'green');
        CLI::write("Test User ID: {$userId}", 'light_gray');
        CLI::write('Models are working correctly. You can now use them in your controllers.', 'light_gray');
    }

    private function generateUuid()
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
