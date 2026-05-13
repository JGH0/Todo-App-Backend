<?php

namespace App\Models;

trait LoggableTrait
{
    /**
     * Log activity after insert
     */
    protected function afterInsert(array $data)
    {
        try {
            $this->logActivity('created', $data);
        } catch (\Exception $e) {
            // Silently fail to avoid breaking the main operation
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
        return $data;
    }

    /**
     * Log activity after update
     */
    protected function afterUpdate(array $data)
    {
        try {
            $this->logActivity('updated', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
        return $data;
    }

    /**
     * Log activity after delete
     */
    protected function afterDelete(array $data)
    {
        try {
            $this->logActivity('deleted', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
        return $data;
    }

    /**
     * Log activity to activity_logs table
     */
    protected function logActivity($action, $data)
    {
        $activityLogModel = new ActivityLogModel();

        $entityType = $this->getEntityType();
        $entityId = $data['id'] ?? $data[$this->primaryKey] ?? null;
        $userId = $data['user_id'] ?? null;

        // Try to get user from session if not in data
        if ($userId === null && function_exists('session')) {
            $userId = session()->get('user_id');
        }

        $logData = [
            'user_id' => $userId,
            'action' => $this->getActionName($action, $entityType),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => json_encode($this->getLogDetails($action, $data)),
            'ip_address' => $this->getClientIp(),
            'user_agent' => $this->getUserAgent(),
        ];

        $activityLogModel->logActivity($logData);
    }

    /**
     * Get entity type based on table name
     */
    protected function getEntityType(): string
    {
        $table = $this->table;
        // Remove plural 's' if present
        return rtrim($table, 's');
    }

    /**
     * Get formatted action name
     */
    protected function getActionName($action, $entityType): string
    {
        return "{$entityType}_{$action}";
    }

    /**
     * Get log details (can be overridden in models)
     */
    protected function getLogDetails($action, $data): array
    {
        $details = [
            'action' => $action,
        ];

        // Add relevant fields based on entity type
        if (isset($data['title'])) {
            $details['title'] = $data['title'];
        }
        if (isset($data['name'])) {
            $details['name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $details['email'] = $data['email'];
        }

        return $details;
    }

    /**
     * Get client IP address
     */
    protected function getClientIp(): ?string
    {
        try {
            $request = \Config\Services::request();
            return $request->getIPAddress();
        } catch (\Exception $e) {
            return 'CLI';
        }
    }

    /**
     * Get user agent
     */
    protected function getUserAgent(): ?string
    {
        try {
            $request = \Config\Services::request();
            return $request->getUserAgent()->getAgentString();
        } catch (\Exception $e) {
            return 'CLI/Script';
        }
    }
}
