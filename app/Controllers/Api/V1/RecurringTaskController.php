<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\RecurringTaskModel;
use App\Models\RecurringTaskCategoryModel;

class RecurringTaskController extends BaseController
{
    protected $recurringTaskModel;
    protected $recurringTaskCategoryModel;

    public function __construct()
    {
        $this->recurringTaskModel = new RecurringTaskModel();
        $this->recurringTaskCategoryModel = new RecurringTaskCategoryModel();
    }

    /**
     * Get all recurring tasks for the authenticated user
     * GET /api/v1/recurring-tasks
     */
    public function index()
    {
        $userId = $this->getUserId();
        $tasks = $this->recurringTaskModel->getByUserWithCategories($userId);

        return $this->successResponse($tasks, 'Recurring tasks retrieved successfully');
    }

    /**
     * Create a new recurring task
     * POST /api/v1/recurring-tasks
     */
    public function create()
    {
        $userId = $this->getUserId();
        $json = $this->request->getJSON(true);

        $rules = [
            'title' => 'required|max_length[255]',
            'schedule' => 'required|in_list[daily,weekly,monthly,custom]',
        ];

        if (!$this->validateRequest($rules)) {
            return;
        }

        $data = [
            'id' => $this->generateUuid(),
            'user_id' => $userId,
            'title' => $json['title'],
            'description' => $json['description'] ?? null,
            'schedule' => $json['schedule'],
            'custom_days' => $json['custom_days'] ? json_encode($json['custom_days']) : json_encode([]),
            'favorite' => $json['favorite'] ?? false,
        ];

        $this->recurringTaskModel->insert($data);
        $task = $this->recurringTaskModel->getByUserWithCategories($userId, $data['id']);

        return $this->successResponse($task, 'Recurring task created successfully', 201);
    }

    /**
     * Get a specific recurring task
     * GET /api/v1/recurring-tasks/{id}
     */
    public function show($id = null)
    {
        $userId = $this->getUserId();
        $task = $this->recurringTaskModel->getByUserWithCategories($userId, $id);

        if (!$task) {
            return $this->errorResponse('Recurring task not found', 404);
        }

        return $this->successResponse($task, 'Recurring task retrieved successfully');
    }

    /**
     * Update a recurring task
     * PUT /api/v1/recurring-tasks/{id}
     */
    public function update($id = null)
    {
        $userId = $this->getUserId();
        $task = $this->recurringTaskModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$task) {
            return $this->errorResponse('Recurring task not found', 404);
        }

        $json = $this->request->getJSON(true);
        $allowedFields = ['title', 'description', 'schedule', 'custom_days', 'favorite'];
        $updateData = array_intersect_key($json, array_flip($allowedFields));

        if (isset($updateData['custom_days'])) {
            $updateData['custom_days'] = json_encode($updateData['custom_days']);
        }

        if (empty($updateData)) {
            return $this->errorResponse('No valid fields to update');
        }

        $this->recurringTaskModel->update($id, $updateData);
        $task = $this->recurringTaskModel->getByUserWithCategories($userId, $id);

        return $this->successResponse($task, 'Recurring task updated successfully');
    }

    /**
     * Delete a recurring task
     * DELETE /api/v1/recurring-tasks/{id}
     */
    public function delete($id = null)
    {
        $userId = $this->getUserId();
        $task = $this->recurringTaskModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$task) {
            return $this->errorResponse('Recurring task not found', 404);
        }

        $this->recurringTaskModel->delete($id);

        return $this->successResponse(null, 'Recurring task deleted successfully');
    }

    /**
     * Add a category to a recurring task
     * POST /api/v1/recurring-tasks/{id}/categories
     */
    public function addCategory($taskId = null)
    {
        $userId = $this->getUserId();
        $json = $this->request->getJSON(true);

        $rules = ['category_id' => 'required'];
        if (!$this->validateRequest($rules)) {
            return;
        }

        // Verify task belongs to user
        $task = $this->recurringTaskModel->where('id', $taskId)->where('user_id', $userId)->first();
        if (!$task) {
            return $this->errorResponse('Recurring task not found', 404);
        }

        // Check if link already exists
        $existing = $this->recurringTaskCategoryModel
            ->where('recurring_task_id', $taskId)
            ->where('category_id', $json['category_id'])
            ->first();

        if ($existing) {
            return $this->errorResponse('Category already linked to this task', 409);
        }

        $this->recurringTaskCategoryModel->insert([
            'recurring_task_id' => $taskId,
            'category_id' => $json['category_id'],
        ]);

        return $this->successResponse(null, 'Category added to recurring task successfully', 201);
    }

    /**
     * Remove a category from a recurring task
     * DELETE /api/v1/recurring-tasks/{id}/categories/{categoryId}
     */
    public function removeCategory($taskId = null, $categoryId = null)
    {
        $userId = $this->getUserId();

        // Verify task belongs to user
        $task = $this->recurringTaskModel->where('id', $taskId)->where('user_id', $userId)->first();
        if (!$task) {
            return $this->errorResponse('Recurring task not found', 404);
        }

        $this->recurringTaskCategoryModel
            ->where('recurring_task_id', $taskId)
            ->where('category_id', $categoryId)
            ->delete();

        return $this->successResponse(null, 'Category removed from recurring task successfully');
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
