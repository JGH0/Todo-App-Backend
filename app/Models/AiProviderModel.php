<?php

namespace App\Models;

use CodeIgniter\Model;

class AiProviderModel extends Model
{
    protected $table = 'ai_providers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id',
        'name',
        'display_name',
        'base_url',
        'is_builtin',
        'created_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = null;

    protected $validationRules = [
        'name' => 'required|max_length[100]|is_unique[ai_providers.name]',
        'display_name' => 'required|max_length[255]',
    ];

    // Get builtin providers only
    public function getBuiltinProviders()
    {
        return $this->where('is_builtin', true)
                    ->orderBy('name', 'ASC')
                    ->get()
                    ->getResultArray();
    }

    // Get custom providers only
    public function getCustomProviders()
    {
        return $this->where('is_builtin', false)
                    ->orderBy('name', 'ASC')
                    ->get()
                    ->getResultArray();
    }

    // Get provider by name
    public function getByName($name)
    {
        return $this->where('name', $name)
                    ->get()
                    ->getRowArray();
    }
}
