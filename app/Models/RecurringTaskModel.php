<?php

namespace App\Models;

use CodeIgniter\Model;

class RecurringTaskModel extends Model
{
    protected $table = 'recurring_tasks';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id',
        'user_id',
        'title',
        'description',
        'schedule',
        'custom_days',
        'favorite',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required',
        'title' => 'required|max_length[255]',
        'schedule' => 'required|in_list[daily,weekly,monthly,custom]',
    ];

    // Get recurring tasks with categories
    public function getWithCategories($taskId = null)
    {
        $builder = $this->select('recurring_tasks.*, GROUP_CONCAT(categories.name) as category_names')
                        ->join('recurring_task_categories', 'recurring_tasks.id = recurring_task_categories.recurring_task_id', 'left')
                        ->join('categories', 'recurring_task_categories.category_id = categories.id', 'left')
                        ->groupBy('recurring_tasks.id');

        if ($taskId) {
            $builder->where('recurring_tasks.id', $taskId);
        }

        return $builder->get()->getResultArray();
    }

    // Get recurring tasks by user with categories
    public function getByUserWithCategories($userId)
    {
        return $this->select('recurring_tasks.*, GROUP_CONCAT(categories.name) as category_names')
                    ->join('recurring_task_categories', 'recurring_tasks.id = recurring_task_categories.recurring_task_id', 'left')
                    ->join('categories', 'recurring_task_categories.category_id = categories.id', 'left')
                    ->where('recurring_tasks.user_id', $userId)
                    ->groupBy('recurring_tasks.id')
                    ->get()
                    ->getResultArray();
    }
}
