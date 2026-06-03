<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\TodoModel;
use App\Models\TodoCategoryModel;
use App\Models\CategoryModel;

class TodoController extends BaseController
{
    protected $todoModel;
    protected $todoCategoryModel;

    public function __construct()
    {
        $this->todoModel         = new TodoModel();
        $this->todoCategoryModel = new TodoCategoryModel();
    }

    // ── Allowed sort & filter fields ───────────────────────────────────────
    const SORTABLE  = ['title', 'status', 'due_date', 'due_time', 'created_at', 'updated_at'];
    const FILTERABLE = ['status', 'project_id', 'sync_enabled', 'reminder_enabled', 'recurring_enabled'];

    /**
     * GET /api/v1/todos
     * Paginated, sortable, filterable list of todos for the authenticated user.
     *
     * Query params:
     *   page      (int)   – page number, default 1
     *   per_page  (int)   – items per page, default 50, max 200
     *   sort      (string) – e.g. "title" or "-created_at,title"
     *   status    (string) – filter by status
     *   project_id (string) – filter by project
     */
    public function index()
    {
        $userId = $this->getUserId();

        $filters = $this->getFilterParams(self::FILTERABLE);
        $sorts   = $this->getSortParams(self::SORTABLE);

        $builder = $this->todoModel
            ->select('todos.*, GROUP_CONCAT(DISTINCT categories.id SEPARATOR \',\') as category_ids, GROUP_CONCAT(DISTINCT categories.name SEPARATOR \', \') as category_names')
            ->join('todo_categories', 'todos.id = todo_categories.todo_id', 'left')
            ->join('categories', 'todo_categories.category_id = categories.id', 'left')
            ->where('todos.user_id', $userId)
            ->groupBy('todos.id');

        // Apply filters
        $this->applyFilters($builder, $filters);

        // Apply sorting (default: newest first)
        if (empty($sorts)) {
            $builder->orderBy('todos.created_at', 'DESC');
        } else {
            $this->applySort($builder, $sorts);
        }

        return $this->paginatedResponse($builder, 'Todos retrieved successfully');
    }

    /**
     * POST /api/v1/todos
     */
    public function create()
    {
        $userId = $this->getUserId();

        $__val = $this->validateWithModel($this->todoModel); if ($__val !== true) { return $__val; }

        $json = $this->request->getJSON(true);

        $data = [
            'id'                => $this->generateUuid(),
            'user_id'           => $userId,
            'title'             => $json['title'],
            'description'       => $json['description'] ?? null,
            'status'            => $json['status'] ?? 'open',
            'due_date'          => $json['due_date'] ?? null,
            'due_time'          => $json['due_time'] ?? null,
            'sync_enabled'      => !empty($json['sync_enabled']),
            'reminder_enabled'  => !empty($json['reminder_enabled']),
            'recurring_enabled' => !empty($json['recurring_enabled']),
            'project_id'        => $json['project_id'] ?? null,
        ];

        $this->todoModel->insert($data);

        // Link category if provided
        if (!empty($json['category_id'])) {
            $this->linkCategory($data['id'], $json['category_id']);
        }

        $this->logActivity('todo_created', 'todo', $data['id'], [
            'title' => $data['title'],
        ]);

        $todos = $this->todoModel->getByUserWithCategories($userId, $data['id']);

        return $this->successResponse($todos[0] ?? null, 'Todo created successfully', 201);
    }

    /**
     * GET /api/v1/todos/{id}
     */
    public function show($id = null)
    {
        $userId = $this->getUserId();
        $todos  = $this->todoModel->getByUserWithCategories($userId, $id);

        if (empty($todos)) {
            return $this->errorResponse('Todo not found', 404);
        }

        return $this->successResponse($todos[0], 'Todo retrieved successfully');
    }

    /**
     * PUT /api/v1/todos/{id}
     */
    public function update($id = null)
    {
        $userId = $this->getUserId();
        $todo   = $this->todoModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$todo) {
            return $this->errorResponse('Todo not found', 404);
        }

        $json = $this->request->getJSON(true);

        // Handle category update separately (not a column in todos table)
        $hasCategoryUpdate = array_key_exists('category_id', $json);
        if ($hasCategoryUpdate) {
            $categoryId = $json['category_id'];
            $this->todoCategoryModel->where('todo_id', $id)->delete();
            if (!empty($categoryId)) {
                $this->linkCategory($id, $categoryId);
            }
        }

        // Update todo fields
        $allowedFields = [
            'title', 'description', 'status', 'due_date', 'due_time',
            'sync_enabled', 'reminder_enabled', 'recurring_enabled', 'project_id',
        ];
        $updateData = array_intersect_key($json, array_flip($allowedFields));

        if (!empty($updateData)) {
            // Convert boolean-ish values
            foreach (['sync_enabled', 'reminder_enabled', 'recurring_enabled'] as $boolField) {
                if (array_key_exists($boolField, $updateData)) {
                    $updateData[$boolField] = !empty($updateData[$boolField]);
                }
            }

            $this->todoModel->update($id, $updateData);
        }

        $this->logActivity('todo_updated', 'todo', $id, [
            'title' => $todo['title'] ?? 'Unknown',
        ]);

        $updated = $this->todoModel->getByUserWithCategories($userId, $id);

        return $this->successResponse($updated[0] ?? null, 'Todo updated successfully');
    }

    /**
     * DELETE /api/v1/todos/{id}
     */
    public function delete($id = null)
    {
        $userId = $this->getUserId();
        $todo   = $this->todoModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$todo) {
            return $this->errorResponse('Todo not found', 404);
        }

        $this->todoModel->delete($id);

        $this->logActivity('todo_deleted', 'todo', $id, [
            'title' => $todo['title'] ?? 'Unknown',
        ]);

        return $this->successResponse(null, 'Todo deleted successfully');
    }

    // ── Category linking ───────────────────────────────────────────────────

    /**
     * POST /api/v1/todos/{id}/categories
     */
    public function addCategory($todoId = null)
    {
        $userId = $this->getUserId();
        $json   = $this->request->getJSON(true);

        $rules = ['category_id' => 'required'];
        $__val = $this->validateRequest($rules); if ($__val !== true) { return $__val; }

        $todo = $this->todoModel->where('id', $todoId)->where('user_id', $userId)->first();
        if (!$todo) {
            return $this->errorResponse('Todo not found', 404);
        }

        $existing = $this->todoCategoryModel
            ->where('todo_id', $todoId)
            ->where('category_id', $json['category_id'])
            ->first();

        if ($existing) {
            return $this->errorResponse('Category already linked to this todo', 409);
        }

        $this->todoCategoryModel->insert([
            'todo_id'     => $todoId,
            'category_id' => $json['category_id'],
        ]);

        return $this->successResponse(null, 'Category added to todo successfully', 201);
    }

    /**
     * DELETE /api/v1/todos/{id}/categories/{categoryId}
     */
    public function removeCategory($todoId = null, $categoryId = null)
    {
        $userId = $this->getUserId();

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

    /**
     * Link a category to a todo (internal helper)
     */
    private function linkCategory(string $todoId, string $categoryId): void
    {
        $userId = $this->getUserId();

        $categoryModel = new CategoryModel();
        $category      = $categoryModel->where('id', $categoryId)->where('user_id', $userId)->first();

        if (!$category) {
            return;
        }

        $existing = $this->todoCategoryModel
            ->where('todo_id', $todoId)
            ->where('category_id', $categoryId)
            ->first();

        if (!$existing) {
            $this->todoCategoryModel->insert([
                'todo_id'     => $todoId,
                'category_id' => $categoryId,
            ]);
        }
    }
}
