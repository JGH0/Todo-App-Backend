<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        // Generate a UUID helper function
        $generateUuid = function() {
            return sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        };

        // Create a sample user (or get existing one)
        $existingUser = $this->db->table('users')->where('email', 'demo@example.com')->get()->getRowArray();
        if ($existingUser) {
            $userId = $existingUser['id'];
        } else {
            $userId = $generateUuid();
            $this->db->table('users')->insert([
                'id' => $userId,
                'email' => 'demo@example.com',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'name' => 'Demo User',
                'avatar_url' => null,
                'settings' => json_encode(['language' => 'en', 'default_view' => 'list']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Create sample categories (check for existing)
        $existingCategories = $this->db->table('categories')->where('user_id', $userId)->get()->getResultArray();
        $existingCategoryNames = array_column($existingCategories, 'name');

        $categories = [];
        $categoryNames = ['Work', 'Home', 'Personal'];
        $categoryColors = ['#3B82F6', '#10B981', '#F59E0B'];
        $categoryFavorites = [true, false, false];

        foreach ($categoryNames as $index => $name) {
            if (!in_array($name, $existingCategoryNames)) {
                $categories[] = [
                    'id' => $generateUuid(),
                    'user_id' => $userId,
                    'name' => $name,
                    'color' => $categoryColors[$index],
                    'favorite' => $categoryFavorites[$index],
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        if (!empty($categories)) {
            $this->db->table('categories')->insertBatch($categories);
        }

        // Get all categories for the user (existing + newly created)
        $allCategories = $this->db->table('categories')->where('user_id', $userId)->get()->getResultArray();
        $categories = [];
        foreach ($categoryNames as $name) {
            foreach ($allCategories as $cat) {
                if ($cat['name'] === $name) {
                    $categories[] = $cat;
                    break;
                }
            }
        }

        // Create sample projects (check for existing)
        $existingProjects = $this->db->table('projects')->where('user_id', $userId)->get()->getResultArray();
        $existingProjectNames = array_column($existingProjects, 'name');

        $projects = [];
        $projectData = [
            ['name' => 'Web Redesign', 'description' => 'Redesign the company website', 'color' => '#8B5CF6'],
            ['name' => 'Home Renovation', 'description' => 'Renovate the kitchen and bathroom', 'color' => '#EC4899'],
            ['name' => 'Learning', 'description' => 'Learn new technologies and skills', 'color' => '#14B8A6'],
        ];

        foreach ($projectData as $proj) {
            if (!in_array($proj['name'], $existingProjectNames)) {
                $projects[] = [
                    'id' => $generateUuid(),
                    'user_id' => $userId,
                    'name' => $proj['name'],
                    'description' => $proj['description'],
                    'color' => $proj['color'],
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        if (!empty($projects)) {
            $this->db->table('projects')->insertBatch($projects);
        }

        // Get all projects for the user
        $allProjects = $this->db->table('projects')->where('user_id', $userId)->get()->getResultArray();
        $projects = [];
        foreach ($projectData as $proj) {
            foreach ($allProjects as $p) {
                if ($p['name'] === $proj['name']) {
                    $projects[] = $p;
                    break;
                }
            }
        }

        $webRedesignId = isset($projects[0]) ? $projects[0]['id'] : null;
        $homeRenovationId = isset($projects[1]) ? $projects[1]['id'] : null;
        $learningId = isset($projects[2]) ? $projects[2]['id'] : null;

        // Create sample todos (check for existing)
        $existingTodos = $this->db->table('todos')->where('user_id', $userId)->get()->getResultArray();
        $existingTodoTitles = array_column($existingTodos, 'title');

        $todos = [];
        $todoData = [
            [
                'title' => 'Bestehende Aufgaben analysieren',
                'description' => 'Aktuellen Aufbau der Todo-App sichten und Felder abstimmen.',
                'status' => 'open',
                'due_date' => date('Y-m-d', strtotime('+7 days')),
                'due_time' => '10:30:00',
                'sync_enabled' => true,
                'reminder_enabled' => false,
                'recurring_enabled' => false,
                'project_id' => $webRedesignId,
            ],
            [
                'title' => 'Wireframes erstellen',
                'description' => 'Erste Skizzen für das neue Design machen.',
                'status' => 'in_progress',
                'due_date' => date('Y-m-d', strtotime('+14 days')),
                'sync_enabled' => true,
                'reminder_enabled' => true,
                'recurring_enabled' => false,
                'project_id' => $webRedesignId,
            ],
            [
                'title' => 'Küche planen',
                'description' => 'Neue Küche auswählen und bestellen.',
                'status' => 'open',
                'due_date' => date('Y-m-d', strtotime('+30 days')),
                'sync_enabled' => false,
                'reminder_enabled' => true,
                'recurring_enabled' => false,
                'project_id' => $homeRenovationId,
            ],
            [
                'title' => 'CodeIgniter lernen',
                'description' => 'Offizielle Dokumentation durchgehen.',
                'status' => 'completed',
                'due_date' => date('Y-m-d', strtotime('-5 days')),
                'sync_enabled' => true,
                'reminder_enabled' => false,
                'recurring_enabled' => false,
                'project_id' => $learningId,
            ],
            [
                'title' => 'Einkaufen',
                'description' => 'Milch, Brot, Eier, Gemüse',
                'status' => 'open',
                'due_date' => date('Y-m-d', strtotime('+1 day')),
                'sync_enabled' => true,
                'reminder_enabled' => true,
                'recurring_enabled' => false,
                'project_id' => null,
            ],
        ];

        foreach ($todoData as $todo) {
            if (!in_array($todo['title'], $existingTodoTitles)) {
                $todos[] = array_merge($todo, [
                    'id' => $generateUuid(),
                    'user_id' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if (!empty($todos)) {
            $this->db->table('todos')->insertBatch($todos);
        }

        // Get all todos for the user
        $allTodos = $this->db->table('todos')->where('user_id', $userId)->get()->getResultArray();

        // Link todos to categories
        $workCategoryId = $categories[0]['id'];
        $homeCategoryId = $categories[1]['id'];
        $personalCategoryId = $categories[2]['id'];

        $todoCategories = [];
        $todoCategoryMap = [
            'Bestehende Aufgaben analysieren' => $workCategoryId,
            'Wireframes erstellen' => $workCategoryId,
            'Küche planen' => $homeCategoryId,
            'CodeIgniter lernen' => $workCategoryId,
            'Einkaufen' => $personalCategoryId,
        ];

        foreach ($allTodos as $todo) {
            if (isset($todoCategoryMap[$todo['title']])) {
                // Check if this link already exists
                $existingLink = $this->db->table('todo_categories')
                    ->where('todo_id', $todo['id'])
                    ->where('category_id', $todoCategoryMap[$todo['title']])
                    ->get()
                    ->getRowArray();

                if (!$existingLink) {
                    $todoCategories[] = [
                        'todo_id' => $todo['id'],
                        'category_id' => $todoCategoryMap[$todo['title']],
                    ];
                }
            }
        }

        if (!empty($todoCategories)) {
            $this->db->table('todo_categories')->insertBatch($todoCategories);
        }

        // Create sample recurring tasks (check for existing)
        $existingRecurringTasks = $this->db->table('recurring_tasks')->where('user_id', $userId)->get()->getResultArray();
        $existingRecurringTaskTitles = array_column($existingRecurringTasks, 'title');

        $recurringTasks = [];
        $recurringTaskData = [
            [
                'title' => 'Weekly review',
                'description' => 'Plan next week\'s tasks',
                'schedule' => 'weekly',
                'custom_days' => json_encode([]),
                'favorite' => true,
            ],
            [
                'title' => 'Clean the house',
                'description' => 'Every Saturday',
                'schedule' => 'custom',
                'custom_days' => json_encode(['sat']),
                'favorite' => false,
            ],
            [
                'title' => 'Daily standup',
                'description' => 'Team meeting every morning',
                'schedule' => 'daily',
                'custom_days' => json_encode([]),
                'favorite' => true,
            ],
        ];

        foreach ($recurringTaskData as $task) {
            if (!in_array($task['title'], $existingRecurringTaskTitles)) {
                $recurringTasks[] = array_merge($task, [
                    'id' => $generateUuid(),
                    'user_id' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if (!empty($recurringTasks)) {
            $this->db->table('recurring_tasks')->insertBatch($recurringTasks);
        }

        // Get all recurring tasks for the user
        $allRecurringTasks = $this->db->table('recurring_tasks')->where('user_id', $userId)->get()->getResultArray();

        // Link recurring tasks to categories
        $recurringTaskCategories = [];
        $recurringTaskCategoryMap = [
            'Weekly review' => $workCategoryId,
            'Clean the house' => $homeCategoryId,
            'Daily standup' => $workCategoryId,
        ];

        foreach ($allRecurringTasks as $task) {
            if (isset($recurringTaskCategoryMap[$task['title']])) {
                // Check if this link already exists
                $existingLink = $this->db->table('recurring_task_categories')
                    ->where('recurring_task_id', $task['id'])
                    ->where('category_id', $recurringTaskCategoryMap[$task['title']])
                    ->get()
                    ->getRowArray();

                if (!$existingLink) {
                    $recurringTaskCategories[] = [
                        'recurring_task_id' => $task['id'],
                        'category_id' => $recurringTaskCategoryMap[$task['title']],
                    ];
                }
            }
        }

        if (!empty($recurringTaskCategories)) {
            $this->db->table('recurring_task_categories')->insertBatch($recurringTaskCategories);
        }

        // Create an API key for the demo user
        $existingApiKey = $this->db->table('api_auth_keys')
            ->where('user_id', $userId)
            ->where('name', 'Demo API Key')
            ->get()
            ->getRowArray();

        if (!$existingApiKey) {
            $apiKey = 'todo_' . bin2hex(random_bytes(32));
            $keyHash = hash('sha256', $apiKey);
            $keyPrefix = substr($apiKey, 0, 8);

            $this->db->table('api_auth_keys')->insert([
                'id' => $generateUuid(),
                'user_id' => $userId,
                'key_hash' => $keyHash,
                'key_prefix' => $keyPrefix,
                'name' => 'Demo API Key',
                'scopes' => json_encode(['read', 'write']),
                'expires_at' => null,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            echo "\n========================================\n";
            echo "DEMO API KEY CREATED:\n";
            echo "========================================\n";
            echo "API Key: {$apiKey}\n";
            echo "Prefix: {$keyPrefix}\n";
            echo "Use this key in the X-API-Key header\n";
            echo "========================================\n\n";
        }
    }
}
