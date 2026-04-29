<?php

namespace App\Models;

use CodeIgniter\Model;

class AiChatModel extends Model
{
    protected $table = 'ai_chats';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id',
        'user_id',
        'title',
        'provider_id',
        'model_used',
        'system_prompt',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required',
    ];

    // Get chats by user
    public function getByUser($userId, $limit = 50)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('updated_at', 'DESC')
                    ->limit($limit)
                    ->get()
                    ->getResultArray();
    }

    // Get chat with message count
    public function getWithMessageCount($chatId)
    {
        $chat = $this->find($chatId);
        if ($chat) {
            $messageModel = new AiMessageModel();
            $chat['message_count'] = $messageModel->where('chat_id', $chatId)->countAllResults();
        }
        return $chat;
    }

    // Get all chats by user with message counts
    public function getByUserWithMessageCounts($userId)
    {
        $chats = $this->getByUser($userId);
        $messageModel = new AiMessageModel();

        foreach ($chats as &$chat) {
            $chat['message_count'] = $messageModel->where('chat_id', $chat['id'])->countAllResults();
        }

        return $chats;
    }

    // Get chat with provider info
    public function getWithProvider($chatId)
    {
        return $this->select('ai_chats.*, ai_providers.name as provider_name, ai_providers.display_name')
                    ->join('ai_providers', 'ai_chats.provider_id = ai_providers.id', 'left')
                    ->where('ai_chats.id', $chatId)
                    ->get()
                    ->getRowArray();
    }

    // Update chat title
    public function updateTitle($chatId, $title)
    {
        return $this->update($chatId, ['title' => $title]);
    }

    // Create new chat
    public function createChat($userId, $data = [])
    {
        $data['id'] = $this->generateUuid();
        $data['user_id'] = $userId;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->insert($data);
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
