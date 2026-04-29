<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketplaceThemeModel extends Model
{
    protected $table = 'marketplace_themes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id',
        'name',
        'display_name',
        'description',
        'author',
        'version',
        'thumbnail_url',
        'download_url',
        'price',
        'is_published',
        'metadata',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|max_length[255]|is_unique[marketplace_themes.name]',
        'display_name' => 'required|max_length[255]',
        'download_url' => 'required',
    ];

    // Get published themes only
    public function getPublished()
    {
        return $this->where('is_published', true)
                    ->orderBy('created_at', 'DESC')
                    ->get()
                    ->getResultArray();
    }

    // Get free themes
    public function getFreeThemes()
    {
        return $this->where('price', 0)
                    ->where('is_published', true)
                    ->orderBy('created_at', 'DESC')
                    ->get()
                    ->getResultArray();
    }

    // Get paid themes
    public function getPaidThemes()
    {
        return $this->where('price >', 0)
                    ->where('is_published', true)
                    ->orderBy('price', 'ASC')
                    ->get()
                    ->getResultArray();
    }
}
