<?php

namespace App\Controllers;

use App\Models\MarketplaceThemeModel;
use App\Models\UserThemeModel;
use CodeIgniter\HTTP\Response;

class ThemeStore extends BaseController
{
    public function index()
    {
        $model  = new MarketplaceThemeModel();
        $themes = $model->where('is_published', 1)->findAll();

        foreach ($themes as &$theme) {
            $meta = json_decode($theme['metadata'] ?? '{}', true);
            $theme['colors'] = $meta['colors'] ?? [];
            $theme['tags']   = $meta['tags']   ?? [];
            $theme['vars']   = $meta['vars']   ?? [];
            
            // Provide a preview array compatible with the frontend
            $theme['preview'] = !empty($theme['colors']) ? array_values($theme['colors']) : ['#ffffff', '#f0f0f0', '#007acc'];
        }

        if ($this->request->isAJAX() || $this->request->hasHeader('Fetch') || str_contains($this->request->getHeaderLine('Accept'), 'application/json')) {
            header('Access-Control-Allow-Origin: http://localhost:5173');
            header('Access-Control-Allow-Credentials: true');
            return $this->response->setJSON($themes);
        }

        return view('theme_store', [
            'themes'  => $themes,
            'flash_success' => session()->getFlashdata('success'),
            'flash_error'   => session()->getFlashdata('error'),
        ]);
    }

    public function upload(): Response
    {
        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Credentials: true');

        $file        = $this->request->getFile('theme_css');
        $displayName = trim($this->request->getPost('display_name') ?? '');
        $description = trim($this->request->getPost('description') ?? '');

        if ($displayName === '') {
            if ($this->request->isAJAX() || $this->request->hasHeader('Fetch')) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Display name is required.']);
            }
            return redirect()->to('themes')->with('error', 'Display name is required.');
        }

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            if ($this->request->isAJAX() || $this->request->hasHeader('Fetch')) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please upload a valid CSS file.']);
            }
            return redirect()->to('themes')->with('error', 'Please upload a valid CSS file.');
        }

        if (strtolower($file->getExtension()) !== 'css') {
            if ($this->request->isAJAX() || $this->request->hasHeader('Fetch')) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Only .css files are allowed.']);
            }
            return redirect()->to('themes')->with('error', 'Only .css files are allowed.');
        }

        $slug     = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $displayName));
        $slug     = trim($slug, '-');
        $filename = $slug . '-' . substr(bin2hex(random_bytes(3)), 0, 6) . '.css';

        $file->move(FCPATH . 'themes', $filename, true);

        // Extract CSS variables and colors from the uploaded file
        $cssContent = file_get_contents(FCPATH . 'themes/' . $filename);
        preg_match_all('/(--[a-zA-Z0-9-]+)\s*:\s*([^;]+);/', $cssContent, $matches);
        
        $vars = [];
        if (!empty($matches[1])) {
            foreach ($matches[1] as $index => $key) {
                $vars[$key] = trim($matches[2][$index]);
            }
        }
        
        // Try to generate 3-color preview based on standard variables
        $colors = [];
        if (isset($vars['--bg'])) $colors['bg'] = $vars['--bg'];
        if (isset($vars['--surface'])) $colors['surface'] = $vars['--surface'];
        if (isset($vars['--accent'])) $colors['accent'] = $vars['--accent'];

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
            'metadata'     => json_encode([
                'tags'   => ['custom', 'community'],
                'colors' => $colors,
                'vars'   => $vars
            ]),
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        if ($this->request->isAJAX() || $this->request->hasHeader('Fetch')) {
            return $this->response->setJSON([
                'success' => true,
                'message' => '"' . esc($displayName) . '" uploaded successfully!'
            ]);
        }
        return redirect()->to('themes')->with('success', '"' . esc($displayName) . '" uploaded successfully!');
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

    public function install(string $id): Response
    {
        $model = new MarketplaceThemeModel();
        $theme = $model->find($id);

        if (! $theme) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Theme not found in the marketplace.']);
        }

        // Using session user_id or a default placeholder since standard auth might be configured separately
        $userId = session()->get('user_id') ?? 'default-user-id';

        $userThemeModel = new UserThemeModel();
        
        if (! $userThemeModel->isInstalled($userId, $id)) {
            $userThemeModel->installTheme($userId, $id);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => '"' . esc($theme['display_name']) . '" has been installed to your account.'
        ]);
    }

    public function activate(string $id): Response
    {
        $userId = session()->get('user_id') ?? 'default-user-id';

        $userThemeModel = new UserThemeModel();

        if (! $userThemeModel->isInstalled($userId, $id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Theme must be installed before it can be activated.']);
        }

        $userThemeModel->setActiveTheme($userId, $id);

        return $this->response->setJSON(['success' => true, 'message' => 'Theme activated successfully.']);
    }

    public function uninstall(string $id): Response
    {
        $userId = session()->get('user_id') ?? 'default-user-id';
        $userThemeModel = new UserThemeModel();

        $userThemeModel->uninstallTheme($userId, $id);

        return $this->response->setJSON(['success' => true, 'message' => 'Theme successfully uninstalled.']);
    }

    public function myThemes(): Response
    {
        $userId = session()->get('user_id') ?? 'default-user-id';
        $userThemeModel = new UserThemeModel();

        return $this->response->setJSON(['success' => true, 'data' => $userThemeModel->getUserThemes($userId)]);
    }

    public function serveCss(string $filename): Response
    {
        // Ensure it's just a file name (prevent directory traversal)
        $filename = basename($filename);
        $cssPath  = FCPATH . 'themes/' . $filename;
        
        // If the file actually exists on disk (e.g. newly uploaded themes)
        if (file_exists($cssPath)) {
            $css = file_get_contents($cssPath);
            return $this->response->setContentType('text/css')->setBody($css);
        }

        // Generate dynamically for seeded themes that don't have a physical file
        $model = new MarketplaceThemeModel();
        $name  = preg_replace('/\.css$/i', '', $filename);
        $theme = $model->where('name', $name)->first();

        if (! $theme) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $meta = json_decode($theme['metadata'] ?? '{}', true);
        $vars = $meta['vars'] ?? [];

        $css = "/* Theme: {$theme['display_name']} */\n:root {\n";
        foreach ($vars as $prop => $value) {
            $css .= "  {$prop}: {$value};\n";
        }
        $css .= "}\n";

        return $this->response->setContentType('text/css')->setBody($css);
    }

    private function uuid4(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
