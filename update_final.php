<?php
/**
 * Script de mise à jour de la cagnotte Lydia
 * Récupère le montant depuis la page Lydia et met à jour data.json
 * Version sans Discord pour éviter les erreurs
 */

// Suppression de l'affichage des erreurs pour éviter les problèmes avec les headers JSON
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Chargement de la configuration centralisée
$config = require_once __DIR__ . '/config.php';

// Vérification que la configuration est bien chargée
if (!$config || !is_array($config)) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de chargement de la configuration', 'success' => false]);
    exit;
}

// Récupération des paramètres depuis la configuration avec vérifications
$url = isset($config['lydia']['url']) ? $config['lydia']['url'] : '';
$objectif = isset($config['lydia']['objectif']) ? $config['lydia']['objectif'] : 0;

// Vérification que l'URL est valide
if (empty($url)) {
    http_response_code(500);
    echo json_encode(['error' => 'URL Lydia non configurée', 'success' => false]);
    exit;
}

// Vérification du format de l'URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(500);
    echo json_encode(['error' => 'Format d\'URL invalide: ' . $url, 'success' => false]);
    exit;
}

/**
 * Fonction pour récupérer le contenu de la page Lydia
 * @param string $url URL de la cagnotte
 * @return array Résultat avec le HTML ou une erreur
 */
function recupererPageLydia($url) {
    global $config;
    
    // Vérification supplémentaire de l'URL
    if (empty($url) || !is_string($url)) {
        return ['success' => false, 'error' => 'URL vide ou invalide'];
    }
    
    // Nettoyage de l'URL (suppression des espaces)
    $url = trim($url);
    
    // Vérification du format de l'URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['success' => false, 'error' => 'Format d\'URL invalide: ' . $url];
    }
    
    $ch = curl_init();
    if ($ch === false) {
        return ['success' => false, 'error' => 'Erreur d\'initialisation cURL'];
    }

    // Configuration cURL avec plus d'options pour la compatibilité
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $config['technique']['user_agent']);
    curl_setopt($ch, CURLOPT_TIMEOUT, $config['technique']['timeout_curl']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Pour éviter les problèmes SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_ENCODING, ''); // Accepter tous les encodages

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    if ($html === false) {
        curl_close($ch);
        return ['success' => false, 'error' => 'Erreur cURL: ' . $curlError];
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

// Sauvegarde des données
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

// Réponse de succès
echo json_encode([
    'amount' => $amount,
    'formatted_amount' => number_format($amount, 2, ',', ' ') . ' €',
    'objectif' => $objectif,
    'pourcentage' => $objectif > 0 ? round(($amount / $objectif) * 100, 2) : 0,
    'derniere_maj' => date('Y-m-d H:i:s'),
    'discord_notification' => false,
    'success' => true
]);

// Forcer l'envoi de la sortie
if (ob_get_level()) {
    ob_end_flush();
}
flush();
?>