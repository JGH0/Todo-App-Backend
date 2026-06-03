<?php

namespace App\Models;

use CodeIgniter\Model;

class TodoModel extends Model
{
    protected $table            = 'todos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'id', 'user_id', 'title', 'description', 'status',
        'due_date', 'due_time', 'sync_enabled', 'reminder_enabled',
        'recurring_enabled', 'project_id', 'created_at', 'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        // user_id is set by the controller from auth context, not the request body
        'title' => [
            'rules' => 'required|max_length[255]',
            'errors' => [
                'required'   => 'The todo title is required.',
                'max_length' => 'The title must not exceed 255 characters.',
            ],
        ],
        'status' => [
            'rules' => 'permit_empty|in_list[open,in_progress,completed,archived]',
            'errors' => [
                'in_list' => 'Status must be one of: open, in_progress, completed, archived.',
            ],
        ],
        'due_date' => [
            'rules' => 'permit_empty|valid_date[Y-m-d]',
            'errors' => ['valid_date' => 'Due date must be in YYYY-MM-DD format.'],
        ],
        'due_time' => [
            'rules' => 'permit_empty|valid_date[H:i:s]',
            'errors' => ['valid_date' => 'Due time must be in HH:MM format.'],
        ],
    ];

    // ── Queries ────────────────────────────────────────────────────────────

    public function getByUserWithCategories($userId, $todoId = null)
    {
        $builder = $this->select('
            todos.*,
            GROUP_CONCAT(DISTINCT categories.id SEPARATOR \',\') as category_ids,
            GROUP_CONCAT(DISTINCT categories.name SEPARATOR \', \') as category_names
        ')
        ->join('todo_categories', 'todos.id = todo_categories.todo_id', 'left')
        ->join('categories', 'todo_categories.category_id = categories.id', 'left')
        ->where('todos.user_id', $userId)
        ->groupBy('todos.id');

        if ($todoId) {
            $builder->where('todos.id', $todoId);
        }

        return $builder->get()->getResultArray();
    }
}
