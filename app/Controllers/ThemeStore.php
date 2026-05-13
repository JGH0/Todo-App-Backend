<?php

namespace App\Controllers;

use App\Models\MarketplaceThemeModel;
use CodeIgniter\HTTP\Response;

class ThemeStore extends BaseController
{
    public function index(): string
    {
        $model  = new MarketplaceThemeModel();
        $themes = $model->where('is_published', 1)->findAll();

        foreach ($themes as &$theme) {
            $meta = json_decode($theme['metadata'] ?? '{}', true);
            $theme['colors'] = $meta['colors'] ?? [];
            $theme['tags']   = $meta['tags']   ?? [];
            $theme['vars']   = $meta['vars']   ?? [];
        }

        return view('theme_store', [
            'themes'  => $themes,
            'flash_success' => session()->getFlashdata('success'),
            'flash_error'   => session()->getFlashdata('error'),
        ]);
    }

    public function upload(): Response
    {
        $file        = $this->request->getFile('theme_css');
        $displayName = trim($this->request->getPost('display_name') ?? '');
        $description = trim($this->request->getPost('description') ?? '');

        if ($displayName === '') {
            return redirect()->to('/themes')->with('error', 'Display name is required.');
        }

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to('/themes')->with('error', 'Please upload a valid CSS file.');
        }

        if (strtolower($file->getExtension()) !== 'css') {
            return redirect()->to('/themes')->with('error', 'Only .css files are allowed.');
        }

        $slug     = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $displayName));
        $slug     = trim($slug, '-');
        $filename = $slug . '-' . substr(bin2hex(random_bytes(3)), 0, 6) . '.css';

        $file->move(FCPATH . 'themes', $filename, true);

        $model = new MarketplaceThemeModel();
        $model->insert([
            'id'           => $this->uuid4(),
            'name'         => $slug,
            'display_name' => $displayName,
            'description'  => $description ?: 'Custom community theme.',
            'author'       => 'Community',
            'version'      => '1.0.0',
            'thumbnail_url' => null,
            'download_url' => '/themes/' . $filename,
            'price'        => 0,
            'is_published' => true,
            'metadata'     => json_encode(['tags' => ['custom', 'community'], 'colors' => []]),
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/themes')->with('success', '"' . esc($displayName) . '" uploaded successfully!');
    }

    public function preview(string $id): Response
    {
        $model = new MarketplaceThemeModel();
        $theme = $model->find($id);

        if (! $theme) {
            return $this->response->setStatusCode(404)->setBody('<p style="font-family:sans-serif;padding:2rem">Theme not found.</p>');
        }

        $distIndex = '/home/came/Nextcloud/arch-work/Projects/Todo-App/dist/index.html';

        if (! file_exists($distIndex)) {
            return $this->response->setBody(
                '<html><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#0f0f17;color:#94a3b8">'
                . '<div style="text-align:center"><p>Todo app dist not found.</p></div></body></html>'
            );
        }

        $todoHtml = file_get_contents($distIndex);

        // Rewrite asset paths from /assets/ to the public symlink so Apache serves them
        $assetBase = rtrim(base_url('todo-preview'), '/');
        $todoHtml  = str_replace('="/assets/', '="' . $assetBase . '/assets/', $todoHtml);

        // Build CSS variable overrides from the stored vars map
        $meta = json_decode($theme['metadata'] ?? '{}', true);
        $vars = $meta['vars'] ?? [];

        $cssVars = ":root {\n";
        foreach ($vars as $prop => $value) {
            $cssVars .= "  {$prop}: {$value};\n";
        }
        $cssVars .= "}\n";

        // Also inject any raw CSS from the downloaded file (for custom/uploaded themes)
        $cssPath = FCPATH . ltrim($theme['download_url'], '/');
        $rawCss  = file_exists($cssPath) ? file_get_contents($cssPath) : '';

        $styleTag = "<style>\n/* Theme Store: {$theme['display_name']} */\n{$cssVars}\n{$rawCss}\n</style>";

        $todoHtml = str_replace('</head>', $styleTag . "\n</head>", $todoHtml);

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=utf-8')
            ->setBody($todoHtml);
    }

    private function uuid4(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
