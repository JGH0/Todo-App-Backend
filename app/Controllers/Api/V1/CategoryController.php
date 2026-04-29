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

    /**
     * Get all categories for the authenticated user
     * GET /api/v1/categories
     */
    public function index()
    {
        $userId = $this->getUserId();
        $categories = $this->categoryModel->where('user_id', $userId)->findAll();

        return $this->successResponse($categories, 'Categories retrieved successfully');
    }

    /**
     * Create a new category
     * POST /api/v1/categories
     */
    public function create()
    {
        $userId = $this->getUserId();
        $json = $this->request->getJSON(true);

        $rules = [
            'name' => 'required|max_length[255]',
            'color' => 'required|max_length[7]',
        ];

        if (!$this->validateRequest($rules)) {
            return;
        }

        $data = [
            'id' => $this->generateUuid(),
            'user_id' => $userId,
            'name' => $json['name'],
            'color' => $json['color'],
            'favorite' => $json['favorite'] ?? false,
        ];

        $this->categoryModel->insert($data);
        $category = $this->categoryModel->find($data['id']);

        return $this->successResponse($category, 'Category created successfully', 201);
    }

    /**
     * Get a specific category
     * GET /api/v1/categories/{id}
     */
    public function show($id = null)
    {
        $userId = $this->getUserId();
        $category = $this->categoryModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        return $this->successResponse($category, 'Category retrieved successfully');
    }

    /**
     * Update a category
     * PUT /api/v1/categories/{id}
     */
    public function update($id = null)
    {
        $userId = $this->getUserId();
        $category = $this->categoryModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        $json = $this->request->getJSON(true);
        $allowedFields = ['name', 'color', 'favorite'];
        $updateData = array_intersect_key($json, array_flip($allowedFields));

        if (empty($updateData)) {
            return $this->errorResponse('No valid fields to update');
        }

        $this->categoryModel->update($id, $updateData);
        $category = $this->categoryModel->find($id);

        return $this->successResponse($category, 'Category updated successfully');
    }

    /**
     * Delete a category
     * DELETE /api/v1/categories/{id}
     */
    public function delete($id = null)
    {
        $userId = $this->getUserId();
        $category = $this->categoryModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        $this->categoryModel->delete($id);

        return $this->successResponse(null, 'Category deleted successfully');
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
