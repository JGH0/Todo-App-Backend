<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\ActivityLogModel;

class ActivityLogController extends BaseController
{
    protected $activityLogModel;

    public function __construct()
    {
        $this->activityLogModel = new ActivityLogModel();
    }

    /**
     * Get activity logs for the authenticated user
     * GET /api/v1/activity-logs
     */
    public function index()
    {
        $userId = $this->getUserId();
        $limit = (int)($this->request->getVar('limit') ?? 50);
        $logs = $this->activityLogModel->getByUser($userId, $limit);

        return $this->successResponse($logs, 'Activity logs retrieved successfully');
    }

    /**
     * Get a specific activity log
     * GET /api/v1/activity-logs/{id}
     */
    public function show($id = null)
    {
        $userId = $this->getUserId();
        $log = $this->activityLogModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$log) {
            return $this->errorResponse('Activity log not found', 404);
        }

        return $this->successResponse($log, 'Activity log retrieved successfully');
    }
}
