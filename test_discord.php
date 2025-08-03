<?php
/**
 * Script de test pour les notifications Discord
 * Permet de tester la configuration Discord et l'envoi de messages
 */

require_once 'config.php';
require_once 'discord.php';

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
 * Teste la configuration Discord
 */
function testerConfigurationDiscord($config) {
    $erreurs = [];
    
    // Vérifier si Discord est activé
    if (!isset($config['discord']['actif']) || !$config['discord']['actif']) {
        $erreurs[] = "Discord n'est pas activé dans la configuration";
    }
    
    // Vérifier l'URL du webhook
    if (!isset($config['discord']['webhook_url']) || empty($config['discord']['webhook_url'])) {
        $erreurs[] = "URL du webhook Discord non configurée";
    } elseif (!filter_var($config['discord']['webhook_url'], FILTER_VALIDATE_URL)) {
        $erreurs[] = "URL du webhook Discord invalide";
    } elseif (!preg_match('/discord\.com\/api\/webhooks\/\d+\/[\w-]+/', $config['discord']['webhook_url'])) {
        $erreurs[] = "Format de l'URL webhook Discord incorrect";
    }
    
    return $erreurs;
}

/**
 * Teste l'envoi d'un message Discord
 */
function testerEnvoiDiscord($config) {
    try {
        $discord = new DiscordWebhook($config['discord']['webhook_url']);
        
        // Test simple avec la méthode testerWebhook
        $resultat = $discord->testerWebhook();
        
        return [
            'succes' => $resultat,
            'message' => $resultat ? 'Message de test envoyé avec succès sur Discord' : 'Échec de l\'envoi du message de test',
            'details' => $resultat ? "Test webhook Discord réussi\nMessage envoyé: Test du système de cagnotte" : 'Vérifiez l\'URL du webhook et votre connexion internet'
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
    <title>Test Discord - Système de Cagnotte</title>
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
            color: #5865F2;
            text-align: center;
            margin-bottom: 30px;
        }
        .info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
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
        <h1>🔧 Test Discord</h1>
        
        <div class="info">
            <strong>ℹ️ À propos de ce test :</strong><br>
            Ce script teste la configuration Discord et envoie un message de test pour vérifier que tout fonctionne correctement.
        </div>

        <?php
        echo "<h2>📋 Résultats des tests</h2>";
        
        // Test 1: Configuration Discord
        $erreursConfig = testerConfigurationDiscord($config);
        if (empty($erreursConfig)) {
            afficherResultat(
                "Configuration Discord",
                true,
                "La configuration Discord est valide",
                "URL webhook: " . substr($config['discord']['webhook_url'], 0, 50) . "...\nStatut: " . ($config['discord']['actif'] ? 'Activé' : 'Désactivé')
            );
        } else {
            afficherResultat(
                "Configuration Discord",
                false,
                "Problèmes de configuration détectés",
                implode("\n", $erreursConfig)
            );
        }
        
        // Test 2: Envoi de message (seulement si la configuration est OK)
        if (empty($erreursConfig)) {
            $resultatEnvoi = testerEnvoiDiscord($config);
            afficherResultat(
                "Envoi de message Discord",
                $resultatEnvoi['succes'],
                $resultatEnvoi['message'],
                $resultatEnvoi['details']
            );
        } else {
            afficherResultat(
                "Envoi de message Discord",
                false,
                "Test ignoré en raison d'erreurs de configuration",
                "Corrigez d'abord les problèmes de configuration ci-dessus"
            );
        }
        
        // Informations supplémentaires
        echo "<h2>📊 Informations de configuration</h2>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<strong>Configuration actuelle :</strong><br>";
        echo "• Discord activé : " . ($config['discord']['actif'] ? 'Oui' : 'Non') . "<br>";
        echo "• Webhook configuré : " . (!empty($config['discord']['webhook_url']) ? 'Oui' : 'Non') . "<br>";
        echo "• Objectif cagnotte : " . ($config['lydia']['objectif'] ?? 'Non défini') . "€<br>";
        echo "</div>";
        
        if (!empty($erreursConfig)) {
            echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<strong>⚠️ Pour corriger les problèmes :</strong><br>";
            echo "1. Accédez à l'interface d'administration<br>";
            echo "2. Configurez l'URL du webhook Discord<br>";
            echo "3. Activez les notifications Discord<br>";
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