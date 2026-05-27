<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\RecurringTaskModel;
use App\Models\RecurringTaskCategoryModel;
use App\Models\CategoryModel;

class RecurringTaskController extends BaseController
{
    protected $recurringTaskModel;
    protected $recurringTaskCategoryModel;

    public function __construct()
    {
        $this->recurringTaskModel         = new RecurringTaskModel();
        $this->recurringTaskCategoryModel = new RecurringTaskCategoryModel();
    }

    const SORTABLE   = ['title', 'schedule', 'created_at'];
    const FILTERABLE = ['schedule', 'favorite'];

    /**
     * GET /api/v1/recurring-tasks
     */
    public function index()
    {
        $userId  = $this->getUserId();
        $filters = $this->getFilterParams(self::FILTERABLE);
        $sorts   = $this->getSortParams(self::SORTABLE);

        $builder = $this->recurringTaskModel
            ->select('recurring_tasks.*, GROUP_CONCAT(DISTINCT categories.id SEPARATOR \',\') as category_ids, GROUP_CONCAT(DISTINCT categories.name SEPARATOR \', \') as category_names')
            ->join('recurring_task_categories', 'recurring_tasks.id = recurring_task_categories.recurring_task_id', 'left')
            ->join('categories', 'recurring_task_categories.category_id = categories.id', 'left')
            ->where('recurring_tasks.user_id', $userId)
            ->groupBy('recurring_tasks.id');

        $this->applyFilters($builder, $filters);

        if (empty($sorts)) {
            $builder->orderBy('recurring_tasks.created_at', 'DESC');
        } else {
            $this->applySort($builder, $sorts);
        }

        return $this->paginatedResponse($builder, 'Recurring tasks retrieved successfully');
    }

    /**
     * POST /api/v1/recurring-tasks
     */
    public function create()
    {
        $userId = $this->getUserId();

        if (!$this->validateWithModel($this->recurringTaskModel)) {
            return;
        }

        $json = $this->request->getJSON(true);

        $data = [
            'id'          => $this->generateUuid(),
            'user_id'     => $userId,
            'title'       => $json['title'],
            'description' => $json['description'] ?? null,
            'schedule'    => $json['schedule'] ?? 'weekly',
            'custom_days' => isset($json['custom_days']) ? json_encode($json['custom_days']) : '[]',
            'favorite'    => !empty($json['favorite']),
        ];

        $this->recurringTaskModel->insert($data);

        // Link category if provided
        if (!empty($json['category_id'])) {
            $this->linkCategory($data['id'], $json['category_id']);
        }

        $this->logActivity('recurring_task_created', 'recurring_task', $data['id'], [
            'title' => $data['title'],
        ]);

        $task = $this->recurringTaskModel->getByUserWithCategories($userId, $data['id']);

        return $this->successResponse($task[0] ?? null, 'Recurring task created successfully', 201);
    }

    /**
     * GET /api/v1/recurring-tasks/{id}
     */
    public function show($id = null)
    {
        $userId = $this->getUserId();
        $tasks  = $this->recurringTaskModel->getByUserWithCategories($userId, $id);

        if (empty($tasks)) {
            return $this->errorResponse('Recurring task not found', 404);
        }

        return $this->successResponse($tasks[0], 'Recurring task retrieved successfully');
    }

    /**
     * PUT /api/v1/recurring-tasks/{id}
     */
    public function update($id = null)
    {
        $userId = $this->getUserId();
        $task   = $this->recurringTaskModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$task) {
            return $this->errorResponse('Recurring task not found', 404);
        }

        $json = $this->request->getJSON(true);

        // Handle category update
        if (array_key_exists('category_id', $json)) {
            $this->recurringTaskCategoryModel->where('recurring_task_id', $id)->delete();
            if (!empty($json['category_id'])) {
                $this->linkCategory($id, $json['category_id']);
            }
        }

        $allowedFields = ['title', 'description', 'schedule', 'custom_days', 'favorite'];
        $updateData    = array_intersect_key($json, array_flip($allowedFields));

        // Convert custom_days array to JSON string
        if (isset($updateData['custom_days']) && is_array($updateData['custom_days'])) {
            $updateData['custom_days'] = json_encode($updateData['custom_days']);
        }
        if (array_key_exists('favorite', $updateData)) {
            $updateData['favorite'] = !empty($updateData['favorite']);
        }

        if (!empty($updateData)) {
            $this->recurringTaskModel->update($id, $updateData);
        }

        $this->logActivity('recurring_task_updated', 'recurring_task', $id, [
            'title' => $task['title'] ?? 'Unknown',
        ]);

        $updated = $this->recurringTaskModel->getByUserWithCategories($userId, $id);

        return $this->successResponse($updated[0] ?? null, 'Recurring task updated successfully');
    }

    /**
     * DELETE /api/v1/recurring-tasks/{id}
     */
    public function delete($id = null)
    {
        $userId = $this->getUserId();
        $task   = $this->recurringTaskModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$task) {
            return $this->errorResponse('Recurring task not found', 404);
        }

        $this->recurringTaskModel->delete($id);

        $this->logActivity('recurring_task_deleted', 'recurring_task', $id, [
            'title' => $task['title'] ?? 'Unknown',
        ]);

        return $this->successResponse(null, 'Recurring task deleted successfully');
    }

    /**
     * POST /api/v1/recurring-tasks/{id}/categories
     */
    public function addCategory($taskId = null)
    {
        $userId = $this->getUserId();
        $json   = $this->request->getJSON(true);

        $rules = ['category_id' => 'required'];
        if (!$this->validateRequest($rules)) {
            return;
        }

        $task = $this->recurringTaskModel->where('id', $taskId)->where('user_id', $userId)->first();
        if (!$task) {
            return $this->errorResponse('Recurring task not found', 404);
        }

        $existing = $this->recurringTaskCategoryModel
            ->where('recurring_task_id', $taskId)
            ->where('category_id', $json['category_id'])
            ->first();

        if ($existing) {
            return $this->errorResponse('Category already linked to this task', 409);
        }

        $this->recurringTaskCategoryModel->insert([
            'recurring_task_id' => $taskId,
            'category_id'       => $json['category_id'],
        ]);

        return $this->successResponse(null, 'Category added to recurring task successfully', 201);
    }

    /**
     * DELETE /api/v1/recurring-tasks/{id}/categories/{categoryId}
     */
    public function removeCategory($taskId = null, $categoryId = null)
    {
        $userId = $this->getUserId();

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

    /**
     * Link a category (internal helper)
     */
    private function linkCategory(string $taskId, string $categoryId): void
    {
        $userId        = $this->getUserId();
        $categoryModel = new CategoryModel();
        $category      = $categoryModel->where('id', $categoryId)->where('user_id', $userId)->first();

        if (!$category) return;

        $existing = $this->recurringTaskCategoryModel
            ->where('recurring_task_id', $taskId)
            ->where('category_id', $categoryId)
            ->first();

        if (!$existing) {
            $this->recurringTaskCategoryModel->insert([
                'recurring_task_id' => $taskId,
                'category_id'       => $categoryId,
            ]);
        }
    }
}
