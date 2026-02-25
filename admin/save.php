<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

// ── 1. Auth guard ────────────────────────────────────────────────────────────
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Niet ingelogd']);
    exit;
}

// ── 2. CSRF-token verificatie ────────────────────────────────────────────────
$sentToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$sessToken = $_SESSION['csrf_token']       ?? '';

if (!$sentToken || !$sessToken || !hash_equals($sessToken, $sentToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Ongeldige CSRF-token']);
    exit;
}

// ── 3. Vernieuw CSRF-token na gebruik (rotation) ─────────────────────────────
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// ── 4. Invoer parsen ─────────────────────────────────────────────────────────
$body    = file_get_contents('php://input');
$payload = json_decode($body, true);

if (!$payload || !isset($payload['section'], $payload['data'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ongeldige invoer']);
    exit;
}

$section = $payload['section'];
$data    = $payload['data'];

// ── 5. Whitelist secties ──────────────────────────────────────────────────────
$allowed = ['hero', 'missie', 'trainingen', 'team', 'contact'];
if (!in_array($section, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ongeldige sectie']);
    exit;
}

// ── 6. Data structureren (alleen bekende sleutels doorlaten) ──────────────────
switch ($section) {
    case 'hero':
        $save = [
            'badge'        => (string)($data['badge']        ?? ''),
            'title'        => (string)($data['title']        ?? ''),
            'title_accent' => (string)($data['title_accent'] ?? ''),
            'subtitle'     => (string)($data['subtitle']     ?? ''),
            'stat1_value'  => (string)($data['stat1_value']  ?? ''),
            'stat1_label'  => (string)($data['stat1_label']  ?? ''),
            'stat2_value'  => (string)($data['stat2_value']  ?? ''),
            'stat2_label'  => (string)($data['stat2_label']  ?? ''),
            'stat3_value'  => (string)($data['stat3_value']  ?? ''),
            'stat3_label'  => (string)($data['stat3_label']  ?? ''),
        ];
        break;

    case 'missie':
        $save = [
            'missie_title'  => (string)($data['missie_title']  ?? ''),
            'missie_text'   => (string)($data['missie_text']   ?? ''),
            'visie_title'   => (string)($data['visie_title']   ?? ''),
            'visie_text'    => (string)($data['visie_text']    ?? ''),
            'waarden_title' => (string)($data['waarden_title'] ?? ''),
            'waarden_text'  => (string)($data['waarden_text']  ?? ''),
        ];
        break;

    case 'trainingen':
        $trainingen = [];
        foreach (($data['trainingen'] ?? []) as $t) {
            // Whitelist kleur-waarden
            $kleurAllowed = ['blue', 'violet', 'emerald', 'amber', 'rose', 'cyan', 'orange'];
            $kleur = in_array($t['kleur'] ?? '', $kleurAllowed, true) ? $t['kleur'] : 'blue';

            // Topics: array van strings, max 10 items, max 100 tekens per item
            $topics = [];
            foreach ((is_array($t['topics']) ? $t['topics'] : []) as $topic) {
                $topics[] = mb_substr((string)$topic, 0, 100);
                if (count($topics) >= 10) break;
            }

            $trainingen[] = [
                'vak'    => mb_substr((string)($t['vak']    ?? ''), 0, 100),
                'niveau' => mb_substr((string)($t['niveau'] ?? ''), 0, 100),
                'duur'   => mb_substr((string)($t['duur']   ?? ''), 0, 100),
                'groep'  => mb_substr((string)($t['groep']  ?? ''), 0, 100),
                'kleur'  => $kleur,
                'topics' => $topics,
            ];
        }
        $save = ['trainingen' => $trainingen];
        break;

    case 'team':
        $kleurAllowed = ['blue', 'violet', 'emerald', 'amber', 'rose', 'cyan', 'orange'];
        $teamleden = [];
        foreach (($data['team'] ?? []) as $lid) {
            $kleur = in_array($lid['kleur'] ?? '', $kleurAllowed, true) ? $lid['kleur'] : 'blue';
            $teamleden[] = [
                'naam'      => mb_substr((string)($lid['naam']      ?? ''), 0, 100),
                'initialen' => mb_substr((string)($lid['initialen'] ?? ''), 0, 5),
                'rol'       => mb_substr((string)($lid['rol']       ?? ''), 0, 100),
                'studie'    => mb_substr((string)($lid['studie']    ?? ''), 0, 100),
                'kleur'     => $kleur,
            ];
        }
        $save = ['teamleden' => $teamleden];
        break;

    case 'contact':
        // Valideer e-mailadres
        $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $save = [
            'heading' => mb_substr((string)($data['heading'] ?? ''), 0, 200),
            'subtext' => mb_substr((string)($data['subtext'] ?? ''), 0, 1000),
            'email'   => $email ?: '',
            'locatie' => mb_substr((string)($data['locatie'] ?? ''), 0, 200),
        ];
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Onbekende sectie']);
        exit;
}

// ── 7. Schrijf JSON ───────────────────────────────────────────────────────────
$path = CONTENT_DIR . $section . '.json';
$json = json_encode($save, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents($path, $json, LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Kan bestand niet schrijven.']);
    exit;
}

// Geef nieuw CSRF-token terug zodat de pagina verder kan opslaan
echo json_encode(['success' => true, 'csrf_token' => $_SESSION['csrf_token']]);
