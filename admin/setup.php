<?php
// =============================================
//  StudeerSamen CMS — Eenmalige Setup
//  Verwijder dit bestand na gebruik!
// =============================================
require_once dirname(__DIR__) . '/config.php';

// Als wachtwoord al bestaat, blokkeer setup opnieuw uitvoeren
$alreadySet = (getAdminPassHash() !== '');

$success = false;
$error   = '';

if (!$alreadySet && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass1 = $_POST['password']  ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (strlen($pass1) < 10) {
        $error = 'Wachtwoord moet minimaal 10 tekens zijn.';
    } elseif ($pass1 !== $pass2) {
        $error = 'Wachtwoorden komen niet overeen.';
    } else {
        // Genereer bcrypt hash (cost factor 12)
        $hash = password_hash($pass1, PASSWORD_BCRYPT, ['cost' => 12]);

        // Sla op in beveiligd bestand
        if (file_put_contents(PASS_FILE, $hash) !== false) {
            $success = true;
            // Verwijder setup.php automatisch na gebruik
            @unlink(__FILE__);
        } else {
            $error = 'Kan wachtwoordbestand niet schrijven. Controleer bestandsrechten op content/.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Setup — <?= htmlspecialchars(SITE_TITLE) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: system-ui, -apple-system, sans-serif; }
  </style>
</head>
<body class="min-h-screen bg-slate-950 flex items-center justify-center p-4">

  <div class="relative z-10 w-full max-w-sm">
    <div class="flex flex-col items-center mb-8">
      <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mb-4 shadow-xl shadow-blue-500/30">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
      </div>
      <h1 class="text-white text-xl font-bold">StudeerSamen</h1>
      <p class="text-slate-400 text-sm mt-1">Eerste installatie</p>
    </div>

    <?php if ($alreadySet): ?>
    <!-- Wachtwoord al ingesteld -->
    <div class="rounded-3xl p-8 text-center" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1)">
      <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#f59e0b">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
      <h2 class="text-white font-bold text-lg mb-2">Setup al uitgevoerd</h2>
      <p class="text-slate-400 text-sm mb-6">Het admin-wachtwoord is al ingesteld. Verwijder dit bestand (<code class="text-blue-400">admin/setup.php</code>) van je server.</p>
      <a href="login.php" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl transition-all text-sm">
        Naar inlogpagina →
      </a>
    </div>

    <?php elseif ($success): ?>
    <!-- Succes -->
    <div class="rounded-3xl p-8 text-center" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1)">
      <svg class="w-14 h-14 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#34d399">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <h2 class="text-white font-bold text-xl mb-2">Wachtwoord ingesteld!</h2>
      <p class="text-slate-400 text-sm mb-2">Je wachtwoord is veilig opgeslagen als bcrypt-hash.</p>
      <p class="text-emerald-400 text-sm mb-6">
        ✓ Setup-pagina is automatisch verwijderd van de server.
      </p>
      <a href="login.php" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl transition-all text-sm">
        Naar inlogpagina →
      </a>
    </div>

    <?php else: ?>
    <!-- Setup formulier -->
    <div class="rounded-3xl p-8" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); backdrop-filter:blur(12px)">
      <h2 class="text-white font-bold text-lg mb-1">Admin wachtwoord instellen</h2>
      <p class="text-slate-400 text-sm mb-6">Kies een sterk wachtwoord (min. 10 tekens). Het wordt opgeslagen als versleutelde bcrypt-hash.</p>

      <?php if ($error): ?>
      <div class="mb-5 p-3 rounded-xl text-sm" style="background:rgba(239,68,68,0.2); color:#fca5a5; border:1px solid rgba(239,68,68,0.3)">
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Gebruikersnaam</label>
          <input type="text" disabled value="admin"
            class="w-full px-4 py-3 rounded-xl text-sm cursor-not-allowed"
            style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); color:#64748b">
          <p class="text-slate-600 text-xs mt-1">Gebruikersnaam is altijd "admin" (wijzigbaar in config.php)</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Wachtwoord *</label>
          <input type="password" name="password" required minlength="10" autocomplete="new-password"
            class="w-full px-4 py-3 rounded-xl text-sm focus:outline-none"
            style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:white"
            placeholder="Min. 10 tekens">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-2">Herhaal wachtwoord *</label>
          <input type="password" name="password2" required minlength="10" autocomplete="new-password"
            class="w-full px-4 py-3 rounded-xl text-sm focus:outline-none"
            style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:white"
            placeholder="Zelfde wachtwoord">
        </div>
        <button type="submit"
          class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl transition-all shadow-lg mt-2">
          Wachtwoord opslaan →
        </button>
      </form>
    </div>
    <?php endif; ?>
  </div>

</body>
</html>
