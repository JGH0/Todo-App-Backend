<?php

namespace App\Models;

use CodeIgniter\Model;

class TodoCategoryModel extends Model
{
    protected $table = 'todo_categories';
    protected $primaryKey = 'todo_id'; // Composite primary key
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'todo_id',
        'category_id',
    ];

    protected $useTimestamps = false;

    // Add category to todo
    public function addCategoryToTodo($todoId, $categoryId)
    {
        return $this->insert([
            'todo_id' => $todoId,
            'category_id' => $categoryId,
        ]);
    }

    // Remove category from todo
    public function removeCategoryFromTodo($todoId, $categoryId)
    {
        return $this->where('todo_id', $todoId)
                    ->where('category_id', $categoryId)
                    ->delete();
    }

    // Get categories for a todo
    public function getCategoriesForTodo($todoId)
    {
        return $this->select('categories.*')
                    ->join('categories', 'todo_categories.category_id = categories.id')
                    ->where('todo_categories.todo_id', $todoId)
                    ->get()
                    ->getResultArray();
    }

    // Get todos for a category
    public function getTodosForCategory($categoryId)
    {
        return $this->select('todos.*')
                    ->join('todos', 'todo_categories.todo_id = todos.id')
                    ->where('todo_categories.category_id', $categoryId)
                    ->get()
                    ->getResultArray();
    }
}
