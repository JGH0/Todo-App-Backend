<?php

namespace App\Models;

use CodeIgniter\Model;

class TodoModel extends Model
{
    use LoggableTrait;

    protected $table = 'todos';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id',
        'user_id',
        'title',
        'description',
        'status',
        'due_date',
        'due_time',
        'sync_enabled',
        'reminder_enabled',
        'recurring_enabled',
        'project_id',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required',
        'title' => 'required|max_length[255]',
        'status' => 'permit_empty|in_list[open,in_progress,completed,archived]',
    ];

    protected function getEntityType(): string
    {
        return 'todo';
    }

    // Get todos with categories
    public function getWithCategories($todoId = null)
    {
        $builder = $this->select('todos.*, GROUP_CONCAT(categories.name) as category_names')
                        ->join('todo_categories', 'todos.id = todo_categories.todo_id', 'left')
                        ->join('categories', 'todo_categories.category_id = categories.id', 'left')
                        ->groupBy('todos.id');

        if ($todoId) {
            $builder->where('todos.id', $todoId);
        }

        return $builder->get()->getResultArray();
    }

    // Get todos by user with categories
    public function getByUserWithCategories($userId)
    {
        return $this->select('todos.*, GROUP_CONCAT(categories.name) as category_names')
                    ->join('todo_categories', 'todos.id = todo_categories.todo_id', 'left')
                    ->join('categories', 'todo_categories.category_id = categories.id', 'left')
                    ->where('todos.user_id', $userId)
                    ->groupBy('todos.id')
                    ->get()
                    ->getResultArray();
    }
}
