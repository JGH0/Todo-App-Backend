<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table = 'activity_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id',
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'details',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;

    protected $validationRules = [
        'action' => 'required|max_length[255]',
    ];

    // Log an activity
    public function logActivity($data)
    {
        // Disable events to prevent any recursive logging
        $this->skipEvents();

        if (!isset($data['id'])) {
            $data['id'] = $this->generateUuid();
        }
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $result = $this->insert($data);

        // Re-enable events
        $this->skipEvents(false);

        return $result;
    }

    // Get logs by user
    public function getByUser($userId, $limit = 50)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get()
                    ->getResultArray();
    }

    // Get logs by entity
    public function getByEntity($entityType, $entityId, $limit = 50)
    {
        return $this->where('entity_type', $entityType)
                    ->where('entity_id', $entityId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get()
                    ->getResultArray();
    }

    // Get logs by action
    public function getByAction($action, $limit = 50)
    {
        return $this->where('action', $action)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get()
                    ->getResultArray();
    }

    private function generateUuid()
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
