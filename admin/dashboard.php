<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

// Auth guard
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Zorg dat er altijd een CSRF-token in de sessie zit
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load content helper
function loadContent(string $section): array {
    $path = CONTENT_DIR . $section . '.json';
    if (!file_exists($path)) return [];
    return json_decode(file_get_contents($path), true) ?? [];
}

$hero      = loadContent('hero');
$missie    = loadContent('missie');
$train     = loadContent('trainingen');
$team      = loadContent('team');
$contact   = loadContent('contact');

$activeSec = $_GET['section'] ?? 'hero';
$sections  = ['hero', 'missie', 'trainingen', 'team', 'contact'];
if (!in_array($activeSec, $sections)) $activeSec = 'hero';

$sectionLabels = [
    'hero'       => 'Hero',
    'missie'     => 'Missie &amp; Visie',
    'trainingen' => 'Trainingen',
    'team'       => 'Team',
    'contact'    => 'Contact',
];

$sectionIcons = [
    'hero'       => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
    'missie'     => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
    'trainingen' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    'team'       => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    'contact'    => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
];

function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function val(array $arr, string $key): string { return e((string)($arr[$key] ?? '')); }
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — <?= e(SITE_TITLE) ?></title>
  <meta name="csrf-token" content="<?= e($_SESSION['csrf_token']) ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: system-ui, -apple-system, sans-serif; }
    .input-dark {
      background: #1e293b;
      border: 1px solid #334155;
      color: #f1f5f9;
      width: 100%;
      padding: 0.6rem 0.85rem;
      border-radius: 0.6rem;
      font-size: 0.875rem;
      transition: border-color 0.2s;
    }
    .input-dark:focus { outline: none; border-color: #3b82f6; }
    textarea.input-dark { resize: vertical; min-height: 90px; }
    label { display: block; font-size: 0.8rem; font-weight: 600; color: #94a3b8; margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .field { margin-bottom: 1.2rem; }
    #toast { position: fixed; top: 1.5rem; right: 1.5rem; z-index: 100; padding: 0.75rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 600; opacity: 0; transform: translateY(-10px); transition: all 0.3s ease; pointer-events: none; }
    #toast.show { opacity: 1; transform: translateY(0); }
    .sidebar-link { display: flex; align-items: center; gap: 0.65rem; padding: 0.6rem 0.85rem; border-radius: 0.6rem; font-size: 0.875rem; font-weight: 500; color: #94a3b8; transition: all 0.15s; cursor: pointer; text-decoration: none; }
    .sidebar-link:hover { background: #1e293b; color: #f1f5f9; }
    .sidebar-link.active { background: #1e3a8a; color: #93c5fd; }
  </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex">

<!-- Toast notification -->
<div id="toast"></div>

<!-- ===== SIDEBAR ===== -->
<aside class="w-64 flex-shrink-0 bg-slate-950 border-r border-slate-800 flex flex-col min-h-screen">
  <!-- Logo -->
  <div class="p-5 border-b border-slate-800">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
      </div>
      <div>
        <p class="text-white font-bold text-sm leading-none">StudeerSamen</p>
        <p class="text-slate-500 text-xs mt-0.5">Admin</p>
      </div>
    </div>
  </div>

  <!-- Nav -->
  <nav class="flex-1 p-4 space-y-1">
    <p class="text-slate-600 text-xs font-semibold uppercase tracking-widest mb-3 px-2">Pagina-secties</p>
    <?php foreach ($sections as $sec): ?>
    <a href="?section=<?= $sec ?>"
      class="sidebar-link <?= $activeSec === $sec ? 'active' : '' ?>">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $sectionIcons[$sec] ?>"/>
      </svg>
      <?= $sectionLabels[$sec] ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- Bottom actions -->
  <div class="p-4 border-t border-slate-800 space-y-2">
    <a href="../index.php" target="_blank"
      class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
      </svg>
      Bekijk website
    </a>
    <a href="logout.php"
      class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-red-400 hover:bg-red-950 hover:text-red-300 transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
      </svg>
      Uitloggen
    </a>
  </div>
</aside>

<!-- ===== MAIN CONTENT ===== -->
<main class="flex-1 flex flex-col min-h-screen overflow-auto">

  <!-- Top bar -->
  <div class="bg-slate-950 border-b border-slate-800 px-8 py-4 flex items-center justify-between">
    <div>
      <h1 class="text-white font-bold text-lg"><?= $sectionLabels[$activeSec] ?></h1>
      <p class="text-slate-500 text-sm">Bewerk de inhoud van deze sectie</p>
    </div>
    <button onclick="saveSection()"
      class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl transition-all shadow-lg text-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
      </svg>
      Opslaan
    </button>
  </div>

  <!-- Editor area -->
  <div class="flex-1 p-8 max-w-3xl">

    <!-- ===== HERO ===== -->
    <?php if ($activeSec === 'hero'): ?>
    <div id="editor-form">
      <input type="hidden" name="_section" value="hero">
      <div class="field"><label>Badge tekst</label><input type="text" class="input-dark" name="badge" value="<?= val($hero,'badge') ?>"></div>
      <div class="field"><label>Titel (regel 1)</label><input type="text" class="input-dark" name="title" value="<?= val($hero,'title') ?>"></div>
      <div class="field"><label>Titel (regel 2, accent)</label><input type="text" class="input-dark" name="title_accent" value="<?= val($hero,'title_accent') ?>"></div>
      <div class="field"><label>Subtekst</label><textarea class="input-dark" name="subtitle"><?= val($hero,'subtitle') ?></textarea></div>
      <div class="bg-slate-800 rounded-2xl p-5 mb-5">
        <p class="text-slate-400 text-sm font-semibold uppercase tracking-wider mb-4">Statistieken</p>
        <div class="grid grid-cols-2 gap-4">
          <div class="field"><label>Stat 1 — waarde</label><input type="text" class="input-dark" name="stat1_value" value="<?= val($hero,'stat1_value') ?>"></div>
          <div class="field"><label>Stat 1 — label</label><input type="text" class="input-dark" name="stat1_label" value="<?= val($hero,'stat1_label') ?>"></div>
          <div class="field"><label>Stat 2 — waarde</label><input type="text" class="input-dark" name="stat2_value" value="<?= val($hero,'stat2_value') ?>"></div>
          <div class="field"><label>Stat 2 — label</label><input type="text" class="input-dark" name="stat2_label" value="<?= val($hero,'stat2_label') ?>"></div>
          <div class="field"><label>Stat 3 — waarde</label><input type="text" class="input-dark" name="stat3_value" value="<?= val($hero,'stat3_value') ?>"></div>
          <div class="field"><label>Stat 3 — label</label><input type="text" class="input-dark" name="stat3_label" value="<?= val($hero,'stat3_label') ?>"></div>
        </div>
      </div>
    </div>

    <!-- ===== MISSIE ===== -->
    <?php elseif ($activeSec === 'missie'): ?>
    <div id="editor-form">
      <input type="hidden" name="_section" value="missie">
      <div class="bg-slate-800 rounded-2xl p-5 mb-5">
        <p class="text-slate-300 font-semibold mb-4">🎯 Missie</p>
        <div class="field"><label>Titel</label><input type="text" class="input-dark" name="missie_title" value="<?= val($missie,'missie_title') ?>"></div>
        <div class="field"><label>Tekst</label><textarea class="input-dark" name="missie_text"><?= val($missie,'missie_text') ?></textarea></div>
      </div>
      <div class="bg-slate-800 rounded-2xl p-5 mb-5">
        <p class="text-slate-300 font-semibold mb-4">👁️ Visie</p>
        <div class="field"><label>Titel</label><input type="text" class="input-dark" name="visie_title" value="<?= val($missie,'visie_title') ?>"></div>
        <div class="field"><label>Tekst</label><textarea class="input-dark" name="visie_text"><?= val($missie,'visie_text') ?></textarea></div>
      </div>
      <div class="bg-slate-800 rounded-2xl p-5 mb-5">
        <p class="text-slate-300 font-semibold mb-4">❤️ Waarden</p>
        <div class="field"><label>Titel</label><input type="text" class="input-dark" name="waarden_title" value="<?= val($missie,'waarden_title') ?>"></div>
        <div class="field"><label>Tekst</label><textarea class="input-dark" name="waarden_text"><?= val($missie,'waarden_text') ?></textarea></div>
      </div>
    </div>

    <!-- ===== TRAININGEN ===== -->
    <?php elseif ($activeSec === 'trainingen'): ?>
    <div id="editor-form">
      <input type="hidden" name="_section" value="trainingen">
      <?php $trainingen = $train['trainingen'] ?? []; ?>
      <?php foreach ($trainingen as $i => $t): ?>
      <div class="bg-slate-800 rounded-2xl p-5 mb-5">
        <p class="text-slate-300 font-semibold mb-4">📚 Training <?= $i+1 ?>: <?= e($t['vak']) ?></p>
        <div class="grid grid-cols-2 gap-4">
          <div class="field"><label>Vak</label><input type="text" class="input-dark" name="trainingen[<?=$i?>][vak]" value="<?= e($t['vak']) ?>"></div>
          <div class="field"><label>Niveau</label><input type="text" class="input-dark" name="trainingen[<?=$i?>][niveau]" value="<?= e($t['niveau']) ?>"></div>
          <div class="field"><label>Duur</label><input type="text" class="input-dark" name="trainingen[<?=$i?>][duur]" value="<?= e($t['duur']) ?>"></div>
          <div class="field"><label>Groep</label><input type="text" class="input-dark" name="trainingen[<?=$i?>][groep]" value="<?= e($t['groep']) ?>"></div>
          <div class="field col-span-2">
            <label>Kleur (blue / violet / emerald)</label>
            <input type="text" class="input-dark" name="trainingen[<?=$i?>][kleur]" value="<?= e($t['kleur']) ?>">
          </div>
        </div>
        <div class="field mt-2">
          <label>Topics (één per regel)</label>
          <textarea class="input-dark" name="trainingen[<?=$i?>][topics]"><?= e(implode("\n", $t['topics'] ?? [])) ?></textarea>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ===== TEAM ===== -->
    <?php elseif ($activeSec === 'team'): ?>
    <div id="editor-form">
      <input type="hidden" name="_section" value="team">
      <?php $teamleden = $team['teamleden'] ?? []; ?>
      <?php foreach ($teamleden as $i => $lid): ?>
      <div class="bg-slate-800 rounded-2xl p-5 mb-4">
        <p class="text-slate-300 font-semibold mb-4">👤 Teamlid <?= $i+1 ?></p>
        <div class="grid grid-cols-2 gap-3">
          <div class="field"><label>Naam</label><input type="text" class="input-dark" name="team[<?=$i?>][naam]" value="<?= e($lid['naam']) ?>"></div>
          <div class="field"><label>Initialen</label><input type="text" class="input-dark" name="team[<?=$i?>][initialen]" value="<?= e($lid['initialen']) ?>"></div>
          <div class="field"><label>Rol</label><input type="text" class="input-dark" name="team[<?=$i?>][rol]" value="<?= e($lid['rol']) ?>"></div>
          <div class="field"><label>Studie</label><input type="text" class="input-dark" name="team[<?=$i?>][studie]" value="<?= e($lid['studie']) ?>"></div>
          <div class="field col-span-2">
            <label>Kleur (blue/violet/emerald/amber/rose/cyan/orange)</label>
            <input type="text" class="input-dark" name="team[<?=$i?>][kleur]" value="<?= e($lid['kleur']) ?>">
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ===== CONTACT ===== -->
    <?php elseif ($activeSec === 'contact'): ?>
    <div id="editor-form">
      <input type="hidden" name="_section" value="contact">
      <div class="field"><label>Sectie titel</label><input type="text" class="input-dark" name="heading" value="<?= val($contact,'heading') ?>"></div>
      <div class="field"><label>Subtekst</label><textarea class="input-dark" name="subtext"><?= val($contact,'subtext') ?></textarea></div>
      <div class="field"><label>E-mailadres</label><input type="email" class="input-dark" name="email" value="<?= val($contact,'email') ?>"></div>
      <div class="field"><label>Locatie</label><input type="text" class="input-dark" name="locatie" value="<?= val($contact,'locatie') ?>"></div>
    </div>
    <?php endif; ?>

  </div><!-- /editor area -->
</main>

<script>
// Lees CSRF-token uit meta-tag
let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.background = ok ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.2)';
  t.style.color = ok ? '#6ee7b7' : '#fca5a5';
  t.style.border = ok ? '1px solid rgba(16,185,129,0.4)' : '1px solid rgba(239,68,68,0.4)';
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

function saveSection() {
  const form = document.getElementById('editor-form');
  const inputs = form.querySelectorAll('input, textarea');
  const data = {};
  const section = form.querySelector('[name="_section"]').value;

  inputs.forEach(el => {
    if (el.name === '_section') return;
    const name = el.name;

    // Verwerk geneste velden: trainingen[0][vak]
    const match = name.match(/^(\w+)\[(\d+)\]\[(\w+)\]$/);
    if (match) {
      const [, arr, idx, key] = match;
      if (!data[arr]) data[arr] = [];
      if (!data[arr][idx]) data[arr][idx] = {};
      if (key === 'topics') {
        data[arr][idx][key] = el.value.split('\n').map(s => s.trim()).filter(Boolean);
      } else {
        data[arr][idx][key] = el.value;
      }
      return;
    }

    // Gewone velden
    data[name] = el.value;
  });

  fetch('save.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrfToken          // ← CSRF-token meesturen
    },
    body: JSON.stringify({ section, data })
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      // Vernieuw token voor volgende opslag
      if (res.csrf_token) csrfToken = res.csrf_token;
      showToast('✓ Opgeslagen!', true);
    } else {
      showToast('Fout: ' + (res.error || 'onbekend'), false);
    }
  })
  .catch(() => showToast('Verbindingsfout', false));
}
</script>

</body>
</html>
