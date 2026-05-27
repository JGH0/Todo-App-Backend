<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\CategoryModel;

class CategoryController extends BaseController
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    const SORTABLE   = ['name', 'created_at'];
    const FILTERABLE = ['favorite'];

    /**
     * GET /api/v1/categories
     */
    public function index()
    {
        $userId  = $this->getUserId();
        $filters = $this->getFilterParams(self::FILTERABLE);
        $sorts   = $this->getSortParams(self::SORTABLE);

        $builder = $this->categoryModel->where('user_id', $userId);
        $this->applyFilters($builder, $filters);

        if (empty($sorts)) {
            $builder->orderBy('name', 'ASC');
        } else {
            $this->applySort($builder, $sorts);
        }

        return $this->paginatedResponse($builder, 'Categories retrieved successfully');
    }

    /**
     * POST /api/v1/categories
     */
    public function create()
    {
        $userId = $this->getUserId();

        if (!$this->validateWithModel($this->categoryModel)) {
            return;
        }

        $json = $this->request->getJSON(true);

        // Custom duplicate check (per user)
        $existing = $this->categoryModel
            ->where('user_id', $userId)
            ->where('name', $json['name'])
            ->first();

        if ($existing) {
            return $this->errorResponse('A category with this name already exists.', 409);
        }

        $data = [
            'id'        => $this->generateUuid(),
            'user_id'   => $userId,
            'name'      => $json['name'],
            'color'     => $json['color'],
            'favorite'  => !empty($json['favorite']),
        ];

        $this->categoryModel->insert($data);
        $category = $this->categoryModel->find($data['id']);

        $this->logActivity('category_created', 'category', $data['id'], [
            'name' => $data['name'],
        ]);

        return $this->successResponse($category, 'Category created successfully', 201);
    }

    /**
     * GET /api/v1/categories/{id}
     */
    public function show($id = null)
    {
        $userId   = $this->getUserId();
        $category = $this->categoryModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        return $this->successResponse($category, 'Category retrieved successfully');
    }

    /**
     * PUT /api/v1/categories/{id}
     */
    public function update($id = null)
    {
        $userId   = $this->getUserId();
        $category = $this->categoryModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        $json = $this->request->getJSON(true);

        // Duplicate check on rename
        if (!empty($json['name']) && strtolower($json['name']) !== strtolower($category['name'])) {
            $existing = $this->categoryModel
                ->where('user_id', $userId)
                ->where('name', $json['name'])
                ->where('id !=', $id)
                ->first();

            if ($existing) {
                return $this->errorResponse('A category with this name already exists.', 409);
            }
        }

        $allowedFields = ['name', 'color', 'favorite'];
        $updateData    = array_intersect_key($json, array_flip($allowedFields));

        if (empty($updateData)) {
            return $this->errorResponse('No valid fields to update');
        }

        // Convert boolean
        if (array_key_exists('favorite', $updateData)) {
            $updateData['favorite'] = !empty($updateData['favorite']);
        }

        $this->categoryModel->update($id, $updateData);
        $category = $this->categoryModel->find($id);

        $this->logActivity('category_updated', 'category', $id, [
            'name' => $category['name'] ?? 'Unknown',
        ]);

        return $this->successResponse($category, 'Category updated successfully');
    }

    /**
     * DELETE /api/v1/categories/{id}
     */
    public function delete($id = null)
    {
        $userId   = $this->getUserId();
        $category = $this->categoryModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        $this->categoryModel->delete($id);

        $this->logActivity('category_deleted', 'category', $id, [
            'name' => $category['name'] ?? 'Unknown',
        ]);

        return $this->successResponse(null, 'Category deleted successfully');
    }
}
