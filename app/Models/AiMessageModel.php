<?php

namespace App\Models;

use CodeIgniter\Model;

class AiMessageModel extends Model
{
    protected $table = 'ai_messages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id',
        'chat_id',
        'role',
        'content',
        'tokens_used',
        'created_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;

    protected $validationRules = [
        'chat_id' => 'required',
        'role' => 'required|in_list[user,assistant,system]',
        'content' => 'required',
    ];

    // Get messages by chat
    public function getByChat($chatId)
    {
        return $this->where('chat_id', $chatId)
                    ->orderBy('created_at', 'ASC')
                    ->get()
                    ->getResultArray();
    }

    // Add message to chat
    public function addMessage($chatId, $role, $content, $tokensUsed = null)
    {
        return $this->insert([
            'id' => $this->generateUuid(),
            'chat_id' => $chatId,
            'role' => $role,
            'content' => $content,
            'tokens_used' => $tokensUsed,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // Get last message from chat
    public function getLastMessage($chatId)
    {
        return $this->where('chat_id', $chatId)
                    ->orderBy('created_at', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
    }

    // Delete all messages from chat
    public function deleteByChat($chatId)
    {
        return $this->where('chat_id', $chatId)->delete();
    }

    // Get total tokens used by chat
    public function getTotalTokens($chatId)
    {
        $result = $this->selectSum('tokens_used')
                       ->where('chat_id', $chatId)
                       ->get()
                       ->getRowArray();

        return $result ? (int) $result['tokens_used'] : 0;
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
