<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\TodoModel;
use App\Models\TodoCategoryModel;

class TodoController extends BaseController
{
    protected $todoModel;
    protected $todoCategoryModel;

    public function __construct()
    {
        $this->todoModel = new TodoModel();
        $this->todoCategoryModel = new TodoCategoryModel();
    }

    /**
     * Get all todos for the authenticated user
     * GET /api/v1/todos
     */
    public function index()
    {
        $userId = $this->getUserId();
        $todos = $this->todoModel->getByUserWithCategories($userId);

        return $this->successResponse($todos, 'Todos retrieved successfully');
    }

    /**
     * Create a new todo
     * POST /api/v1/todos
     */
    public function create()
    {
        $userId = $this->getUserId();
        $json = $this->request->getJSON(true);

        $rules = [
            'title' => 'required|max_length[255]',
            'status' => 'permit_empty|in_list[open,in_progress,completed,archived]',
        ];

        if (!$this->validateRequest($rules)) {
            return;
        }

        $data = [
            'id' => $this->generateUuid(),
            'user_id' => $userId,
            'title' => $json['title'],
            'description' => $json['description'] ?? null,
            'status' => $json['status'] ?? 'open',
            'due_date' => $json['due_date'] ?? null,
            'due_time' => $json['due_time'] ?? null,
            'sync_enabled' => $json['sync_enabled'] ?? true,
            'reminder_enabled' => $json['reminder_enabled'] ?? false,
            'recurring_enabled' => $json['recurring_enabled'] ?? false,
            'project_id' => $json['project_id'] ?? null,
        ];

        $this->todoModel->insert($data);
        $todo = $this->todoModel->getByUserWithCategories($userId, $data['id']);

        return $this->successResponse($todo, 'Todo created successfully', 201);
    }

    /**
     * Get a specific todo
     * GET /api/v1/todos/{id}
     */
    public function show($id = null)
    {
        $userId = $this->getUserId();
        $todo = $this->todoModel->getByUserWithCategories($userId, $id);

        if (!$todo) {
            return $this->errorResponse('Todo not found', 404);
        }

        return $this->successResponse($todo, 'Todo retrieved successfully');
    }

    /**
     * Update a todo
     * PUT /api/v1/todos/{id}
     */
    public function update($id = null)
    {
        $userId = $this->getUserId();
        $todo = $this->todoModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$todo) {
            return $this->errorResponse('Todo not found', 404);
        }

        $json = $this->request->getJSON(true);
        $allowedFields = ['title', 'description', 'status', 'due_date', 'due_time', 'sync_enabled', 'reminder_enabled', 'recurring_enabled', 'project_id'];
        $updateData = array_intersect_key($json, array_flip($allowedFields));

        if (empty($updateData)) {
            return $this->errorResponse('No valid fields to update');
        }

        $this->todoModel->update($id, $updateData);
        $todo = $this->todoModel->getByUserWithCategories($userId, $id);

        return $this->successResponse($todo, 'Todo updated successfully');
    }

    /**
     * Delete a todo
     * DELETE /api/v1/todos/{id}
     */
    public function delete($id = null)
    {
        $userId = $this->getUserId();
        $todo = $this->todoModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$todo) {
            return $this->errorResponse('Todo not found', 404);
        }

        $this->todoModel->delete($id);

        return $this->successResponse(null, 'Todo deleted successfully');
    }

    /**
     * Add a category to a todo
     * POST /api/v1/todos/{id}/categories
     */
    public function addCategory($todoId = null)
    {
        $userId = $this->getUserId();
        $json = $this->request->getJSON(true);

        $rules = ['category_id' => 'required'];
        if (!$this->validateRequest($rules)) {
            return;
        }

        // Verify todo belongs to user
        $todo = $this->todoModel->where('id', $todoId)->where('user_id', $userId)->first();
        if (!$todo) {
            return $this->errorResponse('Todo not found', 404);
        }

        // Check if link already exists
        $existing = $this->todoCategoryModel
            ->where('todo_id', $todoId)
            ->where('category_id', $json['category_id'])
            ->first();

        if ($existing) {
            return $this->errorResponse('Category already linked to this todo', 409);
        }

        $this->todoCategoryModel->insert([
            'todo_id' => $todoId,
            'category_id' => $json['category_id'],
        ]);

        return $this->successResponse(null, 'Category added to todo successfully', 201);
    }

    /**
     * Remove a category from a todo
     * DELETE /api/v1/todos/{id}/categories/{categoryId}
     */
    public function removeCategory($todoId = null, $categoryId = null)
    {
        $userId = $this->getUserId();

        // Verify todo belongs to user
        $todo = $this->todoModel->where('id', $todoId)->where('user_id', $userId)->first();
        if (!$todo) {
            return $this->errorResponse('Todo not found', 404);
        }

        $this->todoCategoryModel
            ->where('todo_id', $todoId)
            ->where('category_id', $categoryId)
            ->delete();

        return $this->successResponse(null, 'Category removed from todo successfully');
    }

    private function generateUuid(): string
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
