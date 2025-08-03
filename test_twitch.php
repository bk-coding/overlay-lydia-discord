<?php
/**
 * Script de test pour les notifications Twitch
 * Permet de tester la configuration Twitch et l'envoi de messages
 */

require_once 'config.php';
require_once 'twitch.php';

// Configuration pour l'affichage
header('Content-Type: text/html; charset=UTF-8');

/**
 * Affiche le résultat d'un test avec un style approprié
 */
function afficherResultat($titre, $succes, $message, $details = null) {
    $couleur = $succes ? '#28a745' : '#dc3545';
    $icone = $succes ? '✅' : '❌';
    
    echo "<div style='margin: 10px 0; padding: 15px; border-left: 4px solid {$couleur}; background: #f8f9fa;'>";
    echo "<h3 style='margin: 0 0 10px 0; color: {$couleur};'>{$icone} {$titre}</h3>";
    echo "<p style='margin: 0;'>{$message}</p>";
    
    if ($details) {
        echo "<details style='margin-top: 10px;'>";
        echo "<summary style='cursor: pointer; color: #6c757d;'>Détails techniques</summary>";
        echo "<pre style='background: #e9ecef; padding: 10px; margin-top: 5px; border-radius: 4px; overflow-x: auto;'>";
        echo htmlspecialchars($details);
        echo "</pre>";
        echo "</details>";
    }
    
    echo "</div>";
}

/**
 * Teste la configuration Twitch
 */
function testerConfigurationTwitch($config) {
    $erreurs = [];
    
    // Vérifier si Twitch est activé
    if (!isset($config['twitch']['actif']) || !$config['twitch']['actif']) {
        $erreurs[] = "Twitch n'est pas activé dans la configuration";
    }
    
    // Vérifier les paramètres requis
    $parametresRequis = [
        'client_id' => 'Client ID',
        'access_token' => 'Access Token',
        'broadcaster_id' => 'Broadcaster ID',
        'bot_user_id' => 'Bot User ID'
    ];
    
    foreach ($parametresRequis as $param => $nom) {
        if (!isset($config['twitch'][$param]) || empty($config['twitch'][$param])) {
            $erreurs[] = "$nom ($param) non configuré";
        } elseif (strpos($config['twitch'][$param], 'VOTRE_') !== false) {
            $erreurs[] = "$nom ($param) contient encore la valeur par défaut";
        }
    }
    
    return $erreurs;
}

/**
 * Teste la connexion à l'API Twitch
 */
function testerConnexionTwitch($config) {
    try {
        $twitchBot = creerBotTwitch($config, __DIR__ . '/' . $config['technique']['fichier_donnees']);
        
        if ($twitchBot === null) {
            return [
                'succes' => false,
                'message' => 'Impossible de créer l\'instance du bot Twitch',
                'details' => 'Vérifiez la configuration et les fichiers requis'
            ];
        }
        
        $testConnexion = $twitchBot->testerConnexion();
        
        if (!$testConnexion['success']) {
            return [
                'succes' => false,
                'message' => 'Erreur de connexion à l\'API Twitch: ' . $testConnexion['error'],
                'details' => "Vérifications à effectuer:\n1. Access Token valide et non expiré\n2. Client ID correct\n3. Permissions nécessaires accordées\n4. Connexion internet fonctionnelle"
            ];
        }
        
        $details = "Connexion API Twitch réussie\n";
        if (isset($testConnexion['user_info'])) {
            $userInfo = $testConnexion['user_info'];
            $details .= "Utilisateur: " . ($userInfo['login'] ?? 'N/A') . "\n";
            $details .= "Client ID: " . ($userInfo['client_id'] ?? 'N/A') . "\n";
            $details .= "Scopes: " . (isset($userInfo['scopes']) ? implode(', ', $userInfo['scopes']) : 'N/A');
        }
        
        return [
            'succes' => true,
            'message' => 'Connexion à l\'API Twitch réussie',
            'details' => $details
        ];
        
    } catch (Exception $e) {
        return [
            'succes' => false,
            'message' => 'Erreur lors du test de connexion: ' . $e->getMessage(),
            'details' => $e->getTraceAsString()
        ];
    }
}

/**
 * Teste l'envoi d'un message Twitch
 */
function testerEnvoiTwitch($config) {
    try {
        $twitchBot = creerBotTwitch($config, __DIR__ . '/' . $config['technique']['fichier_donnees']);
        
        if ($twitchBot === null) {
            return [
                'succes' => false,
                'message' => 'Impossible de créer l\'instance du bot Twitch',
                'details' => 'Vérifiez la configuration'
            ];
        }
        
        $resultatTest = $twitchBot->envoyerMessageTest();
        
        if (!$resultatTest['success']) {
            $details = "Erreur: " . $resultatTest['error'] . "\n";
            if (isset($resultatTest['http_code'])) {
                $details .= "Code HTTP: " . $resultatTest['http_code'] . "\n";
            }
            $details .= "\nVérifications supplémentaires:\n";
            $details .= "1. Permission 'user:write:chat' accordée\n";
            $details .= "2. Broadcaster ID correct\n";
            $details .= "3. Bot User ID correct\n";
            $details .= "4. Le bot n'a pas besoin d'être modérateur";
            
            return [
                'succes' => false,
                'message' => 'Échec de l\'envoi du message de test',
                'details' => $details
            ];
        }
        
        return [
            'succes' => true,
            'message' => 'Message de test envoyé avec succès sur Twitch',
            'details' => "Message envoyé: Test du système de cagnotte\nVérifiez votre chat Twitch pour voir le message"
        ];
        
    } catch (Exception $e) {
        return [
            'succes' => false,
            'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage(),
            'details' => $e->getTraceAsString()
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Twitch - Système de Cagnotte</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #9146FF;
            text-align: center;
            margin-bottom: 30px;
        }
        .info {
            background: #e8f4fd;
            border-left: 4px solid #9146FF;
            padding: 15px;
            margin: 20px 0;
        }
        .retour {
            text-align: center;
            margin-top: 30px;
        }
        .retour a {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .retour a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Test Twitch</h1>
        
        <div class="info">
            <strong>ℹ️ À propos de ce test :</strong><br>
            Ce script teste la configuration Twitch et envoie un message de test sur le chat pour vérifier que tout fonctionne correctement.
        </div>

        <?php
        echo "<h2>📋 Résultats des tests</h2>";
        
        // Test 1: Configuration Twitch
        $erreursConfig = testerConfigurationTwitch($config);
        if (empty($erreursConfig)) {
            afficherResultat(
                "Configuration Twitch",
                true,
                "La configuration Twitch est valide",
                "Client ID: " . substr($config['twitch']['client_id'], 0, 8) . "...\n" .
                "Broadcaster ID: " . $config['twitch']['broadcaster_id'] . "\n" .
                "Bot User ID: " . $config['twitch']['bot_user_id'] . "\n" .
                "Statut: " . ($config['twitch']['actif'] ? 'Activé' : 'Désactivé')
            );
        } else {
            afficherResultat(
                "Configuration Twitch",
                false,
                "Problèmes de configuration détectés",
                implode("\n", $erreursConfig)
            );
        }
        
        // Test 2: Connexion API (seulement si la configuration est OK)
        if (empty($erreursConfig)) {
            $resultatConnexion = testerConnexionTwitch($config);
            afficherResultat(
                "Connexion API Twitch",
                $resultatConnexion['succes'],
                $resultatConnexion['message'],
                $resultatConnexion['details']
            );
            
            // Test 3: Envoi de message (seulement si la connexion est OK)
            if ($resultatConnexion['succes']) {
                $resultatEnvoi = testerEnvoiTwitch($config);
                afficherResultat(
                    "Envoi de message Twitch",
                    $resultatEnvoi['succes'],
                    $resultatEnvoi['message'],
                    $resultatEnvoi['details']
                );
            } else {
                afficherResultat(
                    "Envoi de message Twitch",
                    false,
                    "Test ignoré en raison d'erreurs de connexion",
                    "Corrigez d'abord les problèmes de connexion ci-dessus"
                );
            }
        } else {
            afficherResultat(
                "Connexion API Twitch",
                false,
                "Test ignoré en raison d'erreurs de configuration",
                "Corrigez d'abord les problèmes de configuration ci-dessus"
            );
            
            afficherResultat(
                "Envoi de message Twitch",
                false,
                "Test ignoré en raison d'erreurs de configuration",
                "Corrigez d'abord les problèmes de configuration ci-dessus"
            );
        }
        
        // Informations supplémentaires
        echo "<h2>📊 Informations de configuration</h2>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<strong>Configuration actuelle :</strong><br>";
        echo "• Twitch activé : " . ($config['twitch']['actif'] ? 'Oui' : 'Non') . "<br>";
        echo "• Client ID configuré : " . (!empty($config['twitch']['client_id']) ? 'Oui' : 'Non') . "<br>";
        echo "• Access Token configuré : " . (!empty($config['twitch']['access_token']) ? 'Oui' : 'Non') . "<br>";
        echo "• Message de contribution : " . ($config['twitch']['message_contribution'] ?? 'Non défini') . "<br>";
        echo "• Objectif cagnotte : " . ($config['lydia']['objectif'] ?? 'Non défini') . "€<br>";
        echo "</div>";
        
        if (!empty($erreursConfig)) {
            echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<strong>⚠️ Pour corriger les problèmes :</strong><br>";
            echo "1. Accédez à l'interface d'administration<br>";
            echo "2. Configurez les paramètres Twitch (Client ID, Access Token, etc.)<br>";
            echo "3. Activez les notifications Twitch<br>";
            echo "4. Relancez ce test<br>";
            echo "</div>";
        }
        ?>

        <div class="retour">
            <a href="index.php">← Retour à l'administration</a>
        </div>
    </div>
</body>
</html>