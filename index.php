<?php
/**
 * Interface d'administration pour la configuration de la cagnotte
 * Permet de modifier la configuration après authentification
 */

session_start();

// Charger la configuration
$config = require __DIR__ . '/config.php';

// Gestion de l'authentification
$isAuthenticated = false;
$error = '';
$success = '';

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION[$config['admin']['nom_session']]) && 
    $_SESSION[$config['admin']['nom_session']] > time()) {
    $isAuthenticated = true;
}

// Traitement de la connexion
if (isset($_POST['login'])) {
    $code = $_POST['code'] ?? '';
    if ($code === $config['admin']['code_connexion']) {
        $_SESSION[$config['admin']['nom_session']] = time() + $config['admin']['duree_session'];
        $isAuthenticated = true;
    } else {
        $error = 'Code de connexion incorrect';
    }
}

// Traitement de la déconnexion
if (isset($_POST['logout'])) {
    unset($_SESSION[$config['admin']['nom_session']]);
    $isAuthenticated = false;
}

// Traitement de la sauvegarde de configuration
if ($isAuthenticated && isset($_POST['save_config'])) {
    try {
        // Construire le nouveau tableau de configuration
        $newConfig = [
            'lydia' => [
                'url' => $_POST['lydia_url'] ?? $config['lydia']['url'],
                'objectif' => (int)($_POST['lydia_objectif'] ?? $config['lydia']['objectif']),
            ],
            'discord' => [
                'webhook_url' => $_POST['discord_webhook_url'] ?? $config['discord']['webhook_url'],
                'actif' => isset($_POST['discord_actif']),
            ],
            'apparence' => [
                'couleur_debut' => $_POST['apparence_couleur_debut'] ?? $config['apparence']['couleur_debut'],
                'couleur_fin' => $_POST['apparence_couleur_fin'] ?? $config['apparence']['couleur_fin'],
                'couleur_bordure' => $_POST['apparence_couleur_bordure'] ?? $config['apparence']['couleur_bordure'],
                'couleur_fond' => $_POST['apparence_couleur_fond'] ?? $config['apparence']['couleur_fond'],
                'couleur_texte' => $_POST['apparence_couleur_texte'] ?? $config['apparence']['couleur_texte'],
                'largeur' => (int)($_POST['apparence_largeur'] ?? $config['apparence']['largeur']),
                'hauteur' => (int)($_POST['apparence_hauteur'] ?? $config['apparence']['hauteur']),
                'bordure_epaisseur' => (int)($_POST['apparence_bordure_epaisseur'] ?? $config['apparence']['bordure_epaisseur']),
                'bordure_rayon' => (int)($_POST['apparence_bordure_rayon'] ?? $config['apparence']['bordure_rayon']),
                'taille_police' => (int)($_POST['apparence_taille_police'] ?? $config['apparence']['taille_police']),
                'poids_police' => (int)($_POST['apparence_poids_police'] ?? $config['apparence']['poids_police']),
                'texte_personnalise' => $_POST['apparence_texte_personnalise'] ?? $config['apparence']['texte_personnalise'],
                'espacement_texte' => (int)($_POST['apparence_espacement_texte'] ?? $config['apparence']['espacement_texte']),
                'position_horizontale' => $_POST['apparence_position_horizontale'] ?? $config['apparence']['position_horizontale'],
                'position_verticale' => $_POST['apparence_position_verticale'] ?? $config['apparence']['position_verticale'],
                'marge_horizontale' => (int)($_POST['apparence_marge_horizontale'] ?? $config['apparence']['marge_horizontale']),
                'marge_verticale' => (int)($_POST['apparence_marge_verticale'] ?? $config['apparence']['marge_verticale']),
            ],
            'audio' => [
                'fichier' => $_POST['audio_fichier'] ?? $config['audio']['fichier'],
                'volume' => (float)($_POST['audio_volume'] ?? $config['audio']['volume']),
                'actif' => isset($_POST['audio_actif']),
                'formats_supportes' => $config['audio']['formats_supportes'], // Garder les formats existants
            ],
            'technique' => [
                'intervalle_maj' => (int)($_POST['technique_intervalle_maj'] ?? $config['technique']['intervalle_maj']),
                'timeout_curl' => (int)($_POST['technique_timeout_curl'] ?? $config['technique']['timeout_curl']),
                'duree_transition' => (int)($_POST['technique_duree_transition'] ?? $config['technique']['duree_transition']),
                'fichier_donnees' => $_POST['technique_fichier_donnees'] ?? $config['technique']['fichier_donnees'],
                'user_agent' => $_POST['technique_user_agent'] ?? $config['technique']['user_agent'],
            ],
            'messages' => [
                'chargement' => $_POST['messages_chargement'] ?? $config['messages']['chargement'],
                'erreur' => $_POST['messages_erreur'] ?? $config['messages']['erreur'],
                'format_montant' => $_POST['messages_format_montant'] ?? $config['messages']['format_montant'],
                'discord_titre_contribution' => $_POST['messages_discord_titre_contribution'] ?? $config['messages']['discord_titre_contribution'],
                'discord_titre_mise_a_jour' => $_POST['messages_discord_titre_mise_a_jour'] ?? $config['messages']['discord_titre_mise_a_jour'],
                'discord_titre_actualisation' => $_POST['messages_discord_titre_actualisation'] ?? $config['messages']['discord_titre_actualisation'],
                'discord_footer' => $_POST['messages_discord_footer'] ?? $config['messages']['discord_footer'],
            ],
            'admin' => $config['admin'], // Garder la configuration admin existante
        ];

        // Générer le contenu PHP
        $configContent = "<?php\n";
        $configContent .= "/**\n";
        $configContent .= " * Configuration centralisée du système d'overlay de cagnotte\n";
        $configContent .= " * \n";
        $configContent .= " * Ce fichier contient toutes les données personnalisables du système.\n";
        $configContent .= " * Modifiez uniquement les valeurs ci-dessous selon vos besoins.\n";
        $configContent .= " * \n";
        $configContent .= " * IMPORTANT : Après modification, aucun autre fichier ne doit être modifié.\n";
        $configContent .= " * Dernière modification : " . date('Y-m-d H:i:s') . "\n";
        $configContent .= " */\n\n";
        $configContent .= "return " . var_export($newConfig, true) . ";\n";
        $configContent .= "?>";

        // Sauvegarder le fichier
        if (file_put_contents(__DIR__ . '/config.php', $configContent) !== false) {
            $success = 'Configuration sauvegardée avec succès !';
            // Recharger la configuration
            $config = $newConfig;
        } else {
            $error = 'Erreur lors de la sauvegarde de la configuration';
        }
    } catch (Exception $e) {
        $error = 'Erreur : ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Cagnotte Overlay</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .content {
            padding: 30px;
        }

        .login-form {
            max-width: 400px;
            margin: 50px auto;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group input[type="color"] {
            height: 50px;
            padding: 5px;
        }

        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-danger {
            background: #dc3545;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .config-sections {
            display: grid;
            gap: 30px;
        }

        .config-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border-left: 5px solid #667eea;
        }

        .config-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.4em;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .preview-section {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .preview-bar {
            margin: 20px auto;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .logout-btn {
                position: static;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Administration Cagnotte</h1>
            <p>Interface de configuration pour votre overlay de cagnotte Twitch</p>
        </div>

        <div class="content">
            <?php if (!$isAuthenticated): ?>
                <!-- Formulaire de connexion -->
                <div class="login-form">
                    <h2>🔐 Connexion requise</h2>
                    <p style="margin: 20px 0; color: #666;">Entrez le code de connexion pour accéder à l'interface d'administration.</p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="code">Code de connexion</label>
                            <input type="password" id="code" name="code" required placeholder="Entrez votre code">
                        </div>
                        <button type="submit" name="login" class="btn">Se connecter</button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Interface d'administration -->
                <form method="POST" class="logout-btn">
                    <button type="submit" name="logout" class="btn btn-danger">Déconnexion</button>
                </form>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="config-sections">
                        <!-- Configuration Lydia -->
                        <div class="config-section">
                            <h3>💰 Configuration Lydia</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="lydia_url">URL de la cagnotte Lydia</label>
                                    <input type="url" id="lydia_url" name="lydia_url" value="<?= htmlspecialchars($config['lydia']['url']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="lydia_objectif">Objectif (€)</label>
                                    <input type="number" id="lydia_objectif" name="lydia_objectif" value="<?= $config['lydia']['objectif'] ?>" min="1" required>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Discord -->
                        <div class="config-section">
                            <h3>🔔 Configuration Discord</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="discord_webhook_url">URL du Webhook Discord</label>
                                    <input type="url" id="discord_webhook_url" name="discord_webhook_url" value="<?= htmlspecialchars($config['discord']['webhook_url']) ?>">
                                </div>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="discord_actif" name="discord_actif" <?= $config['discord']['actif'] ? 'checked' : '' ?>>
                                <label for="discord_actif">Activer les notifications Discord</label>
                            </div>
                        </div>

                        <!-- Configuration Apparence -->
                        <div class="config-section">
                            <h3>🎨 Configuration Apparence</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="apparence_couleur_debut">Couleur de début</label>
                                    <input type="color" id="apparence_couleur_debut" name="apparence_couleur_debut" value="<?= htmlspecialchars($config['apparence']['couleur_debut']) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="apparence_couleur_fin">Couleur de fin</label>
                                    <input type="color" id="apparence_couleur_fin" name="apparence_couleur_fin" value="<?= htmlspecialchars($config['apparence']['couleur_fin']) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="apparence_couleur_bordure">Couleur de bordure</label>
                                    <input type="color" id="apparence_couleur_bordure" name="apparence_couleur_bordure" value="<?= htmlspecialchars($config['apparence']['couleur_bordure']) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="apparence_couleur_texte">Couleur du texte</label>
                                    <input type="color" id="apparence_couleur_texte" name="apparence_couleur_texte" value="<?= htmlspecialchars($config['apparence']['couleur_texte']) ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="apparence_largeur">Largeur (px)</label>
                                    <input type="number" id="apparence_largeur" name="apparence_largeur" value="<?= $config['apparence']['largeur'] ?>" min="100" max="1000">
                                </div>
                                <div class="form-group">
                                    <label for="apparence_hauteur">Hauteur (px)</label>
                                    <input type="number" id="apparence_hauteur" name="apparence_hauteur" value="<?= $config['apparence']['hauteur'] ?>" min="20" max="200">
                                </div>
                                <div class="form-group">
                                    <label for="apparence_taille_police">Taille de police (px)</label>
                                    <input type="number" id="apparence_taille_police" name="apparence_taille_police" value="<?= $config['apparence']['taille_police'] ?>" min="10" max="50">
                                </div>
                                <div class="form-group">
                                    <label for="apparence_bordure_rayon">Rayon des coins (px)</label>
                                    <input type="number" id="apparence_bordure_rayon" name="apparence_bordure_rayon" value="<?= $config['apparence']['bordure_rayon'] ?>" min="0" max="50">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="apparence_texte_personnalise">Texte personnalisé</label>
                                    <input type="text" id="apparence_texte_personnalise" name="apparence_texte_personnalise" value="<?= htmlspecialchars($config['apparence']['texte_personnalise']) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="apparence_position_horizontale">Position horizontale</label>
                                    <select id="apparence_position_horizontale" name="apparence_position_horizontale">
                                        <option value="gauche" <?= $config['apparence']['position_horizontale'] === 'gauche' ? 'selected' : '' ?>>Gauche</option>
                                        <option value="droite" <?= $config['apparence']['position_horizontale'] === 'droite' ? 'selected' : '' ?>>Droite</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="apparence_position_verticale">Position verticale</label>
                                    <select id="apparence_position_verticale" name="apparence_position_verticale">
                                        <option value="haut" <?= $config['apparence']['position_verticale'] === 'haut' ? 'selected' : '' ?>>Haut</option>
                                        <option value="bas" <?= $config['apparence']['position_verticale'] === 'bas' ? 'selected' : '' ?>>Bas</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Audio -->
                        <div class="config-section">
                            <h3>🔊 Configuration Audio</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="audio_fichier">Fichier audio</label>
                                    <input type="text" id="audio_fichier" name="audio_fichier" value="<?= htmlspecialchars($config['audio']['fichier']) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="audio_volume">Volume (0.0 - 1.0)</label>
                                    <input type="number" id="audio_volume" name="audio_volume" value="<?= $config['audio']['volume'] ?>" min="0" max="1" step="0.1">
                                </div>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="audio_actif" name="audio_actif" <?= $config['audio']['actif'] ? 'checked' : '' ?>>
                                <label for="audio_actif">Activer le son</label>
                            </div>
                        </div>

                        <!-- Configuration Messages -->
                        <div class="config-section">
                            <h3>💬 Messages Personnalisables</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="messages_chargement">Message de chargement</label>
                                    <input type="text" id="messages_chargement" name="messages_chargement" value="<?= htmlspecialchars($config['messages']['chargement']) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="messages_erreur">Message d'erreur</label>
                                    <input type="text" id="messages_erreur" name="messages_erreur" value="<?= htmlspecialchars($config['messages']['erreur']) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="messages_discord_footer">Footer Discord</label>
                                    <input type="text" id="messages_discord_footer" name="messages_discord_footer" value="<?= htmlspecialchars($config['messages']['discord_footer']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 30px;">
                        <button type="submit" name="save_config" class="btn" style="font-size: 18px; padding: 20px 40px;">
                            💾 Sauvegarder la configuration
                        </button>
                    </div>
                </form>

                <div style="margin-top: 40px; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <h3>🔗 Liens utiles</h3>
                    <p style="margin: 10px 0;">
                        <a href="overlay.php" target="_blank" style="color: #667eea; text-decoration: none; font-weight: 600;">
                            📺 Voir l'overlay
                        </a>
                        |
                        <a href="update.php" target="_blank" style="color: #667eea; text-decoration: none; font-weight: 600;">
                            🔄 Tester la mise à jour
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>