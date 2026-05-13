<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\UserThemeModel;

class UserThemeController extends BaseController
{
    protected $userThemeModel;

    public function __construct()
    {
        $this->userThemeModel = new UserThemeModel();
    }

    /**
     * Get all themes for the authenticated user
     * GET /api/v1/user/themes
     */
    public function index()
    {
        $userId = $this->getUserId();
        $themes = $this->userThemeModel->getByUser($userId);

        return $this->successResponse($themes, 'User themes retrieved successfully');
    }

    /**
     * Create a new user theme
     * POST /api/v1/user/themes
     */
    public function create()
    {
        $userId = $this->getUserId();
        $json = $this->request->getJSON(true);

        $rules = [
            'theme_id' => 'required',
        ];

        if (!$this->validateRequest($rules)) {
            return;
        }

        $data = [
            'id' => $this->generateUuid(),
            'user_id' => $userId,
            'theme_id' => $json['theme_id'],
            'is_active' => $json['is_active'] ?? false,
            'custom_settings' => $json['custom_settings'] ? json_encode($json['custom_settings']) : null,
        ];

        $this->userThemeModel->insert($data);
        $theme = $this->userThemeModel->find($data['id']);

        return $this->successResponse($theme, 'User theme created successfully', 201);
    }

    /**
     * Update a user theme
     * PUT /api/v1/user/themes/{id}
     */
    public function update($id = null)
    {
        $userId = $this->getUserId();
        $theme = $this->userThemeModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$theme) {
            return $this->errorResponse('User theme not found', 404);
        }

        $json = $this->request->getJSON(true);
        $allowedFields = ['is_active', 'custom_settings'];
        $updateData = array_intersect_key($json, array_flip($allowedFields));

        if (isset($updateData['custom_settings'])) {
            $updateData['custom_settings'] = json_encode($updateData['custom_settings']);
        }

        if (empty($updateData)) {
            return $this->errorResponse('No valid fields to update');
        }

        $this->userThemeModel->update($id, $updateData);
        $theme = $this->userThemeModel->find($id);

        return $this->successResponse($theme, 'User theme updated successfully');
    }

    /**
     * Delete a user theme
     * DELETE /api/v1/user/themes/{id}
     */
    public function delete($id = null)
    {
        $userId = $this->getUserId();
        $theme = $this->userThemeModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$theme) {
            return $this->errorResponse('User theme not found', 404);
        }

        $this->userThemeModel->delete($id);

        return $this->successResponse(null, 'User theme deleted successfully');
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
