<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\MarketplaceThemeModel;

class MarketplaceController extends BaseController
{
    protected $marketplaceThemeModel;

    public function __construct()
    {
        $this->marketplaceThemeModel = new MarketplaceThemeModel();
    }

    /**
     * Get all marketplace themes
     * GET /api/v1/marketplace/themes
     */
    public function index()
    {
        $themes = $this->marketplaceThemeModel->getPublished();

        return $this->successResponse($themes, 'Marketplace themes retrieved successfully');
    }

    /**
     * Get a specific marketplace theme
     * GET /api/v1/marketplace/themes/{id}
     */
    public function show($id = null)
    {
        $theme = $this->marketplaceThemeModel->find($id);

        if (!$theme) {
            return $this->errorResponse('Theme not found', 404);
        }

        return $this->successResponse($theme, 'Theme retrieved successfully');
    }
}
