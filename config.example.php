<?php
/**
 * Configuration d'exemple du système d'overlay de cagnotte
 * 
 * Ce fichier contient toutes les données personnalisables du système.
 * Copiez ce fichier vers config.php et modifiez les valeurs selon vos besoins.
 * 
 * IMPORTANT : Après modification, aucun autre fichier ne doit être modifié.
 */

return array (
  'lydia' => 
  array (
    'url' => 'https://pots.lydia.me/collect/pots?id=VOTRE-ID-CAGNOTTE',  // Remplacez par votre URL Lydia
    'objectif' => 500,  // Objectif de votre cagnotte en euros
  ),
  'discord' => 
  array (
    'webhook_url' => 'https://discord.com/api/webhooks/VOTRE_WEBHOOK_ID/VOTRE_WEBHOOK_TOKEN',  // URL de votre webhook Discord
    'actif' => true,  // true pour activer les notifications Discord, false pour désactiver
  ),
  'twitch' => 
  array (
    'actif' => false,                    // true pour activer les messages Twitch, false pour désactiver
    'client_id' => 'VOTRE_CLIENT_ID',    // Client ID de votre application Twitch
    'access_token' => 'VOTRE_ACCESS_TOKEN',  // Token d'accès OAuth du bot
    'broadcaster_id' => 'VOTRE_BROADCASTER_ID',  // ID du streamer (votre ID utilisateur)
    'bot_user_id' => 'VOTRE_BOT_USER_ID',        // ID utilisateur du bot
    'message_contribution' => '🎉 Merci pour la contribution de {contribution} ! On est maintenant à {total}€ sur {objectif}€ ({pourcentage}%) !',  // Message lors d'une contribution
    'message_test' => '🤖 Test du bot de cagnotte - Tout fonctionne !',  // Message de test
  ),
  'apparence' => 
  array (
    // Couleurs de la barre de progression (dégradé)
    'couleur_debut' => '#ffc400',    // Couleur de début du dégradé (jaune)
    'couleur_fin' => '#ff6600',      // Couleur de fin du dégradé (orange)
    'couleur_bordure' => '#ffffff',  // Couleur de la bordure (blanc)
    'couleur_fond' => 'rgba(0,0,0,0.7)',     // Couleur de fond avec transparence
    'couleur_texte' => '#ffffff',             // Couleur du texte (blanc)
    
    // Dimensions de la barre
    'largeur' => 400,                // Largeur de la barre en pixels
    'hauteur' => 50,                 // Hauteur de la barre en pixels
    'bordure_epaisseur' => 3,        // Épaisseur de la bordure en pixels
    'bordure_rayon' => 10,           // Rayon des coins arrondis en pixels
    
    // Police et texte
    'taille_police' => 20,           // Taille de la police en pixels
    'poids_police' => 900,           // Poids de la police (100-900, 900 = très gras)
    'texte_personnalise' => 'Objectif Stream',  // Texte à afficher au-dessus (vide = pas de texte)
    'espacement_texte' => 10,        // Espacement entre le texte et la barre en pixels
    
    // Position de l'overlay sur l'écran
    'position_horizontale' => 'droite',  // 'gauche' ou 'droite'
    'position_verticale' => 'bas',       // 'haut' ou 'bas'
    'marge_horizontale' => 10,           // Marge depuis le bord horizontal en pixels
    'marge_verticale' => 10,             // Marge depuis le bord vertical en pixels
  ),
  'audio' => 
  array (
    'fichier' => 'caisse.mp3',       // Nom du fichier audio (doit être dans le même dossier)
    'volume' => 0.7,                 // Volume du son (0.0 = muet, 1.0 = volume max)
    'actif' => true,                 // true pour activer le son, false pour désactiver
    'formats_supportes' => 
    array (
      'mp3' => 'audio/mpeg',         // Format MP3
      'wav' => 'audio/wav',          // Format WAV
      'ogg' => 'audio/ogg',          // Format OGG
    ),
  ),
  'technique' => 
  array (
    'intervalle_maj' => 60000,       // Intervalle de mise à jour en millisecondes (60000 = 1 minute)
    'timeout_curl' => 30,            // Timeout des requêtes HTTP en secondes
    'duree_transition' => 1,         // Durée des animations en secondes
    'fichier_donnees' => 'data.json', // Nom du fichier de données (ne pas modifier)
    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',  // User agent pour les requêtes
  ),
  'messages' => 
  array (
    // Messages affichés dans l'overlay
    'chargement' => 'Chargement...',
    'erreur' => 'Erreur de chargement',
    'format_montant' => '%s€ / %s€',
    
    // Messages Discord
    'discord_titre_contribution' => '🎉 Nouvelle contribution !',
    'discord_titre_mise_a_jour' => '📊 Montant mis à jour',
    'discord_titre_actualisation' => '🔄 Données actualisées',
    'discord_footer' => 'Cagnotte Twitch',
  ),
  'admin' => 
  array (
    'code_connexion' => 'CHANGEZ_MOI',   // Code de connexion pour l'interface d'administration (CHANGEZ-LE ABSOLUMENT !)
    'utilise_hash' => false,             // true = utilise password_hash() (recommandé), false = texte brut (moins sécurisé)
    'duree_session' => 3600,             // Durée de session en secondes (3600 = 1 heure)
    'nom_session' => 'cagnotte_admin',   // Nom de la session (ne pas modifier)
  ),
);
?>