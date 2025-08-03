<?php
/**
 * Script de test pour les notifications Twitch
 * 
 * Ce script permet de tester la configuration Twitch et d'envoyer un message de test
 * sur le chat pour vérifier que tout fonctionne correctement.
 */

// Chargement de la configuration
$config = require_once __DIR__ . '/config.php';

// Vérification que la configuration est bien chargée
if (!$config || !is_array($config)) {
    echo "❌ Erreur : Impossible de charger la configuration\n";
    exit(1);
}

// Inclusion du système Twitch
require_once __DIR__ . '/twitch.php';

echo "🔍 Test de la configuration Twitch\n";
echo "==================================\n\n";

// Vérification de la configuration Twitch
if (!isset($config['twitch'])) {
    echo "❌ Erreur : Section 'twitch' manquante dans la configuration\n";
    exit(1);
}

$twitchConfig = $config['twitch'];

// Vérification que Twitch est activé
if (!$twitchConfig['actif']) {
    echo "⚠️  Twitch est désactivé dans la configuration\n";
    echo "   Changez 'actif' => true dans config.php pour l'activer\n";
    exit(0);
}

// Vérification des paramètres requis
$parametresRequis = [
    'client_id' => 'Client ID',
    'access_token' => 'Access Token',
    'broadcaster_id' => 'Broadcaster ID',
    'bot_user_id' => 'Bot User ID'
];

$parametresManquants = [];
foreach ($parametresRequis as $param => $nom) {
    if (empty($twitchConfig[$param]) || strpos($twitchConfig[$param], 'VOTRE_') !== false) {
        $parametresManquants[] = $nom . " ($param)";
    }
}

if (!empty($parametresManquants)) {
    echo "❌ Paramètres manquants ou non configurés :\n";
    foreach ($parametresManquants as $param) {
        echo "   - $param\n";
    }
    echo "\n📋 Veuillez configurer ces paramètres dans config.php\n";
    exit(1);
}

echo "✅ Configuration Twitch trouvée et complète\n\n";

// Création du bot Twitch
$twitchBot = creerBotTwitch($config, __DIR__ . '/' . $config['technique']['fichier_donnees']);

if ($twitchBot === null) {
    echo "❌ Erreur : Impossible de créer l'instance du bot Twitch\n";
    exit(1);
}

echo "✅ Instance du bot Twitch créée avec succès\n\n";

// Test de la connexion à l'API Twitch
echo "🔗 Test de la connexion à l'API Twitch...\n";
$testConnexion = $twitchBot->testerConnexion();

if (!$testConnexion['success']) {
    echo "❌ Erreur de connexion : " . $testConnexion['error'] . "\n";
    echo "\n🔧 Vérifications à effectuer :\n";
    echo "   1. Votre Access Token est-il valide et non expiré ?\n";
    echo "   2. Votre Client ID est-il correct ?\n";
    echo "   3. Le bot a-t-il les permissions nécessaires ?\n";
    echo "   4. Votre connexion internet fonctionne-t-elle ?\n";
    exit(1);
}

echo "✅ Connexion à l'API Twitch réussie !\n";
if (isset($testConnexion['user_info'])) {
    $userInfo = $testConnexion['user_info'];
    echo "   - Utilisateur : " . ($userInfo['login'] ?? 'N/A') . "\n";
    echo "   - Client ID : " . ($userInfo['client_id'] ?? 'N/A') . "\n";
    echo "   - Scopes : " . (isset($userInfo['scopes']) ? implode(', ', $userInfo['scopes']) : 'N/A') . "\n";
}
echo "\n";

// Demande de confirmation pour l'envoi du message de test
echo "🤖 Voulez-vous envoyer un message de test sur le chat Twitch ? (y/N) : ";
$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmation) !== 'y' && strtolower($confirmation) !== 'yes') {
    echo "Test annulé par l'utilisateur.\n";
    exit(0);
}

// Envoi du message de test
echo "\n📤 Envoi du message de test...\n";
$resultatTest = $twitchBot->envoyerMessageTest();

if (!$resultatTest['success']) {
    echo "❌ Erreur lors de l'envoi du message : " . $resultatTest['error'] . "\n";
    
    if (isset($resultatTest['http_code'])) {
        echo "   Code HTTP : " . $resultatTest['http_code'] . "\n";
    }
    
    echo "\n🔧 Vérifications supplémentaires :\n";
    echo "   1. Le bot a-t-il la permission 'user:write:chat' ?\n";
    echo "   2. Le Broadcaster ID correspond-il bien à votre chaîne ?\n";
    echo "   3. Le Bot User ID est-il correct ?\n";
    echo "   4. Le bot est-il modérateur de votre chaîne ?\n";
    exit(1);
}

echo "✅ Message de test envoyé avec succès !\n";
echo "   Vérifiez votre chat Twitch pour voir le message.\n\n";

echo "🎉 Configuration Twitch entièrement fonctionnelle !\n";
echo "\n📋 Résumé de la configuration :\n";
echo "   - Client ID : " . substr($twitchConfig['client_id'], 0, 8) . "...\n";
echo "   - Broadcaster ID : " . $twitchConfig['broadcaster_id'] . "\n";
echo "   - Bot User ID : " . $twitchConfig['bot_user_id'] . "\n";
echo "   - Message de contribution : " . $twitchConfig['message_contribution'] . "\n";

echo "\n🚀 Le système enverra automatiquement des messages lors des contributions à la cagnotte !\n";
?>