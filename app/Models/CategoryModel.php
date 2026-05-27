<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'id', 'user_id', 'name', 'color', 'favorite', 'created_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $validationRules = [
        'user_id' => [
            'rules' => 'required',
            'errors' => ['required' => 'User ID is required.'],
        ],
        'name' => [
            'rules' => 'required|max_length[255]',
            'errors' => [
                'required'   => 'The category name is required.',
                'max_length' => 'The category name must not exceed 255 characters.',
            ],
        ],
        'color' => [
            'rules' => 'required|max_length[7]|regex_match[/^#[0-9a-fA-F]{6}$/]',
            'errors' => [
                'required'    => 'A color value is required.',
                'max_length'  => 'Color must be a hex code (e.g. #3B82F6).',
                'regex_match' => 'Color must be a valid hex code (e.g. #3B82F6).',
            ],
        ],
    ];
}
