<?php
/**
 * Système de notifications Twitch Chat
 * 
 * Ce fichier gère l'envoi de messages automatiques sur le chat Twitch
 * lors des contributions à la cagnotte Lydia.
 * 
 * Utilise l'API Twitch Helix pour envoyer des messages via un bot.
 */

class TwitchChatBot {
    private $clientId;
    private $accessToken;
    private $broadcasterId;
    private $botUserId;
    private $fichierDonnees;
    private $config;
    
    /**
     * Constructeur de la classe TwitchChatBot
     * 
     * @param string $clientId ID client de l'application Twitch
     * @param string $accessToken Token d'accès OAuth pour le bot
     * @param string $broadcasterId ID du streamer (channel)
     * @param string $botUserId ID du bot utilisateur
     * @param string $fichierDonnees Chemin vers le fichier de données JSON
     * @param array $config Configuration générale du système
     */
    public function __construct($clientId, $accessToken, $broadcasterId, $botUserId, $fichierDonnees, $config) {
        $this->clientId = $clientId;
        $this->accessToken = $accessToken;
        $this->broadcasterId = $broadcasterId;
        $this->botUserId = $botUserId;
        $this->fichierDonnees = $fichierDonnees;
        $this->config = $config;
    }
    
    /**
     * Envoie un message sur le chat Twitch
     * 
     * @param string $message Le message à envoyer
     * @return array Résultat de l'envoi (success, error)
     */
    public function envoyerMessage($message) {
        // Vérification des paramètres requis
        if (empty($this->clientId) || empty($this->accessToken) || empty($this->broadcasterId) || empty($this->botUserId)) {
            return [
                'success' => false,
                'error' => 'Configuration Twitch incomplète'
            ];
        }
        
        // URL de l'API Twitch Helix pour envoyer des messages
        $url = 'https://api.twitch.tv/helix/chat/messages';
        
        // Données à envoyer
        $data = [
            'broadcaster_id' => $this->broadcasterId,
            'sender_id' => $this->botUserId,
            'message' => $message
        ];
        
        // Configuration de la requête cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['technique']['timeout_curl']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Client-Id: ' . $this->clientId,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Gestion des erreurs cURL
        if ($response === false) {
            return [
                'success' => false,
                'error' => 'Erreur cURL: ' . $curlError
            ];
        }
        
        // Vérification du code de réponse HTTP
        if ($httpCode !== 200 && $httpCode !== 204) {
            $responseData = json_decode($response, true);
            $errorMessage = isset($responseData['message']) ? $responseData['message'] : 'Erreur HTTP: ' . $httpCode;
            
            return [
                'success' => false,
                'error' => $errorMessage,
                'http_code' => $httpCode,
                'response' => $response
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Message envoyé avec succès sur Twitch',
            'http_code' => $httpCode
        ];
    }
    
    /**
     * Sauvegarde les données et envoie une notification Twitch si nécessaire
     * 
     * @param float $nouveauMontant Le nouveau montant de la cagnotte
     * @param float $objectif L'objectif de la cagnotte
     * @return bool True si une notification a été envoyée, false sinon
     */
    public function sauvegarderAvecNotification($nouveauMontant, $objectif) {
        // Chargement des données existantes
        $donneesExistantes = $this->chargerDonnees();
        $ancienMontant = $donneesExistantes ? $donneesExistantes['montant'] : 0;
        
        // Préparation des nouvelles données
        $nouvellesDonnees = [
            'montant' => $nouveauMontant,
            'objectif' => $objectif,
            'derniere_maj' => date('Y-m-d H:i:s')
        ];
        
        // Sauvegarde des données
        if (file_put_contents($this->fichierDonnees, json_encode($nouvellesDonnees, JSON_PRETTY_PRINT)) === false) {
            return false;
        }
        
        // Vérification s'il y a eu une contribution (augmentation du montant)
        if ($nouveauMontant > $ancienMontant && $ancienMontant > 0) {
            $contribution = $nouveauMontant - $ancienMontant;
            $message = $this->genererMessageContribution($contribution, $nouveauMontant, $objectif);
            
            $resultat = $this->envoyerMessage($message);
            return $resultat['success'];
        }
        
        return false; // Pas de notification envoyée
    }
    
    /**
     * Génère un message de contribution pour le chat Twitch
     * 
     * @param float $contribution Montant de la contribution
     * @param float $montantTotal Montant total actuel
     * @param float $objectif Objectif de la cagnotte
     * @return string Le message formaté
     */
    private function genererMessageContribution($contribution, $montantTotal, $objectif) {
        $pourcentage = $objectif > 0 ? round(($montantTotal / $objectif) * 100, 1) : 0;
        $contributionFormatee = number_format($contribution, 2, ',', ' ');
        $montantFormate = number_format($montantTotal, 2, ',', ' ');
        $objectifFormate = number_format($objectif, 2, ',', ' ');
        
        // Utilisation du message configuré ou message par défaut
        $template = isset($this->config['messages']['twitch_contribution']) 
            ? $this->config['messages']['twitch_contribution']
            : $this->config['twitch']['message_contribution'];
        
        // Remplacement des variables dans le template
        $message = str_replace([
            '{contribution}',
            '{montant_total}',
            '{objectif}',
            '{pourcentage}'
        ], [
            $contributionFormatee . '€',
            $montantFormate . '€',
            $objectifFormate . '€',
            $pourcentage . '%'
        ], $template);
        
        return $message;
    }
    
    /**
     * Charge les données existantes depuis le fichier JSON
     * 
     * @return array|null Les données chargées ou null en cas d'erreur
     */
    private function chargerDonnees() {
        if (!file_exists($this->fichierDonnees)) {
            return null;
        }
        
        $contenu = file_get_contents($this->fichierDonnees);
        if ($contenu === false) {
            return null;
        }
        
        $donnees = json_decode($contenu, true);
        return $donnees ?: null;
    }
    
    /**
     * Teste la connexion à l'API Twitch
     * 
     * @return array Résultat du test (success, error, user_info)
     */
    public function testerConnexion() {
        // Test avec l'endpoint de validation du token
        $url = 'https://id.twitch.tv/oauth2/validate';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['technique']['timeout_curl']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($response === false) {
            return [
                'success' => false,
                'error' => 'Erreur cURL: ' . $curlError
            ];
        }
        
        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => 'Token invalide ou expiré (HTTP: ' . $httpCode . ')'
            ];
        }
        
        $data = json_decode($response, true);
        
        return [
            'success' => true,
            'user_info' => $data,
            'message' => 'Connexion Twitch réussie'
        ];
    }
    
    /**
     * Envoie un message de test sur le chat
     * 
     * @return array Résultat de l'envoi du message de test
     */
    public function envoyerMessageTest() {
        $messageTest = isset($this->config['twitch']['message_test']) 
            ? $this->config['twitch']['message_test']
            : '🤖 Test du bot de cagnotte - Tout fonctionne !';
            
        return $this->envoyerMessage($messageTest);
    }
}

/**
 * Fonction utilitaire pour créer une instance TwitchChatBot depuis la configuration
 * 
 * @param array $config Configuration complète du système
 * @param string $fichierDonnees Chemin vers le fichier de données
 * @return TwitchChatBot|null Instance du bot ou null si la configuration est incomplète
 */
function creerBotTwitch($config, $fichierDonnees) {
    // Vérification que la configuration Twitch existe et est active
    if (!isset($config['twitch']) || !$config['twitch']['actif']) {
        return null;
    }
    
    $twitchConfig = $config['twitch'];
    
    // Vérification des paramètres requis
    $parametresRequis = ['client_id', 'access_token', 'broadcaster_id', 'bot_user_id'];
    foreach ($parametresRequis as $param) {
        if (empty($twitchConfig[$param]) || strpos($twitchConfig[$param], 'VOTRE_') !== false) {
            return null;
        }
    }
    
    return new TwitchChatBot(
        $twitchConfig['client_id'],
        $twitchConfig['access_token'],
        $twitchConfig['broadcaster_id'],
        $twitchConfig['bot_user_id'],
        $fichierDonnees,
        $config
    );
}
?>