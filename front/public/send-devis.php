<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit();
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Données invalides']);
    exit();
}

// ── Extraction des données ──────────────────────────────────────────────────
$ct      = $data['ct']      ?? [];
$ev      = $data['ev']      ?? [];
$brazero = $data['brazero'] ?? null;
$carteAp = $data['carteAp'] ?? [];
$carteVi = $data['carteVi'] ?? [];
$traiteur= $data['traiteur']?? [];
$estimatif = $data['estimatif'] ?? '—';

$prenom = htmlspecialchars($ct['prenom'] ?? '');
$nom    = htmlspecialchars($ct['nom']    ?? '');
$email  = filter_var($ct['email'] ?? '', FILTER_VALIDATE_EMAIL);
$tel    = htmlspecialchars($ct['tel']   ?? '');

$typeEv  = htmlspecialchars($ev['type']   ?? '');
$date    = htmlspecialchars($ev['date']   ?? '');
$guests  = intval($ev['guests'] ?? 0);
$lieu    = htmlspecialchars($ev['lieu']   ?? '');
$note    = htmlspecialchars($ev['note']   ?? '');

if (!$email) {
    echo json_encode(['success' => false, 'error' => 'Email invalide']);
    exit();
}

// ── Format de la date ───────────────────────────────────────────────────────
$dateFormatee = $date;
if ($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if ($d) $dateFormatee = $d->format('d/m/Y');
}

// ── Construction de l'email HTML ────────────────────────────────────────────
$lignesBrazero = '';
if ($brazero) {
    $lignesBrazero .= "<tr><td style='padding:6px 0;color:#A0A0A0;'>Formule Brazéro</td><td style='padding:6px 0;font-weight:bold;color:#fff;'>$brazero</td></tr>";
}
if (!empty($carteAp)) {
    $lignesBrazero .= "<tr><td style='padding:6px 0;color:#A0A0A0;vertical-align:top;'>Apéritifs à la carte</td><td style='padding:6px 0;color:#fff;'>" . implode('<br>', array_map('htmlspecialchars', $carteAp)) . "</td></tr>";
}
if (!empty($carteVi)) {
    $lignesBrazero .= "<tr><td style='padding:6px 0;color:#A0A0A0;vertical-align:top;'>Viandes à la carte</td><td style='padding:6px 0;color:#fff;'>" . implode('<br>', array_map('htmlspecialchars', $carteVi)) . "</td></tr>";
}

$lignesTraiteur = '';
foreach ($traiteur as $t) {
    $tname = htmlspecialchars($t['name'] ?? '');
    $tqty  = intval($t['qty'] ?? 0);
    $lignesTraiteur .= "<tr><td style='padding:4px 0;color:#A0A0A0;'>$tname</td><td style='padding:4px 0;color:#fff;'>× $tqty</td></tr>";
}

$sectionTraiteur = $lignesTraiteur
    ? "<h3 style='color:#F5C518;font-size:13px;margin:20px 0 8px;text-transform:uppercase;letter-spacing:0.2em;'>Extras Traiteur</h3><table style='width:100%;border-collapse:collapse;'>$lignesTraiteur</table>"
    : '';

$sectionNote = $note
    ? "<h3 style='color:#F5C518;font-size:13px;margin:20px 0 8px;text-transform:uppercase;letter-spacing:0.2em;'>Note</h3><p style='color:#ccc;font-size:14px;margin:0;'>$note</p>"
    : '';

$html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#111;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#111;padding:40px 20px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#1A1A1A;border:1px solid rgba(255,255,255,0.08);max-width:600px;width:100%;">

        <!-- Header -->
        <tr>
          <td style="background:#F5C518;padding:24px 32px;">
            <p style="margin:0;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:0.4em;color:#1A1A1A;">Boucherie Chez Guillaume</p>
            <p style="margin:4px 0 0;font-size:22px;font-weight:900;color:#1A1A1A;">Nouvelle demande de devis</p>
          </td>
        </tr>

        <!-- Contact -->
        <tr>
          <td style="padding:28px 32px 0;">
            <h3 style="color:#F5C518;font-size:13px;margin:0 0 12px;text-transform:uppercase;letter-spacing:0.2em;">Contact</h3>
            <table style="width:100%;border-collapse:collapse;">
              <tr><td style="padding:6px 0;color:#A0A0A0;width:140px;">Nom</td><td style="padding:6px 0;font-weight:bold;color:#fff;">$prenom $nom</td></tr>
              <tr><td style="padding:6px 0;color:#A0A0A0;">Email</td><td style="padding:6px 0;"><a href="mailto:$email" style="color:#F5C518;">$email</a></td></tr>
              <tr><td style="padding:6px 0;color:#A0A0A0;">Téléphone</td><td style="padding:6px 0;"><a href="tel:$tel" style="color:#F5C518;">$tel</a></td></tr>
            </table>
          </td>
        </tr>

        <!-- Événement -->
        <tr>
          <td style="padding:20px 32px 0;">
            <h3 style="color:#F5C518;font-size:13px;margin:0 0 12px;text-transform:uppercase;letter-spacing:0.2em;">Événement</h3>
            <table style="width:100%;border-collapse:collapse;">
              <tr><td style="padding:6px 0;color:#A0A0A0;width:140px;">Type</td><td style="padding:6px 0;font-weight:bold;color:#fff;">$typeEv</td></tr>
              <tr><td style="padding:6px 0;color:#A0A0A0;">Date</td><td style="padding:6px 0;color:#fff;">$dateFormatee</td></tr>
              <tr><td style="padding:6px 0;color:#A0A0A0;">Convives</td><td style="padding:6px 0;color:#fff;">$guests personnes</td></tr>
              <tr><td style="padding:6px 0;color:#A0A0A0;">Lieu</td><td style="padding:6px 0;color:#fff;">$lieu</td></tr>
            </table>
          </td>
        </tr>

        <!-- Prestations -->
        <tr>
          <td style="padding:20px 32px 0;">
            <h3 style="color:#F5C518;font-size:13px;margin:0 0 12px;text-transform:uppercase;letter-spacing:0.2em;">Prestations</h3>
            <table style="width:100%;border-collapse:collapse;">$lignesBrazero</table>
          </td>
        </tr>

        <!-- Traiteur -->
        <tr><td style="padding:0 32px;">$sectionTraiteur</td></tr>

        <!-- Note -->
        <tr><td style="padding:0 32px;">$sectionNote</td></tr>

        <!-- Total estimatif -->
        <tr>
          <td style="padding:24px 32px;">
            <div style="background:#242424;border-left:3px solid #F5C518;padding:16px 20px;">
              <p style="margin:0 0 4px;font-size:11px;color:#A0A0A0;text-transform:uppercase;letter-spacing:0.3em;">Estimation indicative</p>
              <p style="margin:0;font-size:28px;font-weight:900;color:#F5C518;">$estimatif</p>
            </div>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="padding:20px 32px 32px;border-top:1px solid rgba(255,255,255,0.06);">
            <p style="margin:0;font-size:11px;color:#555;">Demande reçue depuis le site boucheriechezguillaume.fr</p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

// ── Envoi ───────────────────────────────────────────────────────────────────
$destinataire = 'contact@boucheriechezguillaume.fr';
$sujet        = "=?UTF-8?B?" . base64_encode("Nouveau devis – $prenom $nom – $typeEv ($guests pers.)") . "?=";
$headers      = implode("\r\n", [
    "From: Site Chez Guillaume <no-reply@boucheriechezguillaume.fr>",
    "Reply-To: $prenom $nom <$email>",
    "MIME-Version: 1.0",
    "Content-Type: text/html; charset=UTF-8",
    "X-Mailer: PHP/" . phpversion(),
]);

$ok = mail($destinataire, $sujet, $html, $headers);

echo json_encode(['success' => $ok]);
