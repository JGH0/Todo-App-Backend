<?php

namespace App\Models;

use CodeIgniter\Model;

class RecurringTaskModel extends Model
{
    protected $table            = 'recurring_tasks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'id', 'user_id', 'title', 'description', 'schedule',
        'custom_days', 'favorite', 'created_at', 'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'user_id' => [
            'rules' => 'required',
            'errors' => ['required' => 'User ID is required.'],
        ],
        'title' => [
            'rules' => 'required|max_length[255]',
            'errors' => [
                'required'   => 'The recurring task title is required.',
                'max_length' => 'The title must not exceed 255 characters.',
            ],
        ],
        'schedule' => [
            'rules' => 'permit_empty|in_list[daily,weekly,monthly,custom]',
            'errors' => [
                'in_list' => 'Schedule must be one of: daily, weekly, monthly, custom.',
            ],
        ],
    ];

    // ── Queries ────────────────────────────────────────────────────────────

    public function getByUserWithCategories($userId, $taskId = null)
    {
        $builder = $this->select('
            recurring_tasks.*,
            GROUP_CONCAT(DISTINCT categories.id SEPARATOR \',\') as category_ids,
            GROUP_CONCAT(DISTINCT categories.name SEPARATOR \', \') as category_names
        ')
        ->join('recurring_task_categories', 'recurring_tasks.id = recurring_task_categories.recurring_task_id', 'left')
        ->join('categories', 'recurring_task_categories.category_id = categories.id', 'left')
        ->where('recurring_tasks.user_id', $userId)
        ->groupBy('recurring_tasks.id');

        if ($taskId) {
            $builder->where('recurring_tasks.id', $taskId);
        }

        return $builder->get()->getResultArray();
    }
}
