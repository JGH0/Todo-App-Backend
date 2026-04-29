<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\ProjectModel;

class ProjectController extends BaseController
{
    protected $projectModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
    }

    /**
     * Get all projects for the authenticated user
     * GET /api/v1/projects
     */
    public function index()
    {
        $userId = $this->getUserId();
        $projects = $this->projectModel->where('user_id', $userId)->findAll();

        return $this->successResponse($projects, 'Projects retrieved successfully');
    }

    /**
     * Create a new project
     * POST /api/v1/projects
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
            'description' => $json['description'] ?? null,
            'color' => $json['color'],
        ];

        $this->projectModel->insert($data);
        $project = $this->projectModel->find($data['id']);

        return $this->successResponse($project, 'Project created successfully', 201);
    }

    /**
     * Get a specific project
     * GET /api/v1/projects/{id}
     */
    public function show($id = null)
    {
        $userId = $this->getUserId();
        $project = $this->projectModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        return $this->successResponse($project, 'Project retrieved successfully');
    }

    /**
     * Update a project
     * PUT /api/v1/projects/{id}
     */
    public function update($id = null)
    {
        $userId = $this->getUserId();
        $project = $this->projectModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        $json = $this->request->getJSON(true);
        $allowedFields = ['name', 'description', 'color'];
        $updateData = array_intersect_key($json, array_flip($allowedFields));

        if (empty($updateData)) {
            return $this->errorResponse('No valid fields to update');
        }

        $this->projectModel->update($id, $updateData);
        $project = $this->projectModel->find($id);

        return $this->successResponse($project, 'Project updated successfully');
    }

    /**
     * Delete a project
     * DELETE /api/v1/projects/{id}
     */
    public function delete($id = null)
    {
        $userId = $this->getUserId();
        $project = $this->projectModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        $this->projectModel->delete($id);

        return $this->successResponse(null, 'Project deleted successfully');
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
