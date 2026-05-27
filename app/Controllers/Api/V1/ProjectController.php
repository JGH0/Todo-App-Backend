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

    const SORTABLE   = ['name', 'created_at'];
    const FILTERABLE = [];

    /**
     * GET /api/v1/projects
     */
    public function index()
    {
        $userId  = $this->getUserId();
        $filters = $this->getFilterParams(self::FILTERABLE);
        $sorts   = $this->getSortParams(self::SORTABLE);

        $builder = $this->projectModel->where('user_id', $userId);
        $this->applyFilters($builder, $filters);

        if (empty($sorts)) {
            $builder->orderBy('created_at', 'DESC');
        } else {
            $this->applySort($builder, $sorts);
        }

        return $this->paginatedResponse($builder, 'Projects retrieved successfully');
    }

    /**
     * POST /api/v1/projects
     */
    public function create()
    {
        $userId = $this->getUserId();

        if (!$this->validateWithModel($this->projectModel)) {
            return;
        }

        $json = $this->request->getJSON(true);

        $data = [
            'id'          => $this->generateUuid(),
            'user_id'     => $userId,
            'name'        => $json['name'],
            'description' => $json['description'] ?? null,
            'color'       => $json['color'] ?? '#8B5CF6',
        ];

        $this->projectModel->insert($data);
        $project = $this->projectModel->find($data['id']);

        $this->logActivity('project_created', 'project', $data['id'], [
            'name' => $data['name'],
        ]);

        return $this->successResponse($project, 'Project created successfully', 201);
    }

    /**
     * GET /api/v1/projects/{id}
     */
    public function show($id = null)
    {
        $userId  = $this->getUserId();
        $project = $this->projectModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        return $this->successResponse($project, 'Project retrieved successfully');
    }

    /**
     * PUT /api/v1/projects/{id}
     */
    public function update($id = null)
    {
        $userId  = $this->getUserId();
        $project = $this->projectModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        $json = $this->request->getJSON(true);

        $allowedFields = ['name', 'description', 'color'];
        $updateData    = array_intersect_key($json, array_flip($allowedFields));

        if (empty($updateData)) {
            return $this->errorResponse('No valid fields to update');
        }

        $this->projectModel->update($id, $updateData);
        $project = $this->projectModel->find($id);

        $this->logActivity('project_updated', 'project', $id, [
            'name' => $project['name'] ?? 'Unknown',
        ]);

        return $this->successResponse($project, 'Project updated successfully');
    }

    /**
     * DELETE /api/v1/projects/{id}
     */
    public function delete($id = null)
    {
        $userId  = $this->getUserId();
        $project = $this->projectModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$project) {
            return $this->errorResponse('Project not found', 404);
        }

        $this->projectModel->delete($id);

        $this->logActivity('project_deleted', 'project', $id, [
            'name' => $project['name'] ?? 'Unknown',
        ]);

        return $this->successResponse(null, 'Project deleted successfully');
    }
}
