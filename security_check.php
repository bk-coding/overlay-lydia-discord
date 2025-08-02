<?php
/**
 * Script de vérification de sécurité
 * Vérifie que toutes les mesures de sécurité sont correctement configurées
 */

// Protection contre l'accès web direct
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Accès interdit - Ce script doit être exécuté en ligne de commande');
}

echo "=== VÉRIFICATION DE SÉCURITÉ DU SYSTÈME ===\n\n";

$errors = [];
$warnings = [];
$success = [];

/**
 * Fonction utilitaire pour vérifier l'existence d'un fichier
 */
function checkFile($file, $description) {
    if (file_exists($file)) {
        return true;
    }
    return false;
}

/**
 * Fonction utilitaire pour vérifier les permissions d'un fichier
 */
function checkPermissions($file, $expectedPerms, $description) {
    if (!file_exists($file)) {
        return false;
    }
    
    $perms = substr(sprintf('%o', fileperms($file)), -3);
    return $perms === $expectedPerms;
}

/**
 * Vérification des fichiers essentiels
 */
echo "1. Vérification des fichiers essentiels...\n";

$essentialFiles = [
    '.htaccess' => 'Fichier de configuration Apache',
    'security.php' => 'Gestionnaire de sécurité',
    'config.php' => 'Fichier de configuration',
    'index.php' => 'Interface d\'administration',
    'update.php' => 'Script de mise à jour',
    'discord.php' => 'Gestionnaire Discord',
    'overlay.php' => 'Générateur d\'overlay',
    'secure_permissions.sh' => 'Script de permissions'
];

foreach ($essentialFiles as $file => $description) {
    if (checkFile($file, $description)) {
        $success[] = "✓ $description trouvé";
    } else {
        $errors[] = "✗ $description manquant ($file)";
    }
}

/**
 * Vérification des permissions de fichiers
 */
echo "\n2. Vérification des permissions de fichiers...\n";

$filePermissions = [
    'config.php' => ['600', 'Fichier de configuration'],
    'security.php' => ['644', 'Gestionnaire de sécurité'],
    'index.php' => ['644', 'Interface d\'administration'],
    'update.php' => ['644', 'Script de mise à jour'],
    'discord.php' => ['644', 'Gestionnaire Discord'],
    'overlay.php' => ['644', 'Générateur d\'overlay'],
    'secure_permissions.sh' => ['755', 'Script de permissions']
];

foreach ($filePermissions as $file => $config) {
    list($expectedPerms, $description) = $config;
    if (file_exists($file)) {
        if (checkPermissions($file, $expectedPerms, $description)) {
            $success[] = "✓ Permissions correctes pour $description ($expectedPerms)";
        } else {
            $currentPerms = substr(sprintf('%o', fileperms($file)), -3);
            $warnings[] = "⚠ Permissions incorrectes pour $description (actuel: $currentPerms, attendu: $expectedPerms)";
        }
    }
}

/**
 * Vérification du contenu du fichier .htaccess
 */
echo "\n3. Vérification du fichier .htaccess...\n";

if (file_exists('.htaccess')) {
    $htaccessContent = file_get_contents('.htaccess');
    
    $htaccessChecks = [
        'php_flag display_errors off' => 'Désactivation de l\'affichage des erreurs',
        'LimitRequestBody' => 'Limitation de la taille des requêtes',
        'X-Content-Type-Options' => 'En-tête de sécurité X-Content-Type-Options',
        'X-Frame-Options' => 'En-tête de sécurité X-Frame-Options',
        'X-XSS-Protection' => 'En-tête de sécurité X-XSS-Protection',
        'Content-Security-Policy' => 'Politique de sécurité du contenu',
        'RewriteEngine On' => 'Moteur de réécriture activé',
        'Files "config.php"' => 'Protection du fichier de configuration',
        'Files "security.php"' => 'Protection du gestionnaire de sécurité',
        'Files "data.json"' => 'Protection du fichier de données'
    ];
    
    foreach ($htaccessChecks as $pattern => $description) {
        if (strpos($htaccessContent, $pattern) !== false) {
            $success[] = "✓ $description configuré";
        } else {
            $warnings[] = "⚠ $description manquant dans .htaccess";
        }
    }
} else {
    $errors[] = "✗ Fichier .htaccess manquant";
}

/**
 * Vérification de la classe SecurityManager
 */
echo "\n4. Vérification de la classe SecurityManager...\n";

if (file_exists('security.php')) {
    require_once 'security.php';
    
    if (class_exists('SecurityManager')) {
        $success[] = "✓ Classe SecurityManager trouvée";
        
        $securityMethods = [
            'hashPassword' => 'Hachage des mots de passe',
            'verifyPassword' => 'Vérification des mots de passe',
            'generateCSRFToken' => 'Génération de tokens CSRF',
            'verifyCSRFToken' => 'Vérification de tokens CSRF',
            'authenticate' => 'Authentification',
            'validateData' => 'Validation des données',
            'logSecurityEvent' => 'Journalisation de sécurité',
            'checkRateLimit' => 'Limitation de taux'
        ];
        
        foreach ($securityMethods as $method => $description) {
            if (method_exists('SecurityManager', $method)) {
                $success[] = "✓ Méthode $description disponible";
            } else {
                $errors[] = "✗ Méthode $description manquante";
            }
        }
    } else {
        $errors[] = "✗ Classe SecurityManager non trouvée";
    }
} else {
    $errors[] = "✗ Fichier security.php manquant";
}

/**
 * Vérification de la configuration PHP
 */
echo "\n5. Vérification de la configuration PHP...\n";

$phpChecks = [
    'display_errors' => ['off', 'Affichage des erreurs désactivé'],
    'log_errors' => ['on', 'Journalisation des erreurs activée'],
    'session.cookie_httponly' => ['1', 'Cookies de session HTTPOnly'],
    'session.use_strict_mode' => ['1', 'Mode strict des sessions']
];

foreach ($phpChecks as $setting => $config) {
    list($expectedValue, $description) = $config;
    $currentValue = ini_get($setting);
    
    if ($currentValue == $expectedValue) {
        $success[] = "✓ $description";
    } else {
        $warnings[] = "⚠ $description (actuel: $currentValue, recommandé: $expectedValue)";
    }
}

/**
 * Vérification des répertoires
 */
echo "\n6. Vérification des répertoires...\n";

$directories = [
    'logs' => 'Répertoire de logs'
];

foreach ($directories as $dir => $description) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            $success[] = "✓ $description accessible en écriture";
        } else {
            $warnings[] = "⚠ $description non accessible en écriture";
        }
    } else {
        $warnings[] = "⚠ $description manquant (sera créé automatiquement)";
    }
}

/**
 * Vérification des en-têtes de sécurité dans les fichiers PHP
 */
echo "\n7. Vérification des en-têtes de sécurité...\n";

$phpFiles = ['index.php', 'update.php', 'discord.php', 'overlay.php'];
$securityHeaders = [
    'X-Content-Type-Options' => 'Protection contre le MIME sniffing',
    'X-Frame-Options' => 'Protection contre le clickjacking',
    'X-XSS-Protection' => 'Protection XSS basique'
];

foreach ($phpFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $fileHasHeaders = false;
        
        foreach ($securityHeaders as $header => $description) {
            if (strpos($content, $header) !== false) {
                $fileHasHeaders = true;
                break;
            }
        }
        
        if ($fileHasHeaders) {
            $success[] = "✓ En-têtes de sécurité trouvés dans $file";
        } else {
            $warnings[] = "⚠ En-têtes de sécurité manquants dans $file";
        }
    }
}

/**
 * Affichage des résultats
 */
echo "\n=== RÉSULTATS DE LA VÉRIFICATION ===\n\n";

if (!empty($success)) {
    echo "SUCCÈS (" . count($success) . "):\n";
    foreach ($success as $item) {
        echo "  $item\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "AVERTISSEMENTS (" . count($warnings) . "):\n";
    foreach ($warnings as $item) {
        echo "  $item\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "ERREURS (" . count($errors) . "):\n";
    foreach ($errors as $item) {
        echo "  $item\n";
    }
    echo "\n";
}

/**
 * Score de sécurité
 */
$totalChecks = count($success) + count($warnings) + count($errors);
$securityScore = $totalChecks > 0 ? round((count($success) / $totalChecks) * 100) : 0;

echo "=== SCORE DE SÉCURITÉ ===\n";
echo "Score global: $securityScore%\n";

if ($securityScore >= 90) {
    echo "État: EXCELLENT ✓\n";
} elseif ($securityScore >= 75) {
    echo "État: BON ⚠\n";
} elseif ($securityScore >= 60) {
    echo "État: MOYEN ⚠\n";
} else {
    echo "État: CRITIQUE ✗\n";
}

echo "\nRecommandations:\n";
if (!empty($errors)) {
    echo "- Corriger les erreurs critiques en priorité\n";
}
if (!empty($warnings)) {
    echo "- Examiner et corriger les avertissements\n";
}
echo "- Exécuter ce script régulièrement\n";
echo "- Consulter SECURITY.md pour plus de détails\n";

echo "\n=== FIN DE LA VÉRIFICATION ===\n";

// Code de sortie basé sur les erreurs
exit(empty($errors) ? 0 : 1);
?>