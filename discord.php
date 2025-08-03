<?php
/**
 * Système d'intégration Discord avec webhook
 * Envoie des notifications à chaque modification du montant de la cagnotte
 */

// Charger la configuration seulement si elle n'est pas déjà disponible
if (!isset($config) || !is_array($config)) {
    $config = require __DIR__ . '/config.php';
}

class DiscordWebhook {
    private $webhookUrl;
    private $dataFile;
    
    /**
     * Constructeur
     * @param string $webhookUrl URL du webhook Discord
     * @param string $dataFile Chemin vers le fichier data.json
     */
    public function __construct($webhookUrl, $dataFile = 'data.json') {
        $this->webhookUrl = $webhookUrl;
        $this->dataFile = $dataFile;
    }
    
    /**
     * Envoie un message vers Discord
     * @param array $data Données à envoyer
     * @return bool Succès de l'envoi
     */
    private function envoyerMessage($data) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode >= 200 && $httpCode < 300;
    }
    
    /**
     * Crée un embed Discord pour la notification
     * @param float $ancienMontant Ancien montant
     * @param float $nouveauMontant Nouveau montant
     * @param float $objectif Objectif de la cagnotte
     * @return array Données de l'embed
     */
    private function creerEmbed($ancienMontant, $nouveauMontant, $objectif) {
        global $config;
        
        $difference = $nouveauMontant - $ancienMontant;
        $pourcentage = round(($nouveauMontant / $objectif) * 100, 1);
        
        // Couleur selon le type de changement
        $couleur = $difference > 0 ? 0x00ff00 : ($difference < 0 ? 0xff0000 : 0xffff00);
        
        // Emoji selon le type de changement
        $emoji = $difference > 0 ? '💰' : ($difference < 0 ? '📉' : '🔄');
        
        // Titre selon le type de changement avec vérifications de sécurité
        $titres_defaut = [
            'contribution' => '🎉 Nouvelle contribution !',
            'mise_a_jour' => '📊 Montant mis à jour',
            'actualisation' => '🔄 Données actualisées'
        ];
        
        if ($difference > 0) {
            $titre = isset($config['messages']['discord_titre_contribution']) 
                ? $config['messages']['discord_titre_contribution'] 
                : $titres_defaut['contribution'];
        } elseif ($difference < 0) {
            $titre = isset($config['messages']['discord_titre_mise_a_jour']) 
                ? $config['messages']['discord_titre_mise_a_jour'] 
                : $titres_defaut['mise_a_jour'];
        } else {
            $titre = isset($config['messages']['discord_titre_actualisation']) 
                ? $config['messages']['discord_titre_actualisation'] 
                : $titres_defaut['actualisation'];
        }
        
        // Création de la barre de progression visuelle
        $barreLength = 20;
        $progression = min($barreLength, round(($nouveauMontant / $objectif) * $barreLength));
        $barre = str_repeat('█', $progression) . str_repeat('░', $barreLength - $progression);
        
        $data = [
            "embeds" => [[
                "title" => $titre,
                "color" => $couleur,
                "fields" => [
                    [
                        "name" => "💵 Montant actuel",
                        "value" => "**{$nouveauMontant}€**",
                        "inline" => true
                    ],
                    [
                        "name" => "🎯 Objectif",
                        "value" => "**{$objectif}€**",
                        "inline" => true
                    ],
                    [
                        "name" => "📊 Progression",
                        "value" => "**{$pourcentage}%**",
                        "inline" => true
                    ],
                    [
                        "name" => "📈 Barre de progression",
                        "value" => "`{$barre}` {$pourcentage}%",
                        "inline" => false
                    ]
                ],
                "footer" => [
                    "text" => (isset($config['messages']['discord_footer']) ? $config['messages']['discord_footer'] : 'Cagnotte Twitch') . " • " . date('d/m/Y à H:i:s')
                ],
                "timestamp" => date('c')
            ]]
        ];
        
        // Ajout du champ différence si il y a eu un changement
        if ($difference != 0) {
            $embed = &$data["embeds"][0];
            $embed["fields"][] = [
                "name" => $difference > 0 ? "➕ Contribution" : "➖ Différence",
                "value" => ($difference > 0 ? "+" : "") . "{$difference}€",
                "inline" => true
            ];
        }
        
        return $data;
    }
    
    /**
     * Notifie Discord d'un changement de montant
     * @param float $ancienMontant Ancien montant
     * @param float $nouveauMontant Nouveau montant
     * @param float $objectif Objectif de la cagnotte
     * @return bool Succès de l'envoi
     */
    public function notifierChangement($ancienMontant, $nouveauMontant, $objectif) {
        $data = $this->creerEmbed($ancienMontant, $nouveauMontant, $objectif);
        return $this->envoyerMessage($data);
    }
    
    /**
     * Envoie un message de test
     * @return bool Succès de l'envoi
     */
    public function testerWebhook() {
        $data = [
            "content" => "🧪 **Test du webhook Discord**",
            "embeds" => [[
                "title" => "✅ Connexion établie",
                "description" => "Le système de notification Discord fonctionne correctement !",
                "color" => 0x00ff00,
                "footer" => [
                    "text" => "Test effectué le " . date('d/m/Y à H:i:s')
                ]
            ]]
        ];
        
        return $this->envoyerMessage($data);
    }
    
    /**
     * Lit les données actuelles de la cagnotte
     * @return array|null Données de la cagnotte ou null si erreur
     */
    public function lireDonnees() {
        if (!file_exists($this->dataFile)) {
            return null;
        }
        
        $contenu = file_get_contents($this->dataFile);
        if ($contenu === false) {
            return null;
        }
        
        $donnees = json_decode($contenu, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $donnees;
    }
    
    /**
     * Sauvegarde les données avec notification Discord
     * @param float $montant Nouveau montant
     * @param float $objectif Objectif
     * @return bool Succès de la sauvegarde et notification
     */
    public function sauvegarderAvecNotification($montant, $objectif) {
        // Lecture des données existantes avec vérifications
        $anciennesDonnees = $this->lireDonnees();
        $ancienMontant = 0;
        
        // Préparation des nouvelles données en préservant les données existantes
        $nouvellesdonnees = $anciennesDonnees && is_array($anciennesDonnees) ? $anciennesDonnees : [];
        
        // Vérification que les données sont valides et contiennent le montant
        if (isset($nouvellesdonnees['montant'])) {
            $ancienMontant = floatval($nouvellesdonnees['montant']);
        }
        
        // Mise à jour des champs nécessaires en préservant le reste
        $nouvellesdonnees['montant'] = $montant;
        $nouvellesdonnees['objectif'] = $objectif;
        $nouvellesdonnees['derniere_maj'] = date('Y-m-d H:i:s');
        
        $succes = file_put_contents($this->dataFile, json_encode($nouvellesdonnees, JSON_PRETTY_PRINT));
        
        // Notification Discord seulement si le montant a changé
        if ($succes && $montant != $ancienMontant) {
            $this->notifierChangement($ancienMontant, $montant, $objectif);
        }
        
        return $succes !== false;
    }
}

// Configuration - Utilise la configuration centralisée avec vérifications
$WEBHOOK_URL = isset($config['discord']['webhook_url']) ? $config['discord']['webhook_url'] : '';

// Utilisation si le fichier est appelé directement
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    // Vérification de l'URL du webhook
    if (strpos($WEBHOOK_URL, "VOTRE_WEBHOOK") !== false) {
        echo "❌ Erreur: Veuillez configurer l'URL du webhook Discord dans le fichier discord.php\n";
        echo "📝 Instructions:\n";
        echo "1. Allez dans votre serveur Discord\n";
        echo "2. Paramètres du canal > Intégrations > Webhooks\n";
        echo "3. Créez un nouveau webhook\n";
        echo "4. Copiez l'URL et remplacez \$WEBHOOK_URL dans ce fichier\n";
        exit(1);
    }
    
    $discord = new DiscordWebhook($WEBHOOK_URL);
    
    // Test du webhook
    echo "🧪 Test du webhook Discord...\n";
    if ($discord->testerWebhook()) {
        echo "✅ Webhook Discord configuré avec succès !\n";
    } else {
        echo "❌ Erreur lors du test du webhook Discord\n";
    }
}
?>