<?php
session_start();
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

// Auth guard
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Niet ingelogd']);
    exit;
}

// Parse JSON body
$body = file_get_contents('php://input');
$payload = json_decode($body, true);

if (!$payload || !isset($payload['section'], $payload['data'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ongeldige invoer']);
    exit;
}

$section = $payload['section'];
$data    = $payload['data'];

// Whitelist allowed sections
$allowed = ['hero', 'missie', 'trainingen', 'team', 'contact'];
if (!in_array($section, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ongeldige sectie']);
    exit;
}

// Build the correct JSON structure per section
switch ($section) {
    case 'hero':
        $save = [
            'badge'       => $data['badge']       ?? '',
            'title'       => $data['title']        ?? '',
            'title_accent'=> $data['title_accent'] ?? '',
            'subtitle'    => $data['subtitle']     ?? '',
            'stat1_value' => $data['stat1_value']  ?? '',
            'stat1_label' => $data['stat1_label']  ?? '',
            'stat2_value' => $data['stat2_value']  ?? '',
            'stat2_label' => $data['stat2_label']  ?? '',
            'stat3_value' => $data['stat3_value']  ?? '',
            'stat3_label' => $data['stat3_label']  ?? '',
        ];
        break;

    case 'missie':
        $save = [
            'missie_title'  => $data['missie_title']  ?? '',
            'missie_text'   => $data['missie_text']   ?? '',
            'visie_title'   => $data['visie_title']   ?? '',
            'visie_text'    => $data['visie_text']    ?? '',
            'waarden_title' => $data['waarden_title'] ?? '',
            'waarden_text'  => $data['waarden_text']  ?? '',
        ];
        break;

    case 'trainingen':
        $trainingen = [];
        foreach (($data['trainingen'] ?? []) as $t) {
            $trainingen[] = [
                'vak'    => $t['vak']    ?? '',
                'niveau' => $t['niveau'] ?? '',
                'duur'   => $t['duur']   ?? '',
                'groep'  => $t['groep']  ?? '',
                'kleur'  => $t['kleur']  ?? 'blue',
                'topics' => is_array($t['topics']) ? array_values($t['topics']) : [],
            ];
        }
        $save = ['trainingen' => $trainingen];
        break;

    case 'team':
        $teamleden = [];
        foreach (($data['team'] ?? []) as $lid) {
            $teamleden[] = [
                'naam'      => $lid['naam']      ?? '',
                'initialen' => $lid['initialen'] ?? '',
                'rol'       => $lid['rol']        ?? '',
                'studie'    => $lid['studie']     ?? '',
                'kleur'     => $lid['kleur']      ?? 'blue',
            ];
        }
        $save = ['teamleden' => $teamleden];
        break;

    case 'contact':
        $save = [
            'heading' => $data['heading'] ?? '',
            'subtext' => $data['subtext'] ?? '',
            'email'   => $data['email']   ?? '',
            'locatie' => $data['locatie'] ?? '',
        ];
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Onbekende sectie']);
        exit;
}

// Write JSON
$path = CONTENT_DIR . $section . '.json';
$json = json_encode($save, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents($path, $json) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Kan bestand niet schrijven. Controleer bestandsrechten.']);
    exit;
}

echo json_encode(['success' => true]);
