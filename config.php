<?php
// =============================================
//  StudeerSamen CMS — Configuratie
// =============================================

define('ADMIN_USER',  'admin');
define('SITE_TITLE',  'Stichting StudeerSamen');
define('CONTENT_DIR', __DIR__ . '/content/');
define('PASS_FILE',   __DIR__ . '/content/.adminpass');

// Laad wachtwoord-hash uit beveiligd bestand
// Dit bestand wordt aangemaakt via admin/setup.php
function getAdminPassHash(): string {
    $file = PASS_FILE;
    if (file_exists($file)) {
        return trim(file_get_contents($file));
    }
    // Fallback: nog niet ingesteld — stuur door naar setup
    return '';
}

// Sessie beveiligingsinstellingen
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_secure',   '0'); // Zet op '1' als je HTTPS gebruikt (aanbevolen!)
ini_set('session.cookie_samesite', 'Strict');
