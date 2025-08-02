<?php
/**
 * Script de déploiement sécurisé pour le système de cagnotte overlay
 * 
 * Ce script configure automatiquement les permissions et la sécurité
 * Usage: php deploy.php
 */

// Protection contre l'accès web
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Accès interdit. Ce script doit être exécuté en ligne de commande.');
}

echo "=== DÉPLOIEMENT SÉCURISÉ DU SYSTÈME DE CAGNOTTE ===\n\n";

/**
 * Configurer les permissions des fichiers
 */
function configurerPermissions() {
    echo "Configuration des permissions des fichiers...\n";
    
    $permissions = [
        'config.php' => 0600,
        'security.php' => 0644,
        'index.php' => 0644,
        'update.php' => 0644,
        'discord.php' => 0644,
        'overlay.php' => 0644,
        '.htaccess' => 0644,
        '.user.ini' => 0644,
        'security_check.php' => 0755,
        'deploy.php' => 0755
    ];
    
    foreach ($permissions as $fichier => $permission) {
        if (file_exists($fichier)) {
            chmod($fichier, $permission);
            echo "  ✓ $fichier: " . decoct($permission) . "\n";
        } else {
            echo "  ⚠ $fichier: fichier non trouvé\n";
        }
    }
}

/**
 * Créer les répertoires nécessaires
 */
function creerRepertoires() {
    echo "\nCréation des répertoires nécessaires...\n";
    
    $repertoires = [
        'logs' => 0755,
        'backup' => 0755
    ];
    
    foreach ($repertoires as $repertoire => $permission) {
        if (!is_dir($repertoire)) {
            mkdir($repertoire, $permission, true);
            echo "  ✓ Répertoire $repertoire créé\n";
        } else {
            chmod($repertoire, $permission);
            echo "  ✓ Répertoire $repertoire configuré\n";
        }
    }
}

/**
 * Vérifier la configuration PHP
 */
function verifierConfigurationPHP() {
    echo "\nVérification de la configuration PHP...\n";
    
    $checks = [
        'display_errors' => ['off', 'Affichage des erreurs'],
        'log_errors' => ['on', 'Journalisation des erreurs'],
        'session.cookie_httponly' => ['1', 'Cookies HTTPOnly'],
        'session.use_strict_mode' => ['1', 'Mode strict des sessions']
    ];
    
    foreach ($checks as $param => $info) {
        $valeur = ini_get($param);
        $attendu = $info[0];
        $description = $info[1];
        
        if (strtolower($valeur) === strtolower($attendu)) {
            echo "  ✓ $description: OK\n";
        } else {
            echo "  ⚠ $description: $valeur (recommandé: $attendu)\n";
        }
    }
}

/**
 * Créer un fichier de configuration par défaut
 */
function creerConfigurationDefaut() {
    echo "\nCréation de la configuration par défaut...\n";
    
    if (!file_exists('config.php')) {
        $configDefaut = '<?php
// Configuration par défaut - À personnaliser
$config = [
    "lydia" => [
        "url" => "",
        "objectif" => 1000
    ],
    "discord" => [
        "webhook_url" => ""
    ],
    "apparence" => [
        "couleur_fond" => "#1a1a1a",
        "couleur_texte" => "#ffffff",
        "couleur_barre" => "#4CAF50",
        "largeur" => 400,
        "hauteur" => 100,
        "position_x" => 50,
        "position_y" => 50
    ],
    "audio" => [
        "activer" => true,
        "fichier" => "caisse.mp3",
        "volume" => 0.5
    ],
    "technique" => [
        "intervalle_maj" => 5000,
        "fichier_donnees" => "data.json",
        "timeout_curl" => 10,
        "user_agent" => "CagnotteOverlay/1.0"
    ],
    "messages" => [
        "erreur_chargement" => "Erreur de chargement",
        "aucune_donnee" => "Aucune donnée disponible",
        "format_montant" => "{montant}€ / {objectif}€"
    ]
];
?>';
        
        file_put_contents('config.php', $configDefaut);
        chmod('config.php', 0600);
        echo "  ✓ Fichier config.php créé\n";
    } else {
        echo "  ✓ Fichier config.php existe déjà\n";
    }
}

/**
 * Créer un fichier de données par défaut
 */
function creerDonneesDefaut() {
    echo "\nCréation des données par défaut...\n";
    
    if (!file_exists('data.json')) {
        $donneesDefaut = [
            'montant' => 0,
            'objectif' => 1000,
            'derniere_maj' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents('data.json', json_encode($donneesDefaut, JSON_PRETTY_PRINT));
        chmod('data.json', 0644);
        echo "  ✓ Fichier data.json créé\n";
    } else {
        echo "  ✓ Fichier data.json existe déjà\n";
    }
}

/**
 * Exécuter la vérification de sécurité
 */
function executerVerificationSecurite() {
    echo "\nExécution de la vérification de sécurité...\n";
    
    if (file_exists('security_check.php')) {
        echo "----------------------------------------\n";
        system('php security_check.php');
        echo "----------------------------------------\n";
    } else {
        echo "  ⚠ Script de vérification non trouvé\n";
    }
}

// Exécution du déploiement
try {
    configurerPermissions();
    creerRepertoires();
    creerConfigurationDefaut();
    creerDonneesDefaut();
    verifierConfigurationPHP();
    executerVerificationSecurite();
    
    echo "\n=== DÉPLOIEMENT TERMINÉ ===\n";
    echo "✓ Le système est prêt à être utilisé\n";
    echo "✓ Consultez SECURITY.md pour les bonnes pratiques\n";
    echo "✓ Configurez vos URLs Lydia et Discord dans config.php\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Erreur lors du déploiement: " . $e->getMessage() . "\n";
    exit(1);
}
?>