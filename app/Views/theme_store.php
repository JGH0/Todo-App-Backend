<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Theme Store</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #0f0f17;
      color: #e2e8f0;
      min-height: 100vh;
    }

    /* ── Header ── */
    header {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
      padding: 60px 24px 50px;
      text-align: center;
      border-bottom: 1px solid rgba(255,255,255,0.06);
      position: relative;
      overflow: hidden;
    }
    header::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse at 50% 0%, rgba(124,58,237,0.15) 0%, transparent 70%);
      pointer-events: none;
    }
    header h1 {
      font-size: clamp(2rem, 5vw, 3.2rem);
      font-weight: 800;
      letter-spacing: -0.03em;
      background: linear-gradient(135deg, #a78bfa, #60a5fa, #f472b6);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      position: relative;
    }
    header p {
      margin-top: 12px;
      color: #94a3b8;
      font-size: 1.1rem;
      position: relative;
    }
    .header-row {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 14px;
      margin-top: 20px;
      position: relative;
      flex-wrap: wrap;
    }
    .header-badge {
      background: rgba(124,58,237,0.2);
      border: 1px solid rgba(124,58,237,0.4);
      color: #a78bfa;
      padding: 4px 14px;
      border-radius: 100px;
      font-size: 0.8rem;
      font-weight: 600;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }
    .btn-upload-header {
      background: linear-gradient(135deg, #7c3aed, #6d28d9);
      color: #fff;
      border: none;
      padding: 8px 20px;
      border-radius: 100px;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: opacity 0.15s, transform 0.15s;
    }
    .btn-upload-header:hover { opacity: 0.88; transform: translateY(-1px); }

    /* ── Flash messages ── */
    .flash {
      max-width: 680px;
      margin: 24px auto 0;
      padding: 12px 18px;
      border-radius: 10px;
      font-size: 0.9rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .flash-success { background: rgba(52,211,153,0.12); border: 1px solid rgba(52,211,153,0.3); color: #34d399; }
    .flash-error   { background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.3); color: #f87171; }

    /* ── Grid ── */
    .grid-wrapper {
      max-width: 1200px;
      margin: 0 auto;
      padding: 48px 24px 80px;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 28px;
    }

    /* ── Card ── */
    .card {
      background: #1a1a2e;
      border: 1px solid rgba(255,255,255,0.07);
      border-radius: 16px;
      overflow: hidden;
      transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 48px rgba(0,0,0,0.5);
      border-color: rgba(167,139,250,0.3);
    }
    .card-preview {
      height: 90px;
      display: flex;
      overflow: hidden;
    }
    .card-preview .swatch { flex: 1; transition: flex 0.3s ease; }
    .card:hover .card-preview .swatch { flex: 1.4; }

    .card-body { padding: 22px; }
    .card-meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 8px;
    }
    .card-name  { font-size: 1.15rem; font-weight: 700; color: #f1f5f9; }
    .card-version {
      font-size: 0.75rem; color: #64748b;
      background: rgba(255,255,255,0.05);
      padding: 2px 8px; border-radius: 100px;
    }
    .card-author { font-size: 0.8rem; color: #94a3b8; margin-bottom: 10px; }
    .card-desc {
      font-size: 0.88rem; color: #94a3b8; line-height: 1.55;
      margin-bottom: 14px;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .card-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 18px; }
    .tag {
      background: rgba(167,139,250,0.1);
      border: 1px solid rgba(167,139,250,0.25);
      color: #a78bfa;
      font-size: 0.72rem; padding: 2px 9px; border-radius: 100px; font-weight: 500;
    }
    .card-actions { display: flex; gap: 10px; }
    .btn {
      flex: 1; padding: 10px 0; border-radius: 8px; border: none;
      cursor: pointer; font-size: 0.85rem; font-weight: 600;
      transition: opacity 0.15s, transform 0.15s;
    }
    .btn:hover { opacity: 0.88; transform: translateY(-1px); }
    .btn-download {
      background: linear-gradient(135deg, #7c3aed, #6d28d9);
      color: #fff; text-decoration: none;
      display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .btn-details {
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.1);
      color: #e2e8f0;
    }
    .btn-details:hover { background: rgba(255,255,255,0.1); }

    /* ── Backdrop shared ── */
    .modal-backdrop {
      display: none;
      position: fixed; inset: 0;
      background: rgba(0,0,0,0.8);
      backdrop-filter: blur(6px);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }
    .modal-backdrop.open { display: flex; }

    /* ── Details Modal ── */
    .details-modal {
      background: #1a1a2e;
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 20px;
      width: 95vw;
      max-width: 1100px;
      height: 88vh;
      display: flex;
      flex-direction: column;
      animation: popIn 0.22s cubic-bezier(0.34,1.56,0.64,1);
      overflow: hidden;
    }
    @keyframes popIn {
      from { opacity: 0; transform: scale(0.92) translateY(20px); }
      to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    /* colour stripe at top */
    .modal-preview-stripe {
      height: 80px;
      display: flex;
      flex-shrink: 0;
    }
    .modal-preview-stripe .swatch { flex: 1; }

    /* tab bar */
    .modal-tabs {
      display: flex;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      background: rgba(255,255,255,0.02);
      flex-shrink: 0;
      padding: 0 24px;
    }
    .modal-tab {
      padding: 12px 20px;
      font-size: 0.88rem;
      font-weight: 600;
      color: #64748b;
      cursor: pointer;
      border-bottom: 2px solid transparent;
      transition: color 0.15s, border-color 0.15s;
      background: none;
      border-top: none; border-left: none; border-right: none;
      display: flex; align-items: center; gap: 7px;
      margin-bottom: -1px;
    }
    .modal-tab:hover { color: #94a3b8; }
    .modal-tab.active { color: #a78bfa; border-bottom-color: #7c3aed; }

    /* tab panels */
    .modal-body {
      flex: 1;
      overflow: hidden;
      position: relative;
    }
    .tab-panel {
      display: none;
      height: 100%;
      overflow-y: auto;
    }
    .tab-panel.active { display: block; }

    /* details panel */
    .details-panel { padding: 28px; }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 6px;
    }
    .modal-title { font-size: 1.4rem; font-weight: 800; color: #f1f5f9; }
    .modal-close {
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.1);
      color: #94a3b8;
      border-radius: 8px; width: 34px; height: 34px;
      cursor: pointer; font-size: 1.1rem;
      display: flex; align-items: center; justify-content: center;
      transition: background 0.15s; flex-shrink: 0;
    }
    .modal-close:hover { background: rgba(255,255,255,0.12); color: #fff; }
    .modal-meta { font-size: 0.82rem; color: #64748b; margin-bottom: 16px; }
    .modal-desc { font-size: 0.92rem; color: #94a3b8; line-height: 1.65; margin-bottom: 22px; }
    .section-label {
      font-size: 0.75rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.08em;
      color: #64748b; margin-bottom: 10px;
    }
    .colors-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
      gap: 10px;
      margin-bottom: 22px;
    }
    .color-chip { border-radius: 10px; overflow: hidden; border: 1px solid rgba(255,255,255,0.07); }
    .color-chip-swatch { height: 48px; }
    .color-chip-info { padding: 6px 8px; background: rgba(255,255,255,0.03); }
    .color-chip-name { font-size: 0.7rem; color: #94a3b8; display: block; }
    .color-chip-hex  { font-size: 0.75rem; font-weight: 600; color: #e2e8f0; font-family: 'SF Mono', monospace; }
    .modal-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 24px; }
    .modal-actions { display: flex; gap: 10px; }
    .btn-download-lg {
      flex: 1; padding: 13px; font-size: 0.95rem; border-radius: 10px;
      background: linear-gradient(135deg, #7c3aed, #6d28d9);
      color: #fff; font-weight: 700; text-decoration: none;
      display: flex; align-items: center; justify-content: center; gap: 8px;
      border: none; cursor: pointer;
      transition: opacity 0.15s, transform 0.15s;
    }
    .btn-download-lg:hover { opacity: 0.88; transform: translateY(-1px); }

    /* preview panel */
    .preview-panel {
      padding: 0;
      height: 100%;
      display: none;
      flex-direction: column;
    }
    .preview-panel.active { display: flex; }
    .preview-toolbar {
      padding: 10px 16px;
      background: rgba(255,255,255,0.03);
      border-bottom: 1px solid rgba(255,255,255,0.06);
      display: flex;
      align-items: center;
      gap: 10px;
      flex-shrink: 0;
    }
    .preview-dot { width: 10px; height: 10px; border-radius: 50%; }
    .preview-url {
      flex: 1;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 6px;
      padding: 4px 12px;
      font-size: 0.78rem;
      color: #64748b;
      font-family: monospace;
    }
    .preview-iframe-wrap {
      flex: 1;
      overflow: hidden;
      position: relative;
    }
    .preview-iframe-wrap iframe {
      width: 100%;
      height: 100%;
      border: none;
      display: block;
    }
    .preview-loading {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: #0f0f17;
      color: #94a3b8;
      gap: 14px;
      font-size: 0.9rem;
    }
    .spinner {
      width: 32px; height: 32px;
      border: 3px solid rgba(167,139,250,0.2);
      border-top-color: #7c3aed;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Upload Modal ── */
    .upload-modal {
      background: #1a1a2e;
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 20px;
      width: 100%;
      max-width: 480px;
      animation: popIn 0.22s cubic-bezier(0.34,1.56,0.64,1);
      overflow: hidden;
    }
    .upload-header {
      padding: 24px 28px 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .upload-title { font-size: 1.25rem; font-weight: 700; color: #f1f5f9; }
    .upload-body { padding: 0 28px 28px; }
    .field { margin-bottom: 18px; }
    .field label {
      display: block;
      font-size: 0.8rem;
      font-weight: 600;
      color: #94a3b8;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .field input[type="text"],
    .field textarea {
      width: 100%;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 8px;
      padding: 10px 14px;
      color: #e2e8f0;
      font-size: 0.9rem;
      font-family: inherit;
      outline: none;
      transition: border-color 0.15s;
    }
    .field input[type="text"]:focus,
    .field textarea:focus {
      border-color: rgba(124,58,237,0.5);
    }
    .field textarea { resize: vertical; min-height: 80px; }
    .file-drop {
      border: 2px dashed rgba(124,58,237,0.3);
      border-radius: 10px;
      padding: 24px;
      text-align: center;
      cursor: pointer;
      transition: border-color 0.15s, background 0.15s;
      position: relative;
      color: #64748b;
      font-size: 0.88rem;
    }
    .file-drop:hover,
    .file-drop.drag-over {
      border-color: rgba(124,58,237,0.7);
      background: rgba(124,58,237,0.05);
    }
    .file-drop input[type="file"] {
      position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .file-drop-icon { font-size: 1.8rem; margin-bottom: 8px; display: block; }
    .file-drop strong { color: #a78bfa; }
    .file-name-preview {
      margin-top: 8px;
      font-size: 0.8rem;
      color: #34d399;
      display: none;
    }
    .btn-submit {
      width: 100%;
      padding: 13px;
      background: linear-gradient(135deg, #7c3aed, #6d28d9);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: 0.95rem;
      font-weight: 700;
      cursor: pointer;
      transition: opacity 0.15s, transform 0.15s;
    }
    .btn-submit:hover { opacity: 0.88; transform: translateY(-1px); }

    /* ── Footer ── */
    footer {
      text-align: center;
      padding: 32px 24px;
      color: #334155;
      font-size: 0.82rem;
      border-top: 1px solid rgba(255,255,255,0.04);
    }

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(167,139,250,0.3); border-radius: 3px; }
  </style>
</head>
<body>

<header>
  <h1>Theme Store</h1>
  <p>Beautiful, ready-to-use themes for your application</p>
  <div class="header-row">
    <span class="header-badge"><?= count($themes) ?> free themes</span>
    <button class="btn-upload-header" onclick="openUploadModal()">
      <span>&#x2B;</span> Upload Theme
    </button>
  </div>
</header>

<?php if ($flash_success): ?>
  <div style="max-width:1200px;margin:0 auto;padding:0 24px">
    <div class="flash flash-success">&#10003; <?= esc($flash_success) ?></div>
  </div>
<?php elseif ($flash_error): ?>
  <div style="max-width:1200px;margin:0 auto;padding:0 24px">
    <div class="flash flash-error">&#9888; <?= esc($flash_error) ?></div>
  </div>
<?php endif; ?>

<div class="grid-wrapper">
  <div class="grid">
    <?php foreach ($themes as $theme): ?>
    <div class="card">
      <div class="card-preview">
        <?php foreach (array_values($theme['colors']) as $hex): ?>
          <div class="swatch" style="background:<?= esc($hex) ?>"></div>
        <?php endforeach; ?>
        <?php if (empty($theme['colors'])): ?>
          <div class="swatch" style="background:linear-gradient(135deg,#7c3aed,#f472b6)"></div>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="card-meta">
          <span class="card-name"><?= esc($theme['display_name']) ?></span>
          <span class="card-version">v<?= esc($theme['version']) ?></span>
        </div>
        <div class="card-author">by <?= esc($theme['author']) ?></div>
        <p class="card-desc"><?= esc($theme['description']) ?></p>
        <div class="card-tags">
          <?php foreach ($theme['tags'] as $tag): ?>
            <span class="tag"><?= esc($tag) ?></span>
          <?php endforeach; ?>
        </div>
        <div class="card-actions">
          <a class="btn btn-download" href="<?= esc($theme['download_url']) ?>" download>
            &#8659; Download
          </a>
          <button class="btn btn-details"
            onclick='openDetailsModal(<?= json_encode([
              "id"           => $theme["id"],
              "display_name" => $theme["display_name"],
              "description"  => $theme["description"],
              "author"       => $theme["author"],
              "version"      => $theme["version"],
              "download_url" => $theme["download_url"],
              "colors"       => $theme["colors"],
              "tags"         => $theme["tags"],
              "vars"         => $theme["vars"],
            ]) ?>)'>
            Details
          </button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ── Details Modal ── -->
<div class="modal-backdrop" id="details-backdrop" onclick="closeDetailsOnBackdrop(event)">
  <div class="details-modal" id="details-modal">

    <div class="modal-preview-stripe" id="dm-stripe"></div>

    <div class="modal-tabs">
      <button class="modal-tab active" id="tab-details" onclick="switchTab('details')">
        &#9998; Details
      </button>
      <button class="modal-tab" id="tab-preview" onclick="switchTab('preview')">
        &#9654; Live Preview
      </button>
    </div>

    <div class="modal-body">

      <!-- Details panel -->
      <div class="tab-panel active" id="panel-details">
        <div class="details-panel">
          <div class="modal-header">
            <span class="modal-title" id="dm-title"></span>
            <button class="modal-close" onclick="closeDetailsModal()">&#x2715;</button>
          </div>
          <div class="modal-meta" id="dm-meta"></div>
          <p class="modal-desc" id="dm-desc"></p>

          <div id="dm-colors-section">
            <div class="section-label">Colour Palette</div>
            <div class="colors-grid" id="dm-colors"></div>
          </div>

          <div class="section-label" style="margin-bottom:10px">Tags</div>
          <div class="modal-tags" id="dm-tags"></div>

          <div class="modal-actions">
            <a class="btn-download-lg" id="dm-download" href="#" download>
              &#8659; Download Theme
            </a>
          </div>
        </div>
      </div>

      <!-- Preview panel -->
      <div class="tab-panel" id="panel-preview" style="height:100%">
        <div class="preview-panel" id="preview-panel-inner">
          <div class="preview-toolbar">
            <div class="preview-dot" style="background:#ff5f57"></div>
            <div class="preview-dot" style="background:#febc2e"></div>
            <div class="preview-dot" style="background:#28c840"></div>
            <div class="preview-url" id="preview-url">localhost:5173 — with theme applied</div>
          </div>
          <div class="preview-iframe-wrap">
            <div class="preview-loading" id="preview-loading">
              <div class="spinner"></div>
              <span>Loading preview&hellip;</span>
            </div>
            <iframe id="preview-iframe" title="Theme Preview" onload="iframeLoaded()"></iframe>
          </div>
        </div>
      </div>

    </div><!-- .modal-body -->
  </div>
</div>

<!-- ── Upload Modal ── -->
<div class="modal-backdrop" id="upload-backdrop" onclick="closeUploadOnBackdrop(event)">
  <div class="upload-modal">
    <div class="upload-header">
      <span class="upload-title">Upload Custom Theme</span>
      <button class="modal-close" onclick="closeUploadModal()">&#x2715;</button>
    </div>
    <div class="upload-body">
      <form method="post" action="/themes/upload" enctype="multipart/form-data">

        <div class="field">
          <label>Display Name *</label>
          <input type="text" name="display_name" placeholder="e.g. Neon Sunset" required>
        </div>

        <div class="field">
          <label>Description</label>
          <textarea name="description" placeholder="Describe your theme's mood and colours…"></textarea>
        </div>

        <div class="field">
          <label>CSS File *</label>
          <div class="file-drop" id="file-drop">
            <input type="file" name="theme_css" accept=".css" required onchange="previewFileName(this)">
            <span class="file-drop-icon">&#128190;</span>
            <div>Drop your <strong>.css</strong> file here<br>or <strong>click to browse</strong></div>
            <div class="file-name-preview" id="file-name-preview"></div>
          </div>
        </div>

        <button type="submit" class="btn-submit">&#x2191; Upload Theme</button>
      </form>
    </div>
  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> Theme Store &mdash; All themes are free to use
</footer>

<script>
  let currentThemeId   = null;
  let currentThemeVars = {};
  let previewLoaded    = false;

  /* ── Details modal ── */
  function openDetailsModal(theme) {
    currentThemeId   = theme.id;
    currentThemeVars = theme.vars || {};
    previewLoaded    = false;

    const colors = theme.colors || {};
    const tags   = theme.tags   || [];

    document.getElementById('dm-title').textContent    = theme.display_name;
    document.getElementById('dm-meta').textContent     = 'by ' + theme.author + '  ·  v' + theme.version;
    document.getElementById('dm-desc').textContent     = theme.description;
    document.getElementById('dm-download').href        = theme.download_url;
    document.getElementById('preview-url').textContent = 'localhost — ' + theme.display_name + ' applied';

    // stripe
    const stripe = document.getElementById('dm-stripe');
    const colorValues = Object.values(colors);
    stripe.innerHTML = colorValues.length
      ? colorValues.map(c => `<div class="swatch" style="background:${c}"></div>`).join('')
      : '<div class="swatch" style="background:linear-gradient(135deg,#7c3aed,#f472b6)"></div>';

    // colour chips
    const colorsSection = document.getElementById('dm-colors-section');
    const grid = document.getElementById('dm-colors');
    if (Object.keys(colors).length) {
      colorsSection.style.display = '';
      grid.innerHTML = Object.entries(colors).map(([name, hex]) => `
        <div class="color-chip">
          <div class="color-chip-swatch" style="background:${hex}"></div>
          <div class="color-chip-info">
            <span class="color-chip-name">${name}</span>
            <span class="color-chip-hex">${hex}</span>
          </div>
        </div>`).join('');
    } else {
      colorsSection.style.display = 'none';
    }

    // tags
    document.getElementById('dm-tags').innerHTML =
      tags.length ? tags.map(t => `<span class="tag">${t}</span>`).join('') : '<span style="color:#475569;font-size:0.82rem">No tags</span>';

    // reset to details tab
    switchTab('details');

    document.getElementById('details-backdrop').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeDetailsModal() {
    document.getElementById('details-backdrop').classList.remove('open');
    document.getElementById('preview-iframe').src = '';
    document.getElementById('preview-loading').style.display = 'flex';
    currentThemeVars = {};
    previewLoaded    = false;
    document.body.style.overflow = '';
  }

  function closeDetailsOnBackdrop(e) {
    if (e.target === document.getElementById('details-backdrop')) closeDetailsModal();
  }

  /* ── Tabs ── */
  function switchTab(name) {
    document.querySelectorAll('.modal-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

    document.getElementById('tab-' + name).classList.add('active');
    document.getElementById('panel-' + name).classList.add('active');

    if (name === 'preview') {
      document.getElementById('preview-panel-inner').classList.add('active');
      if (!previewLoaded && currentThemeId) {
        document.getElementById('preview-loading').style.display = 'flex';
        const encoded = btoa(JSON.stringify(currentThemeVars));
        document.getElementById('preview-iframe').src = 'http://localhost:5173/?__theme=' + encoded;
      }
    } else {
      document.getElementById('preview-panel-inner').classList.remove('active');
    }
  }

  function iframeLoaded() {
    previewLoaded = true;
    document.getElementById('preview-loading').style.display = 'none';
  }

  /* ── Upload modal ── */
  function openUploadModal() {
    document.getElementById('upload-backdrop').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeUploadModal() {
    document.getElementById('upload-backdrop').classList.remove('open');
    document.body.style.overflow = '';
  }
  function closeUploadOnBackdrop(e) {
    if (e.target === document.getElementById('upload-backdrop')) closeUploadModal();
  }

  function previewFileName(input) {
    const el = document.getElementById('file-name-preview');
    if (input.files && input.files[0]) {
      el.textContent = '✓ ' + input.files[0].name;
      el.style.display = 'block';
    }
  }

  // Drag-over styling
  const drop = document.getElementById('file-drop');
  drop.addEventListener('dragover',  () => drop.classList.add('drag-over'));
  drop.addEventListener('dragleave', () => drop.classList.remove('drag-over'));
  drop.addEventListener('drop',      () => drop.classList.remove('drag-over'));

  document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeDetailsModal(); closeUploadModal(); } });
</script>

</body>
</html>
