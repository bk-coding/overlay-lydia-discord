<?php
/**
 * Script de sauvegarde automatique pour le système de cagnotte overlay
 * 
 * Ce script crée des sauvegardes horodatées de la configuration et des données
 * Usage: php backup.php [--auto] [--clean]
 */

// Protection contre l'accès web
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Accès interdit. Ce script doit être exécuté en ligne de commande.');
}

// Configuration
$repertoireSauvegarde = 'backup';
$maxSauvegardes = 10; // Nombre maximum de sauvegardes à conserver

/**
 * Créer le répertoire de sauvegarde s'il n'existe pas
 */
function creerRepertoireSauvegarde($repertoire) {
    if (!is_dir($repertoire)) {
        mkdir($repertoire, 0755, true);
        echo "✓ Répertoire de sauvegarde créé: $repertoire\n";
    }
}

/**
 * Créer une sauvegarde horodatée
 */
function creerSauvegarde($repertoire) {
    $timestamp = date('Y-m-d_H-i-s');
    $nomSauvegarde = "sauvegarde_$timestamp";
    $cheminSauvegarde = "$repertoire/$nomSauvegarde";
    
    // Créer le répertoire de cette sauvegarde
    mkdir($cheminSauvegarde, 0755, true);
    
    // Fichiers à sauvegarder
    $fichiersASauvegarder = [
        'config.php' => 'Configuration principale',
        'data.json' => 'Données de la cagnotte',
        '.htaccess' => 'Configuration Apache',
        '.user.ini' => 'Configuration PHP'
    ];
    
    $fichiersSauvegardes = 0;
    
    foreach ($fichiersASauvegarder as $fichier => $description) {
        if (file_exists($fichier)) {
            copy($fichier, "$cheminSauvegarde/$fichier");
            echo "  ✓ $description sauvegardé\n";
            $fichiersSauvegardes++;
        } else {
            echo "  ⚠ $description non trouvé: $fichier\n";
        }
    }
    
    // Créer un fichier d'information sur la sauvegarde
    $infoSauvegarde = [
        'date' => date('Y-m-d H:i:s'),
        'timestamp' => $timestamp,
        'fichiers_sauvegardes' => $fichiersSauvegardes,
        'version_php' => PHP_VERSION,
        'taille_donnees' => file_exists('data.json') ? filesize('data.json') : 0
    ];
    
    file_put_contents(
        "$cheminSauvegarde/info.json", 
        json_encode($infoSauvegarde, JSON_PRETTY_PRINT)
    );
    
    echo "✓ Sauvegarde créée: $nomSauvegarde ($fichiersSauvegardes fichiers)\n";
    return $cheminSauvegarde;
}

/**
 * Nettoyer les anciennes sauvegardes
 */
function nettoyerAnciennesSauvegardes($repertoire, $maxSauvegardes) {
    $sauvegardes = glob("$repertoire/sauvegarde_*");
    
    if (count($sauvegardes) <= $maxSauvegardes) {
        return;
    }
    
    // Trier par date de modification (plus ancien en premier)
    usort($sauvegardes, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    $aSuuprimer = array_slice($sauvegardes, 0, count($sauvegardes) - $maxSauvegardes);
    
    foreach ($aSuuprimer as $sauvegarde) {
        supprimerRecursivement($sauvegarde);
        echo "🗑️ Ancienne sauvegarde supprimée: " . basename($sauvegarde) . "\n";
    }
}

/**
 * Supprimer un répertoire récursivement
 */
function supprimerRecursivement($repertoire) {
    if (!is_dir($repertoire)) {
        return unlink($repertoire);
    }
    
    $fichiers = array_diff(scandir($repertoire), ['.', '..']);
    
    foreach ($fichiers as $fichier) {
        $chemin = "$repertoire/$fichier";
        is_dir($chemin) ? supprimerRecursivement($chemin) : unlink($chemin);
    }
    
    return rmdir($repertoire);
}

/**
 * Lister les sauvegardes existantes
 */
function listerSauvegardes($repertoire) {
    $sauvegardes = glob("$repertoire/sauvegarde_*");
    
    if (empty($sauvegardes)) {
        echo "Aucune sauvegarde trouvée.\n";
        return;
    }
    
    echo "\n=== SAUVEGARDES EXISTANTES ===\n";
    
    // Trier par date (plus récent en premier)
    usort($sauvegardes, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    foreach ($sauvegardes as $sauvegarde) {
        $nom = basename($sauvegarde);
        $date = date('Y-m-d H:i:s', filemtime($sauvegarde));
        $taille = calculerTailleRepertoire($sauvegarde);
        
        echo "📦 $nom\n";
        echo "   Date: $date\n";
        echo "   Taille: " . formaterTaille($taille) . "\n";
        
        // Lire les informations si disponibles
        $infoFichier = "$sauvegarde/info.json";
        if (file_exists($infoFichier)) {
            $info = json_decode(file_get_contents($infoFichier), true);
            echo "   Fichiers: " . $info['fichiers_sauvegardes'] . "\n";
        }
        echo "\n";
    }
}

/**
 * Calculer la taille d'un répertoire
 */
function calculerTailleRepertoire($repertoire) {
    $taille = 0;
    $fichiers = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($repertoire, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($fichiers as $fichier) {
        $taille += $fichier->getSize();
    }
    
    return $taille;
}

/**
 * Formater la taille en octets
 */
function formaterTaille($octets) {
    $unites = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    
    while ($octets >= 1024 && $i < count($unites) - 1) {
        $octets /= 1024;
        $i++;
    }
    
    return round($octets, 2) . ' ' . $unites[$i];
}

/**
 * Restaurer une sauvegarde
 */
function restaurerSauvegarde($cheminSauvegarde) {
    if (!is_dir($cheminSauvegarde)) {
        echo "❌ Sauvegarde non trouvée: $cheminSauvegarde\n";
        return false;
    }
    
    echo "🔄 Restauration de la sauvegarde: " . basename($cheminSauvegarde) . "\n";
    
    $fichiers = ['config.php', 'data.json', '.htaccess', '.user.ini'];
    $restaures = 0;
    
    foreach ($fichiers as $fichier) {
        $source = "$cheminSauvegarde/$fichier";
        if (file_exists($source)) {
            copy($source, $fichier);
            echo "  ✓ $fichier restauré\n";
            $restaures++;
        }
    }
    
    echo "✓ Restauration terminée ($restaures fichiers)\n";
    return true;
}

// Traitement des arguments de ligne de commande
$options = getopt('', ['auto', 'clean', 'list', 'restore:']);

echo "=== SYSTÈME DE SAUVEGARDE ===\n\n";

// Créer le répertoire de sauvegarde
creerRepertoireSauvegarde($repertoireSauvegarde);

// Mode liste
if (isset($options['list'])) {
    listerSauvegardes($repertoireSauvegarde);
    exit(0);
}

// Mode restauration
if (isset($options['restore'])) {
    $nomSauvegarde = $options['restore'];
    $cheminSauvegarde = "$repertoireSauvegarde/$nomSauvegarde";
    restaurerSauvegarde($cheminSauvegarde);
    exit(0);
}

// Créer une nouvelle sauvegarde
echo "Création d'une nouvelle sauvegarde...\n";
$cheminSauvegarde = creerSauvegarde($repertoireSauvegarde);

// Nettoyage automatique si demandé ou en mode auto
if (isset($options['clean']) || isset($options['auto'])) {
    echo "\nNettoyage des anciennes sauvegardes...\n";
    nettoyerAnciennesSauvegardes($repertoireSauvegarde, $maxSauvegardes);
}

// Afficher les sauvegardes si pas en mode auto
if (!isset($options['auto'])) {
    listerSauvegardes($repertoireSauvegarde);
}

echo "\n=== SAUVEGARDE TERMINÉE ===\n";

// Instructions d'utilisation
if (!isset($options['auto'])) {
    echo "\nUtilisation:\n";
    echo "  php backup.php              # Créer une sauvegarde\n";
    echo "  php backup.php --auto       # Sauvegarde silencieuse avec nettoyage\n";
    echo "  php backup.php --clean      # Créer une sauvegarde et nettoyer\n";
    echo "  php backup.php --list       # Lister les sauvegardes\n";
    echo "  php backup.php --restore=nom # Restaurer une sauvegarde\n\n";
}
?>