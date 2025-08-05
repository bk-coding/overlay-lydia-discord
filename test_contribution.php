<?php
/**
 * Script de test pour simuler une contribution à la cagnotte
 * 
 * Ce script permet de tester les notifications Twitch et Discord
 * en simulant une contribution sans avoir besoin d'une vraie donation.
 */

// Charger la configuration
$config = require_once __DIR__ . '/config.php';

// Vérification que la configuration est bien chargée
if (!$config || !is_array($config)) {
    die('<h1>❌ Erreur</h1><p>Impossible de charger la configuration depuis config.php</p>');
}

// Simuler des données de contribution
$montantActuel = 150.50;
$montantPrecedent = 125.75;
$objectif = $config['lydia']['objectif'];
$pourcentage = round(($montantActuel / $objectif) * 100, 1);

echo "<!DOCTYPE html>\n";
echo "<html lang='fr'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>Test de Contribution</title>\n";
echo "    <style>\n";
echo "        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }\n";
echo "        .container { background: #f8f9fa; padding: 30px; border-radius: 10px; }\n";
echo "        .success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }\n";
echo "        .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }\n";
echo "        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }\n";
echo "        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }\n";
echo "        .btn:hover { background: #0056b3; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";
echo "    <div class='container'>\n";
echo "        <h1>🧪 Test de Contribution</h1>\n";
echo "        <p>Ce script simule une contribution pour tester les notifications.</p>\n";

// Afficher les informations de simulation
echo "        <div class='info'>\n";
echo "            <h3>📊 Données simulées :</h3>\n";
echo "            <ul>\n";
echo "                <li><strong>Montant précédent :</strong> " . number_format($montantPrecedent, 2) . "€</li>\n";
echo "                <li><strong>Montant actuel :</strong> " . number_format($montantActuel, 2) . "€</li>\n";
echo "                <li><strong>Contribution :</strong> " . number_format($montantActuel - $montantPrecedent, 2) . "€</li>\n";
echo "                <li><strong>Objectif :</strong> " . number_format($objectif, 2) . "€</li>\n";
echo "                <li><strong>Pourcentage :</strong> " . $pourcentage . "%</li>\n";
echo "            </ul>\n";
echo "        </div>\n";

// Tester les notifications si demandé
if (isset($_GET['test'])) {
    $testResults = [];
    
    // Test Discord
    if ($config['discord']['actif'] && !empty($config['discord']['webhook_url'])) {
        try {
            require_once __DIR__ . '/discord.php';
            $discord = new DiscordWebhook($config['discord']['webhook_url'], __DIR__ . '/data.json');
            
            $result = $discord->notifierChangement($montantPrecedent, $montantActuel, $objectif);
            $testResults['discord'] = ['success' => $result, 'message' => $result ? 'Notification Discord envoyée avec succès' : 'Erreur lors de l\'envoi Discord'];
        } catch (Exception $e) {
            $testResults['discord'] = ['success' => false, 'message' => 'Erreur Discord : ' . $e->getMessage()];
        }
    } else {
        $testResults['discord'] = ['success' => false, 'message' => 'Discord non configuré ou désactivé'];
    }
    
    // Test Twitch
    if ($config['twitch']['actif'] && !empty($config['twitch']['client_id'])) {
        try {
            require_once __DIR__ . '/twitch.php';
            $twitch = creerBotTwitch($config, __DIR__ . '/data.json');
            
            $result = $twitch->sauvegarderAvecNotification($montantActuel, $objectif);
            $testResults['twitch'] = ['success' => $result, 'message' => $result ? 'Notification Twitch envoyée avec succès' : 'Erreur lors de l\'envoi Twitch'];
        } catch (Exception $e) {
            $testResults['twitch'] = ['success' => false, 'message' => 'Erreur Twitch : ' . $e->getMessage()];
        }
    } else {
        $testResults['twitch'] = ['success' => false, 'message' => 'Twitch non configuré ou désactivé'];
    }
    
    // Afficher les résultats
    echo "        <h3>📋 Résultats des tests :</h3>\n";
    
    foreach ($testResults as $service => $result) {
        $class = $result['success'] ? 'success' : 'error';
        $icon = $result['success'] ? '✅' : '❌';
        echo "        <div class='{$class}'>\n";
        echo "            <strong>{$icon} " . ucfirst($service) . " :</strong> {$result['message']}\n";
        echo "        </div>\n";
    }
}

echo "        <div style='margin-top: 30px;'>\n";
echo "            <a href='?test=1' class='btn'>🚀 Lancer le test</a>\n";
echo "            <a href='index.php' class='btn' style='background: #6c757d;'>🏠 Retour à l'admin</a>\n";
echo "        </div>\n";

echo "        <div style='margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px; color: #856404;'>\n";
echo "            <strong>⚠️ Note :</strong> Ce test simule une contribution et enverra de vraies notifications si les services sont configurés et actifs.\n";
echo "        </div>\n";

echo "    </div>\n";
echo "</body>\n";
echo "</html>\n";
?>