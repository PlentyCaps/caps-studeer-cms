<?php
// =============================================
//  StudeerSamen CMS — Configuratie
//  Wijzig het wachtwoord vóór livegang!
// =============================================

define('ADMIN_USER',  'admin');

// Standaard wachtwoord: StudeerSamen2026!
// Verander dit: vervang de waarde hieronder met output van:
//   php -r "echo password_hash('JouwNieuwWachtwoord', PASSWORD_DEFAULT);"
define('ADMIN_PASS',  'StudeerSamen2026!');

define('SITE_TITLE',  'Stichting StudeerSamen');
define('CONTENT_DIR', __DIR__ . '/content/');

// Sessie beveiligingsinstellingen
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
