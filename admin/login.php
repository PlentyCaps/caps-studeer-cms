<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

// Al ingelogd
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

// Wachtwoord nog niet ingesteld → setup vereist
$hash = getAdminPassHash();
if ($hash === '') {
    header('Location: setup.php');
    exit;
}

// ── Loginpoging-limiet (opgeslagen in sessie per IP) ──────────────────────────
$ip         = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$lockFile   = sys_get_temp_dir() . '/ss_login_' . md5($ip) . '.json';
$maxAttempts = 5;
$lockMinutes = 15;

function getAttemptData(string $file): array {
    if (!file_exists($file)) return ['count' => 0, 'last' => 0];
    return json_decode(file_get_contents($file), true) ?? ['count' => 0, 'last' => 0];
}
function saveAttemptData(string $file, array $data): void {
    file_put_contents($file, json_encode($data), LOCK_EX);
}
function resetAttempts(string $file): void {
    @unlink($file);
}

$attempts = getAttemptData($lockFile);
$locked   = false;
$lockSecs = 0;

if ($attempts['count'] >= $maxAttempts) {
    $elapsed  = time() - $attempts['last'];
    $lockSecs = ($lockMinutes * 60) - $elapsed;
    if ($lockSecs > 0) {
        $locked = true;
    } else {
        // Lockout verlopen — reset
        resetAttempts($lockFile);
        $attempts = ['count' => 0, 'last' => 0];
    }
}

$error = '';

if (!$locked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($user === ADMIN_USER && password_verify($pass, $hash)) {
        // Succesvol ingelogd
        resetAttempts($lockFile);
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $user;
        // Genereer CSRF-token voor admin sessie
        $_SESSION['csrf_token']      = bin2hex(random_bytes(32));
        header('Location: dashboard.php');
        exit;
    }

    // Mislukte poging — registreer
    $attempts['count']++;
    $attempts['last'] = time();
    saveAttemptData($lockFile, $attempts);

    $remaining = $maxAttempts - $attempts['count'];
    if ($remaining > 0) {
        $error = "Onjuiste gebruikersnaam of wachtwoord. Nog {$remaining} poging(en) over.";
    } else {
        $locked   = true;
        $lockSecs = $lockMinutes * 60;
        $error    = '';
    }

    // Vertraag om geautomatiseerde aanvallen te remmen
    sleep(1);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — <?= htmlspecialchars(SITE_TITLE) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: system-ui, -apple-system, sans-serif; }
    @keyframes blob { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.08); } }
    .blob { animation: blob 8s ease-in-out infinite; }
  </style>
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center p-4">

  <div class="blob fixed top-1/4 left-1/4 w-96 h-96 bg-blue-600 rounded-full opacity-10" style="filter:blur(80px); pointer-events:none"></div>
  <div class="blob fixed bottom-1/4 right-1/4 w-80 h-80 bg-violet-600 rounded-full opacity-10" style="filter:blur(80px); pointer-events:none; animation-delay:3s"></div>

  <div class="relative z-10 w-full max-w-sm">
    <div class="flex flex-col items-center mb-8">
      <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mb-4 shadow-xl shadow-blue-500/30">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
      </div>
      <h1 class="text-white text-xl font-bold">StudeerSamen</h1>
      <p class="text-slate-400 text-sm mt-1">Admin omgeving</p>
    </div>

    <div class="rounded-3xl p-8" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); backdrop-filter:blur(12px)">

      <?php if ($locked): ?>
      <!-- Geblokkeerd -->
      <div class="text-center">
        <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#f59e0b">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <h2 class="text-white font-bold text-lg mb-2">Account geblokkeerd</h2>
        <p class="text-slate-400 text-sm mb-1">Te veel mislukte pogingen.</p>
        <p class="text-slate-400 text-sm">
          Probeer opnieuw over
          <span class="text-amber-400 font-semibold"><?= ceil($lockSecs / 60) ?> minuten</span>.
        </p>
      </div>

      <?php else: ?>
      <!-- Login formulier -->
      <?php if ($error): ?>
      <div class="mb-5 p-3 rounded-xl text-sm text-center" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.3)">
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Gebruikersnaam</label>
          <input type="text" name="username" required autofocus autocomplete="username"
            class="w-full px-4 py-3 rounded-xl text-sm focus:outline-none"
            style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:white"
            placeholder="admin">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Wachtwoord</label>
          <input type="password" name="password" required autocomplete="current-password"
            class="w-full px-4 py-3 rounded-xl text-sm focus:outline-none"
            style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:white"
            placeholder="••••••••••••">
        </div>
        <button type="submit"
          class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl transition-all shadow-lg mt-2">
          Inloggen →
        </button>
      </form>
      <?php endif; ?>
    </div>

    <p class="text-center text-slate-600 text-xs mt-6">
      <a href="../index.php" class="hover:text-slate-400 transition-colors">← Terug naar website</a>
    </p>
  </div>

</body>
</html>
