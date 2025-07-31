<?php
/**
 * Configuration centralisée du système d'overlay de cagnotte - MODÈLE
 * 
 * Ce fichier contient toutes les données personnalisables du système.
 * 
 * INSTRUCTIONS D'INSTALLATION :
 * 1. Copiez ce fichier et renommez-le en "config.php"
 * 2. Modifiez les valeurs ci-dessous selon vos besoins
 * 3. Après modification, aucun autre fichier ne doit être modifié
 * 
 * IMPORTANT : Ne commitez jamais le fichier config.php avec vos vraies données !
 */

return [
    // ===== CONFIGURATION LYDIA =====
    'lydia' => [
        // URL de votre cagnotte Lydia (remplacez par votre URL)
        'url' => 'https://pots.lydia.me/collect/pots?id=VOTRE_ID_CAGNOTTE',
        
        // Objectif de la cagnotte en euros
        'objectif' => 500,
    ],

    // ===== CONFIGURATION DISCORD =====
    'discord' => [
        // URL du webhook Discord (remplacez par votre webhook)
        // Pour désactiver Discord, laissez cette valeur vide : ''
        'webhook_url' => 'https://discord.com/api/webhooks/VOTRE_WEBHOOK_ID/VOTRE_WEBHOOK_TOKEN',
        
        // Activer/désactiver les notifications Discord
        'actif' => true,
    ],

    // ===== CONFIGURATION VISUELLE =====
    'apparence' => [
        // Couleurs de la barre de progression (dégradé)
        'couleur_debut' => '#ffc400',    // Couleur de début (jaune)
        'couleur_fin' => '#ff6600',      // Couleur de fin (orange)
        
        // Couleurs du conteneur
        'couleur_bordure' => '#ffffff',           // Couleur de la bordure
        'couleur_fond' => 'rgba(0,0,0,0.7)',     // Couleur de fond (avec transparence)
        
        // Couleurs du texte
        'couleur_texte' => '#ffffff',             // Couleur du texte
        
        // Dimensions de la barre
        'largeur' => 400,                // Largeur en pixels
        'hauteur' => 50,                 // Hauteur en pixels
        'bordure_epaisseur' => 3,        // Épaisseur de la bordure en pixels
        'bordure_rayon' => 10,           // Rayon des coins arrondis en pixels
        
        // Texte
        'taille_police' => 20,           // Taille de la police en pixels
        'poids_police' => 900,           // Poids de la police (100-900)
        
        // Texte personnalisé au-dessus de la barre (optionnel)
        'texte_personnalise' => '',      // Texte à afficher au-dessus (vide = pas de texte)
        'espacement_texte' => 10,        // Espacement entre le texte et la barre en pixels
        
        // Position de l'overlay (coin inférieur droit par défaut)
        'position_horizontale' => 'droite',  // 'gauche' ou 'droite'
        'position_verticale' => 'bas',       // 'haut' ou 'bas'
        'marge_horizontale' => 10,           // Marge depuis le bord horizontal en pixels
        'marge_verticale' => 10,             // Marge depuis le bord vertical en pixels
    ],

    // ===== CONFIGURATION AUDIO =====
    'audio' => [
        // Fichier audio à jouer lors d'une contribution
        'fichier' => 'caisse.mp3',
        
        // Volume de l'audio (0.0 à 1.0)
        'volume' => 0.7,
        
        // Activer/désactiver le son
        'actif' => true,
        
        // Formats audio supportés (pour les fallbacks)
        'formats_supportes' => [
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg'
        ],
    ],

    // ===== CONFIGURATION TECHNIQUE =====
    'technique' => [
        // Intervalle de mise à jour en millisecondes (60000 = 1 minute)
        'intervalle_maj' => 60000,
        
        // Timeout pour les requêtes cURL en secondes
        'timeout_curl' => 30,
        
        // Durée de transition de la barre de progression en secondes
        'duree_transition' => 1,
        
        // Fichier de données JSON
        'fichier_donnees' => 'data.json',
        
        // User-Agent pour les requêtes HTTP
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    ],

    // ===== MESSAGES PERSONNALISABLES =====
    'messages' => [
        // Message affiché pendant le chargement
        'chargement' => 'Chargement...',
        
        // Message affiché en cas d'erreur
        'erreur' => 'Erreur de chargement',
        
        // Format d'affichage du montant (utilise sprintf)
        'format_montant' => '%s€ / %s€',
        
        // Messages Discord
        'discord_titre_contribution' => '🎉 Nouvelle contribution !',
        'discord_titre_mise_a_jour' => '📊 Montant mis à jour',
        'discord_titre_actualisation' => '🔄 Données actualisées',
        'discord_footer' => 'Cagnotte Twitch',
    ],
];
?>