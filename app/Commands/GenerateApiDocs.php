<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * GenerateApiDocs
 *
 * Generates a standalone HTML documentation page from the OpenAPI spec.
 * Uses Redoc (CDN) for rendering.
 *
 * Usage: php spark generate:api-docs
 *        php spark generate:api-docs --watch   (validate only, no write)
 *        php spark generate:api-docs --serve   (print live server URL)
 */
class GenerateApiDocs extends BaseCommand
{
    protected $group       = 'Documentation';
    protected $name        = 'generate:api-docs';
    protected $description = 'Generate API documentation HTML from openapi/openapi.yaml';
    protected $usage       = 'generate:api-docs';
    protected $arguments   = [];
    protected $options     = [
        '--watch' => 'Validate YAML only, do not write HTML',
        '--serve' => 'Print the URL at which the docs are served',
    ];

    public function run(array $params)
    {
        $projectRoot = ROOTPATH;
        $openapiFile = $projectRoot . 'openapi/openapi.yaml';
        $outputFile  = $projectRoot . 'public/api-docs.html';

        // ── Validate YAML exists ──────────────────────────────────────────
        if (!file_exists($openapiFile)) {
            CLI::error('[ERROR] openapi/openapi.yaml not found at: ' . $openapiFile);
            CLI::write('Create it first, then run this command again.', 'yellow');
            return EXIT_ERROR;
        }

        $yamlContent = file_get_contents($openapiFile);
        if (empty($yamlContent)) {
            CLI::error('[ERROR] openapi/openapi.yaml is empty.');
            return EXIT_ERROR;
        }

        // Basic structural validation (line count, presence of openapi/info/paths)
        $lines   = explode("\n", $yamlContent);
        $hasOpenapi = preg_match('/^openapi:/m', $yamlContent);
        $hasInfo    = preg_match('/^info:/m', $yamlContent);
        $hasPaths   = preg_match('/^paths:/m', $yamlContent);

        CLI::write(sprintf('  Spec file:       %s', $openapiFile), 'green');
        CLI::write(sprintf('  Size:            %d bytes', strlen($yamlContent)), 'green');
        CLI::write(sprintf('  Lines:           %d', count($lines)), 'green');

        $errors = [];
        if (!$hasOpenapi) $errors[] = 'Missing "openapi:" version declaration';
        if (!$hasInfo)    $errors[] = 'Missing "info:" section';
        if (!$hasPaths)   $errors[] = 'Missing "paths:" section';

        $totalPaths = 0;
        if (preg_match_all('/^\s{2}\/[a-z]/m', $yamlContent, $matches)) {
            $totalPaths = count($matches[0]);
        }

        CLI::write(sprintf('  Endpoints:       %d', $totalPaths), 'green');

        if (!empty($errors)) {
            CLI::error('[VALIDATION] ' . count($errors) . ' issue(s) found:');
            foreach ($errors as $err) {
                CLI::write('  - ' . $err, 'red');
            }
            return EXIT_ERROR;
        }

        CLI::write('[VALIDATION] OpenAPI spec looks valid.', 'green');

        // ── --watch mode: stop here ────────────────────────────────────────
        if (isset($params['watch']) || array_key_exists('watch', $params)) {
            CLI::write('Watch mode — no files written.', 'yellow');
            return EXIT_SUCCESS;
        }

        // ── Generate HTML ────────────────────────────────────────────────
        $apiTitle  = 'Todo App API Documentation';

        // Escape YAML for embedding as a JS template literal.
        // Safe: escape backtick, backslash, and template substitution.
        $escapedYaml = str_replace(
            ['\\', '`', '${'],
            ['\\\\', '\\`', '\\${'],
            $yamlContent
        );

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{$apiTitle}</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.css" rel="stylesheet" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', -apple-system, sans-serif; background: #f8f9fa; }
    .topbar {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
      padding: 16px 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      color: #fff;
    }
    .topbar h1 { font-size: 20px; font-weight: 600; letter-spacing: -0.3px; }
    .topbar .subtitle { font-size: 13px; color: #94a3b8; margin-top: 2px; }
    .topbar .badge {
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.15);
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      color: #e2e8f0;
    }
    #redoc-container { min-height: calc(100vh - 64px); }
    .loading {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 60vh;
      color: #64748b;
      font-size: 14px;
    }
    .loading::after {
      content: '';
      width: 20px;
      height: 20px;
      margin-left: 10px;
      border: 2px solid #e2e8f0;
      border-top-color: #3b82f6;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <div class="topbar">
    <div>
      <h1>{$apiTitle}</h1>
      <div class="subtitle">Todo App Backend — OpenAPI 3.0</div>
    </div>
    <div class="badge">Generated: GENERATED_DATE</div>
  </div>
  <div class="loading" id="loading">Loading API documentation...</div>
  <div id="redoc-container"></div>
  <script src="https://cdn.jsdelivr.net/npm/js-yaml@4.1.0/dist/js-yaml.min.js"></script>
  <script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>
  <script>
    var yamlText = `YAML_CONTENT`;
    var spec = jsyaml.load(yamlText);
    Redoc.init(
      spec,
      {
        scrollYOffset: 64,
        hideDownloadButton: false,
        expandResponses: "200,201",
        hideSingleRequestSampleTab: false,
        sortPropsAlphabetically: false,
        requiredPropsFirst: true,
        showObjectSchemaExamples: true,
        theme: {
          colors: { primary: { main: '#3b82f6' } },
          sidebar: { backgroundColor: '#ffffff', width: '280px' },
          rightPanel: { backgroundColor: '#1e293b' }
        },
        nativeScrollbars: true
      },
      document.getElementById('redoc-container')
    );
    document.getElementById('loading').style.display = 'none';
  </script>
</body>
</html>
HTML;

        $html = str_replace(
            ['YAML_CONTENT', 'GENERATED_DATE'],
            [$escapedYaml, date('Y-m-d H:i:s')],
            $html
        );

        file_put_contents($outputFile, $html);

        CLI::write(sprintf('[DONE] Docs generated: %s', $outputFile), 'green');
        CLI::write(sprintf('       Size: %d bytes', filesize($outputFile)), 'green');

        if (isset($params['serve']) || array_key_exists('serve', $params)) {
            $baseUrl = CLI::getOption('base-url') ?? 'http://localhost:8080';
            CLI::write(sprintf('       Open in browser: %s/api-docs.html', $baseUrl), 'cyan');
        }

        return EXIT_SUCCESS;
    }
}
