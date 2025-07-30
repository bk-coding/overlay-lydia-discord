<?php
/**
 * Script de mise à jour de la cagnotte Lydia
 * Récupère le montant depuis la page Lydia et met à jour data.json
 * Envoie des notifications Discord en cas de changement
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Chargement de la configuration centralisée
$config = require_once __DIR__ . '/config.php';

// Inclusion du système Discord
require_once __DIR__ . '/discord.php';

// Récupération des paramètres depuis la configuration
$url = $config['lydia']['url'];
$objectif = $config['lydia']['objectif'];
$DISCORD_WEBHOOK_URL = $config['discord']['webhook_url'];

/**
 * Fonction pour récupérer le contenu de la page Lydia
 * @param string $url URL de la cagnotte
 * @return array Résultat avec le HTML ou une erreur
 */
function recupererPageLydia($url) {
    global $config;
    
    $ch = curl_init();
    if ($ch === false) {
        return ['success' => false, 'error' => 'Erreur d\'initialisation cURL'];
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $config['technique']['user_agent']);
    curl_setopt($ch, CURLOPT_TIMEOUT, $config['technique']['timeout_curl']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($html === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['success' => false, 'error' => 'Erreur cURL: ' . $error];
    }

    curl_close($ch);

    if ($httpCode !== 200) {
        return ['success' => false, 'error' => 'Erreur HTTP: ' . $httpCode];
    }

    return ['success' => true, 'html' => $html];
}

/**
 * Fonction pour extraire le montant depuis le HTML
 * @param string $html Contenu HTML de la page
 * @return float Montant extrait
 */
function extraireMontant($html) {
    // Utilisation d'une expression régulière pour extraire le montant
    // Le HTML réel a des retours à la ligne, donc on utilise une regex plus flexible
    preg_match('/collected-amount-label[^>]*>([^<]+)/', $html, $matches);

    if (!isset($matches[1])) {
        return 0;
    }

    $amount = trim($matches[1]);
    // Nettoyage du montant pour ne garder que les chiffres et séparateurs décimaux
    $amount = preg_replace('/[^0-9,.]/', '', $amount);
    // Conversion en format numérique (gestion du format français et anglais)
    $amount = str_replace(',', '.', $amount);
    
    return floatval($amount);
}

// Récupération de la page Lydia
$resultat = recupererPageLydia($url);

if (!$resultat['success']) {
    http_response_code(500);
    echo json_encode(['error' => $resultat['error'], 'success' => false]);
    exit;
}

// Extraction du montant
$amount = extraireMontant($resultat['html']);

// Initialisation du système Discord si l'URL est configurée
$discordNotification = false;
if ($config['discord']['actif'] && !empty($DISCORD_WEBHOOK_URL) && strpos($DISCORD_WEBHOOK_URL, "VOTRE_WEBHOOK") === false) {
    $discord = new DiscordWebhook($DISCORD_WEBHOOK_URL, __DIR__ . '/' . $config['technique']['fichier_donnees']);
    
    // Sauvegarde avec notification Discord automatique
    $discordNotification = $discord->sauvegarderAvecNotification($amount, $objectif);
} else {
    // Sauvegarde classique sans Discord
    $data = [
        'montant' => $amount,
        'objectif' => $objectif,
        'derniere_maj' => date('Y-m-d H:i:s')
    ];
    
    if (file_put_contents(__DIR__ . '/' . $config['technique']['fichier_donnees'], json_encode($data, JSON_PRETTY_PRINT)) === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la sauvegarde des données', 'success' => false]);
        exit;
    }
}

// Réponse de succès
echo json_encode([
    'amount' => $amount,
    'formatted_amount' => number_format($amount, 2, ',', ' ') . ' €',
    'objectif' => $objectif,
    'pourcentage' => $objectif > 0 ? round(($amount / $objectif) * 100, 2) : 0,
    'derniere_maj' => date('Y-m-d H:i:s'),
    'discord_notification' => $discordNotification,
    'success' => true
]);
?>