<?php

namespace App\Models;

use CodeIgniter\Model;

class RecurringTaskCategoryModel extends Model
{
    protected $table = 'recurring_task_categories';
    protected $primaryKey = 'recurring_task_id'; // Composite primary key
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'recurring_task_id',
        'category_id',
    ];

    protected $useTimestamps = false;

    // Add category to recurring task
    public function addCategoryToTask($taskId, $categoryId)
    {
        return $this->insert([
            'recurring_task_id' => $taskId,
            'category_id' => $categoryId,
        ]);
    }

    // Remove category from recurring task
    public function removeCategoryFromTask($taskId, $categoryId)
    {
        return $this->where('recurring_task_id', $taskId)
                    ->where('category_id', $categoryId)
                    ->delete();
    }

    // Get categories for a recurring task
    public function getCategoriesForTask($taskId)
    {
        return $this->select('categories.*')
                    ->join('categories', 'recurring_task_categories.category_id = categories.id')
                    ->where('recurring_task_categories.recurring_task_id', $taskId)
                    ->get()
                    ->getResultArray();
    }

    // Get recurring tasks for a category
    public function getTasksForCategory($categoryId)
    {
        return $this->select('recurring_tasks.*')
                    ->join('recurring_tasks', 'recurring_task_categories.recurring_task_id = recurring_tasks.id')
                    ->where('recurring_task_categories.category_id', $categoryId)
                    ->get()
                    ->getResultArray();
    }
}
