<?php
require_once __DIR__ . '/config.php';

// Load all content
function loadContent(string $file): array {
    $path = CONTENT_DIR . $file . '.json';
    if (!file_exists($path)) return [];
    return json_decode(file_get_contents($path), true) ?? [];
}

$hero      = loadContent('hero');
$missie    = loadContent('missie');
$train     = loadContent('trainingen');
$team      = loadContent('team');
$contact   = loadContent('contact');

// Handle contact form submission
$formSuccess = false;
$formError   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $naam    = htmlspecialchars(trim($_POST['naam']    ?? ''));
    $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $school  = htmlspecialchars(trim($_POST['school']  ?? ''));
    $bericht = htmlspecialchars(trim($_POST['bericht'] ?? ''));

    if (!$naam || !$email || !$bericht) {
        $formError = 'Vul alle verplichte velden in.';
    } else {
        $to      = $contact['email'] ?? 'info@stichtingstudeersamen.nl';
        $subject = "Nieuw contactformulier van {$naam}";
        $body    = "Naam: {$naam}\nE-mail: {$email}\nSchool/Org: {$school}\n\nBericht:\n{$bericht}";
        $headers = "From: {$email}\r\nReply-To: {$email}";
        @mail($to, $subject, $body, $headers);
        $formSuccess = true;
    }
}

// Helper: safely echo content
function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$teamKleuren = [
  'blue'    => ['bg' => 'background:#dbeafe', 'text' => 'color:#1d4ed8'],
  'violet'  => ['bg' => 'background:#ede9fe', 'text' => 'color:#7c3aed'],
  'emerald' => ['bg' => 'background:#d1fae5', 'text' => 'color:#059669'],
  'amber'   => ['bg' => 'background:#fef3c7', 'text' => 'color:#d97706'],
  'rose'    => ['bg' => 'background:#ffe4e6', 'text' => 'color:#e11d48'],
  'cyan'    => ['bg' => 'background:#cffafe', 'text' => 'color:#0891b2'],
  'orange'  => ['bg' => 'background:#ffedd5', 'text' => 'color:#ea580c'],
];

$trainKleuren = [
  'blue'    => ['accent' => '#3b82f6', 'badge_bg' => '#eff6ff', 'badge_text' => '#1d4ed8', 'check' => '#3b82f6'],
  'violet'  => ['accent' => '#8b5cf6', 'badge_bg' => '#f5f3ff', 'badge_text' => '#7c3aed', 'check' => '#8b5cf6'],
  'emerald' => ['accent' => '#10b981', 'badge_bg' => '#ecfdf5', 'badge_text' => '#059669', 'check' => '#10b981'],
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e(SITE_TITLE) ?> – Samen voor gelijke onderwijskansen</title>
  <meta name="description" content="StudeerSamen biedt gratis examentraining aan middelbare scholieren, gegeven door universitaire studenten.">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- AOS -->
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <!-- Alpine.js -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'brand': '#1d4ed8',
          }
        }
      }
    }
  </script>

  <style>
    html { scroll-behavior: smooth; }
    body { font-family: 'Inter', system-ui, -apple-system, sans-serif; -webkit-font-smoothing: antialiased; }

    /* Animated blobs */
    @keyframes blob1 {
      0%, 100% { transform: translate(0,0) scale(1); }
      33%       { transform: translate(30px,-20px) scale(1.08); }
      66%       { transform: translate(-15px,15px) scale(0.95); }
    }
    @keyframes blob2 {
      0%, 100% { transform: translate(0,0) scale(1); }
      33%       { transform: translate(-25px,30px) scale(1.12); }
      66%       { transform: translate(20px,-10px) scale(0.97); }
    }
    @keyframes blob3 {
      0%, 100% { transform: translate(0,0) scale(1); }
      50%       { transform: translate(15px,20px) scale(1.06); }
    }
    @keyframes bounce-y {
      0%, 100% { transform: translateY(0); }
      50%       { transform: translateY(8px); }
    }
    @keyframes pulse-dot {
      0%, 100% { opacity: 1; }
      50%       { opacity: 0.4; }
    }

    .blob1 { animation: blob1 12s ease-in-out infinite; }
    .blob2 { animation: blob2 16s ease-in-out infinite 2s; }
    .blob3 { animation: blob3 10s ease-in-out infinite 4s; }
    .bounce-y { animation: bounce-y 1.5s ease-in-out infinite; }
    .pulse-dot { animation: pulse-dot 1.5s ease-in-out infinite; }

    /* Gradient text */
    .gradient-text {
      background: linear-gradient(135deg, #60a5fa, #818cf8);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .gradient-text-dark {
      background: linear-gradient(135deg, #1d4ed8, #7c3aed);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Navbar glass */
    .navbar-glass {
      background: rgba(255,255,255,0.92);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(0,0,0,0.06);
      box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }

    /* Card hover */
    .card-hover {
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .card-hover:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.10);
    }

    /* Training card top accent */
    .training-accent {
      position: absolute;
      top: 0; left: 2rem; right: 2rem;
      height: 3px;
      border-radius: 0 0 4px 4px;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #1d4ed8; }
  </style>
</head>
<body class="bg-white text-slate-900">

<!-- ===================== NAVBAR ===================== -->
<header
  id="navbar"
  x-data="{ open: false, scrolled: false }"
  x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 })"
  :class="scrolled ? 'navbar-glass' : 'bg-transparent'"
  class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16 md:h-20">

      <!-- Logo -->
      <a href="#" class="flex items-center gap-2.5 group">
        <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center group-hover:bg-blue-700 transition-colors">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <span class="font-bold text-slate-900 text-sm leading-tight">
          Stichting<br>
          <span class="text-blue-600 font-semibold">StudeerSamen</span>
        </span>
      </a>

      <!-- Desktop nav -->
      <nav class="hidden md:flex items-center gap-1">
        <a href="#missie"     class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">Missie &amp; Visie</a>
        <a href="#trainingen" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">Trainingen</a>
        <a href="#team"       class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">Ons Team</a>
        <a href="#contact"    class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">Contact</a>
        <a href="#contact"    class="ml-3 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-all shadow-md hover:shadow-blue-200 hover:shadow-lg">Aanmelden</a>
      </nav>

      <!-- Mobile toggle -->
      <button @click="open = !open" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
        <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <svg x-show="open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Mobile menu -->
  <div x-show="open" x-transition class="md:hidden bg-white border-b border-slate-100 shadow-lg">
    <nav class="flex flex-col p-4 gap-1">
      <a href="#missie"     @click="open=false" class="px-4 py-3 text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Missie &amp; Visie</a>
      <a href="#trainingen" @click="open=false" class="px-4 py-3 text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Trainingen</a>
      <a href="#team"       @click="open=false" class="px-4 py-3 text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Ons Team</a>
      <a href="#contact"    @click="open=false" class="px-4 py-3 text-sm font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Contact</a>
      <a href="#contact"    @click="open=false" class="mt-2 px-4 py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl text-center hover:bg-blue-700">Aanmelden</a>
    </nav>
  </div>
</header>

<!-- ===================== HERO ===================== -->
<section class="relative min-h-screen flex items-center overflow-hidden bg-slate-950">

  <!-- Background gradient -->
  <div class="absolute inset-0 bg-gradient-to-br from-blue-950 via-slate-950 to-violet-950"></div>

  <!-- Grid overlay -->
  <div class="absolute inset-0 opacity-[0.03]"
    style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 60px 60px;">
  </div>

  <!-- Animated blobs -->
  <div class="blob1 absolute top-1/4 left-1/4 w-96 h-96 bg-blue-600 rounded-full opacity-20" style="filter:blur(80px)"></div>
  <div class="blob2 absolute bottom-1/4 right-1/4 w-80 h-80 bg-violet-600 rounded-full opacity-20" style="filter:blur(80px)"></div>
  <div class="blob3 absolute top-1/3 right-1/3 w-64 h-64 bg-cyan-600 rounded-full opacity-15" style="filter:blur(80px)"></div>

  <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-24 pt-36 w-full">
    <div class="text-center">

      <!-- Badge -->
      <div class="inline-flex items-center gap-2 px-4 py-2 mb-8 rounded-full text-blue-300 text-sm font-medium"
        style="background:rgba(29,78,216,0.2); border:1px solid rgba(96,165,250,0.3)">
        <span class="pulse-dot w-2 h-2 bg-blue-400 rounded-full inline-block"></span>
        <?= e($hero['badge'] ?? 'Aanmeldingen open voor 2026') ?>
      </div>

      <!-- Headline -->
      <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight tracking-tight">
        <?= e($hero['title'] ?? 'Samen Studeren,') ?><br>
        <span class="gradient-text"><?= e($hero['title_accent'] ?? 'Samen Slagen') ?></span>
      </h1>

      <!-- Subtext -->
      <p class="text-lg sm:text-xl text-slate-300 max-w-2xl mx-auto mb-10 leading-relaxed">
        <?= e($hero['subtitle'] ?? '') ?>
      </p>

      <!-- CTA Buttons -->
      <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-20">
        <a href="#trainingen" class="group px-8 py-4 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-2xl transition-all shadow-xl hover:shadow-blue-500/30 text-base">
          Bekijk trainingen <span class="inline-block ml-1 transition-transform group-hover:translate-x-1">→</span>
        </a>
        <a href="#missie" class="px-8 py-4 font-semibold rounded-2xl transition-all text-base"
          style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:white; backdrop-filter:blur(8px)">
          Onze missie
        </a>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-2xl mx-auto">
        <?php
        $stats = [
          ['value' => $hero['stat1_value'] ?? '100+',   'label' => $hero['stat1_label'] ?? 'Leerlingen geholpen',      'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z'],
          ['value' => $hero['stat2_value'] ?? '7',       'label' => $hero['stat2_label'] ?? 'Universitaire studenten',   'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
          ['value' => $hero['stat3_value'] ?? 'Gratis',  'label' => $hero['stat3_label'] ?? 'Examentraining',            'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
        ];
        foreach ($stats as $s): ?>
        <div class="flex flex-col items-center gap-2 p-5 rounded-2xl transition-colors"
          style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1)">
          <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#60a5fa">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $s['icon'] ?>"/>
          </svg>
          <span class="text-2xl font-bold text-white"><?= e($s['value']) ?></span>
          <span class="text-sm text-slate-400"><?= e($s['label']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Scroll indicator -->
  <a href="#missie" class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center gap-2 text-slate-400 hover:text-white transition-colors">
    <span class="text-xs font-medium tracking-widest uppercase">Scroll</span>
    <span class="bounce-y block">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </span>
  </a>
</section>

<!-- ===================== MISSIE & VISIE ===================== -->
<section id="missie" class="py-24 lg:py-32 bg-white">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="text-center mb-16" data-aos="fade-up">
      <p class="text-blue-600 font-semibold text-sm tracking-widest uppercase mb-3">Wie zijn wij</p>
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 mb-5">
        Missie, Visie <span class="gradient-text-dark">&amp; Waarden</span>
      </h2>
      <p class="text-slate-500 text-lg max-w-2xl mx-auto">
        Wij geloven dat gelijke kansen in het onderwijs geen luxe zijn, maar een basisrecht.
      </p>
    </div>

    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php
      $missieCards = [
        [
          'title' => $missie['missie_title'] ?? 'Onze Missie',
          'text'  => $missie['missie_text']  ?? '',
          'bg'    => '#eff6ff', 'icon_bg' => '#dbeafe', 'icon_color' => '#1d4ed8', 'border' => '#bfdbfe',
          'delay' => '0',
          'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        ],
        [
          'title' => $missie['visie_title'] ?? 'Onze Visie',
          'text'  => $missie['visie_text']  ?? '',
          'bg'    => '#f5f3ff', 'icon_bg' => '#ede9fe', 'icon_color' => '#7c3aed', 'border' => '#ddd6fe',
          'delay' => '100',
          'icon'  => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
        ],
        [
          'title' => $missie['waarden_title'] ?? 'Onze Waarden',
          'text'  => $missie['waarden_text'] ?? '',
          'bg'    => '#fff1f2', 'icon_bg' => '#ffe4e6', 'icon_color' => '#e11d48', 'border' => '#fecdd3',
          'delay' => '200',
          'icon'  => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        ],
      ];
      foreach ($missieCards as $card): ?>
      <div class="card-hover p-8 rounded-3xl"
        style="background:<?= $card['bg'] ?>; border:1px solid <?= $card['border'] ?>"
        data-aos="fade-up" data-aos-delay="<?= $card['delay'] ?>">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-5"
          style="background:<?= $card['icon_bg'] ?>">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            style="color:<?= $card['icon_color'] ?>">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $card['icon'] ?>"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-3"><?= e($card['title']) ?></h3>
        <p class="text-slate-600 leading-relaxed"><?= e($card['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== TRAININGEN ===================== -->
<section id="trainingen" class="py-24 lg:py-32 bg-slate-50">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="text-center mb-16" data-aos="fade-up">
      <p class="text-blue-600 font-semibold text-sm tracking-widest uppercase mb-3">Ons aanbod</p>
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 mb-5">
        Gratis <span class="gradient-text-dark">Examentrainingen</span>
      </h2>
      <p class="text-slate-500 text-lg max-w-2xl mx-auto">
        Klein, intensief en persoonlijk. Gegeven door universitaire studenten met een maatschappelijke motivatie.
      </p>
    </div>

    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php
      $trainingen = $train['trainingen'] ?? [];
      foreach ($trainingen as $i => $t):
        $kl = $trainKleuren[$t['kleur']] ?? $trainKleuren['blue'];
        $delay = $i * 100;
      ?>
      <div class="relative bg-white border-2 border-slate-100 rounded-3xl p-8 card-hover"
        data-aos="fade-up" data-aos-delay="<?= $delay ?>">

        <!-- Top accent -->
        <div class="training-accent" style="background:<?= $kl['accent'] ?>"></div>

        <h3 class="text-2xl font-bold text-slate-900 mb-1 mt-3"><?= e($t['vak']) ?></h3>
        <span class="inline-block px-3 py-1 text-xs font-semibold border rounded-full mb-6"
          style="background:<?= $kl['badge_bg'] ?>; color:<?= $kl['badge_text'] ?>; border-color:<?= $kl['badge_bg'] ?>">
          <?= e($t['niveau']) ?>
        </span>

        <!-- Meta -->
        <div class="flex flex-col gap-3 mb-7 text-sm text-slate-600">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <?= e($t['duur']) ?>
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <?= e($t['groep']) ?>
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Volledig gratis
          </div>
        </div>

        <!-- Topics -->
        <ul class="space-y-2 mb-8">
          <?php foreach (($t['topics'] ?? []) as $topic): ?>
          <li class="flex items-center gap-2 text-sm text-slate-700">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              style="color:<?= $kl['check'] ?>">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <?= e($topic) ?>
          </li>
          <?php endforeach; ?>
        </ul>

        <!-- CTA -->
        <a href="#contact"
          class="group flex items-center justify-center gap-2 w-full py-3 px-5 border-2 font-semibold rounded-2xl transition-all text-sm text-slate-800 hover:bg-slate-50"
          style="border-color:<?= $kl['accent'] ?>">
          Aanmelden
          <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <p class="text-center text-slate-400 text-sm mt-10" data-aos="fade-up" data-aos-delay="300">
      * Trainingen worden gegeven in de regio Groningen. Nieuwe locaties volgen.
    </p>
  </div>
</section>

<!-- ===================== TEAM ===================== -->
<section id="team" class="py-24 lg:py-32 bg-white">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="text-center mb-16" data-aos="fade-up">
      <p class="text-blue-600 font-semibold text-sm tracking-widest uppercase mb-3">De mensen achter StudeerSamen</p>
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 mb-5">
        Ons <span class="gradient-text-dark">Team</span>
      </h2>
      <p class="text-slate-500 text-lg max-w-2xl mx-auto">
        Zeven gepassioneerde studenten van de Rijksuniversiteit Groningen die geloven in gelijke kansen voor iedereen.
      </p>
    </div>

    <!-- Team grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      <?php
      $teamleden = $team['teamleden'] ?? [];
      foreach ($teamleden as $i => $lid):
        $kl = $teamKleuren[$lid['kleur']] ?? $teamKleuren['blue'];
        $delay = $i * 80;
      ?>
      <div class="card-hover flex flex-col items-center text-center p-6 bg-white rounded-3xl border border-slate-100 hover:border-slate-200"
        data-aos="fade-up" data-aos-delay="<?= $delay ?>">
        <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-4 text-2xl font-bold"
          style="<?= $kl['bg'] ?>; <?= $kl['text'] ?>">
          <?= e($lid['initialen']) ?>
        </div>
        <h3 class="font-bold text-slate-900 text-base mb-0.5"><?= e($lid['naam']) ?></h3>
        <p class="text-blue-600 font-semibold text-sm mb-1"><?= e($lid['rol']) ?></p>
        <p class="text-slate-400 text-xs"><?= e($lid['studie']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- RUG badge -->
    <div class="flex items-center justify-center gap-3 mt-12 p-5 bg-slate-50 rounded-2xl max-w-sm mx-auto border border-slate-100" data-aos="fade-up" data-aos-delay="600">
      <div class="w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center text-white font-bold text-xs">RUG</div>
      <div class="text-left">
        <p class="font-semibold text-slate-800 text-sm">Rijksuniversiteit Groningen</p>
        <p class="text-slate-400 text-xs">Alle teamleden zijn RUG-studenten</p>
      </div>
    </div>
  </div>
</section>

<!-- ===================== CONTACT ===================== -->
<section id="contact" class="py-24 lg:py-32 bg-slate-950">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">

      <!-- Left: Info -->
      <div data-aos="fade-right">
        <p class="font-semibold text-sm tracking-widest uppercase mb-3" style="color:#60a5fa">Neem contact op</p>
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-5">
          <?= e($contact['heading'] ?? 'Klaar om Samen te Studeren?') ?><br>
          <span class="gradient-text"><?php /* empty */ ?></span>
        </h2>
        <p class="text-lg leading-relaxed mb-10" style="color:#94a3b8">
          <?= e($contact['subtext'] ?? '') ?>
        </p>

        <!-- Contact items -->
        <div class="space-y-4">
          <?php if (!empty($contact['email'])): ?>
          <a href="mailto:<?= e($contact['email']) ?>"
            class="flex items-center gap-4 p-4 rounded-2xl transition-colors group"
            style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1)">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center group-hover:opacity-80 transition-opacity"
              style="background:rgba(29,78,216,0.3)">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#60a5fa">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
            <div>
              <p class="text-xs mb-0.5" style="color:#64748b">E-mail</p>
              <p class="font-medium text-sm text-white"><?= e($contact['email']) ?></p>
            </div>
          </a>
          <?php endif; ?>

          <?php if (!empty($contact['locatie'])): ?>
          <div class="flex items-center gap-4 p-4 rounded-2xl"
            style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1)">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(124,58,237,0.3)">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#a78bfa">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <div>
              <p class="text-xs mb-0.5" style="color:#64748b">Locatie</p>
              <p class="font-medium text-sm text-white"><?= e($contact['locatie']) ?></p>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right: Form -->
      <div data-aos="fade-left" data-aos-delay="100">
        <?php if ($formSuccess): ?>
        <div class="flex flex-col items-center justify-center text-center p-12 rounded-3xl min-h-96"
          style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1)">
          <svg class="w-16 h-16 mb-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#34d399">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <h3 class="text-2xl font-bold text-white mb-3">Bericht verzonden!</h3>
          <p style="color:#94a3b8">Bedankt voor je bericht. We nemen zo snel mogelijk contact met je op.</p>
        </div>
        <?php else: ?>
        <form method="POST" action="#contact" class="p-8 rounded-3xl space-y-5"
          style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1)">

          <?php if ($formError): ?>
          <div class="p-4 rounded-xl text-sm" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.3)">
            <?= e($formError) ?>
          </div>
          <?php endif; ?>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2" style="color:#cbd5e1">Naam *</label>
              <input type="text" name="naam" required placeholder="Jouw naam"
                value="<?= e($_POST['naam'] ?? '') ?>"
                class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none transition-all"
                style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.1)">
            </div>
            <div>
              <label class="block text-sm font-medium mb-2" style="color:#cbd5e1">E-mail *</label>
              <input type="email" name="email" required placeholder="jouw@email.nl"
                value="<?= e($_POST['email'] ?? '') ?>"
                class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none transition-all"
                style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.1)">
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" style="color:#cbd5e1">School / Organisatie</label>
            <input type="text" name="school" placeholder="Naam van je school"
              value="<?= e($_POST['school'] ?? '') ?>"
              class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none transition-all"
              style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.1)">
          </div>

          <div>
            <label class="block text-sm font-medium mb-2" style="color:#cbd5e1">Bericht *</label>
            <textarea name="bericht" required rows="5" placeholder="Vertel ons meer..."
              class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none transition-all resize-none"
              style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.1)"><?= e($_POST['bericht'] ?? '') ?></textarea>
          </div>

          <button type="submit" name="contact_submit"
            class="group flex items-center justify-center gap-2 w-full py-4 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl transition-all shadow-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Verstuur bericht
            <span class="inline-block transition-transform group-hover:translate-x-1">→</span>
          </button>

          <p class="text-center text-xs" style="color:#475569">We reageren doorgaans binnen 2–3 werkdagen.</p>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ===================== FOOTER ===================== -->
<footer class="bg-slate-950 py-10" style="border-top:1px solid rgba(255,255,255,0.05)">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <span class="text-white font-semibold text-sm">Stichting StudeerSamen</span>
      </div>

      <nav class="flex items-center gap-6">
        <a href="#missie"     class="text-sm transition-colors" style="color:#64748b" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#64748b'">Missie</a>
        <a href="#trainingen" class="text-sm transition-colors" style="color:#64748b" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#64748b'">Trainingen</a>
        <a href="#team"       class="text-sm transition-colors" style="color:#64748b" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#64748b'">Team</a>
        <a href="#contact"    class="text-sm transition-colors" style="color:#64748b" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#64748b'">Contact</a>
      </nav>

      <p class="text-sm" style="color:#475569">© <?= date('Y') ?> Stichting StudeerSamen</p>
    </div>
  </div>
</footer>

<!-- AOS Init -->
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({ duration: 600, easing: 'ease-out-cubic', once: true, offset: 60 });
</script>

</body>
</html>
