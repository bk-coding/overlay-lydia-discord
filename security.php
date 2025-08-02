<?php
/**
 * Classe de sécurité pour l'interface d'administration
 * Gère l'authentification sécurisée, la validation des données et la protection CSRF
 */

class SecurityManager {
    private $config;
    private $sessionName;
    
    /**
     * Constructeur
     * @param array $config Configuration du système
     */
    public function __construct($config) {
        $this->config = $config;
        $this->sessionName = $config['admin']['nom_session'];
        
        // Configuration sécurisée de la session
        $this->configureSession();
    }
    
    /**
     * Configure les paramètres de sécurité de la session
     */
    private function configureSession() {
        // Paramètres de sécurité pour les sessions
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        
        // Régénération de l'ID de session pour éviter la fixation
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Génère un hash sécurisé du mot de passe
     * @param string $password Mot de passe en clair
     * @return string Hash sécurisé
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 itérations
            'threads' => 3          // 3 threads
        ]);
    }
    
    /**
     * Vérifie un mot de passe contre son hash
     * @param string $password Mot de passe en clair
     * @param string $hash Hash stocké
     * @return bool True si le mot de passe est correct
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Génère un token CSRF
     * @return string Token CSRF
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Vérifie un token CSRF
     * @param string $token Token à vérifier
     * @return bool True si le token est valide
     */
    public function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Authentifie un utilisateur
     * @param string $password Mot de passe fourni
     * @return array Résultat de l'authentification
     */
    public function authenticate($password) {
        // Limitation du taux de tentatives
        if (!$this->checkAuthRateLimit()) {
            return [
                'success' => false,
                'error' => 'Trop de tentatives de connexion. Veuillez attendre avant de réessayer.',
                'lockout_time' => $this->getRemainingLockoutTime()
            ];
        }
        
        // Vérification du mot de passe
        $storedHash = $this->config['admin']['password_hash'] ?? '';
        
        // Si pas de hash stocké, utiliser l'ancien système pour la migration
        if (empty($storedHash)) {
            $isValid = ($password === $this->config['admin']['code_connexion']);
        } else {
            $isValid = $this->verifyPassword($password, $storedHash);
        }
        
        if ($isValid) {
            // Connexion réussie
            $this->resetRateLimit();
            $this->createSession();
            $this->logSecurityEvent('login_success', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            
            return ['success' => true];
        } else {
            // Connexion échouée
            $this->recordFailedAttempt();
            $this->logSecurityEvent('login_failed', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            
            return [
                'success' => false,
                'error' => 'Code de connexion incorrect'
            ];
        }
    }
    
    /**
     * Crée une session authentifiée
     */
    private function createSession() {
        // Régénérer l'ID de session pour éviter la fixation
        session_regenerate_id(true);
        
        $_SESSION[$this->sessionName] = [
            'expires' => time() + $this->config['admin']['duree_session'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created' => time()
        ];
    }
    
    /**
     * Vérifie si l'utilisateur est authentifié
     * @return bool True si authentifié
     */
    public function isAuthenticated() {
        if (!isset($_SESSION[$this->sessionName])) {
            return false;
        }
        
        $session = $_SESSION[$this->sessionName];
        
        // Vérifier l'expiration
        if ($session['expires'] < time()) {
            $this->logout();
            return false;
        }
        
        // Vérifier l'IP (optionnel, peut être désactivé si problématique)
        if (isset($session['ip']) && $session['ip'] !== ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
            $this->logSecurityEvent('session_ip_mismatch', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            // Ne pas déconnecter automatiquement car l'IP peut changer légitimement
        }
        
        return true;
    }
    
    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        unset($_SESSION[$this->sessionName]);
        unset($_SESSION['csrf_token']);
        
        // Régénérer l'ID de session
        session_regenerate_id(true);
        
        $this->logSecurityEvent('logout', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }
    
    /**
     * Vérifie la limitation du taux de tentatives d'authentification
     * @return bool True si autorisé
     */
    private function checkAuthRateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'rate_limit_' . md5($ip);
        
        if (!isset($_SESSION[$key])) {
            return true;
        }
        
        $attempts = $_SESSION[$key];
        
        // Si plus de 5 tentatives en 15 minutes, bloquer
        if ($attempts['count'] >= 5 && (time() - $attempts['first_attempt']) < 900) {
            return false;
        }
        
        // Réinitialiser si la fenêtre de temps est passée
        if ((time() - $attempts['first_attempt']) >= 900) {
            unset($_SESSION[$key]);
            return true;
        }
        
        return true;
    }
    
    /**
     * Enregistre une tentative échouée
     */
    private function recordFailedAttempt() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'rate_limit_' . md5($ip);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 1,
                'first_attempt' => time()
            ];
        } else {
            $_SESSION[$key]['count']++;
        }
    }
    
    /**
     * Réinitialise la limitation du taux
     */
    private function resetRateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'rate_limit_' . md5($ip);
        unset($_SESSION[$key]);
    }
    
    /**
     * Obtient le temps restant de blocage
     * @return int Secondes restantes
     */
    private function getRemainingLockoutTime() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'rate_limit_' . md5($ip);
        
        if (!isset($_SESSION[$key])) {
            return 0;
        }
        
        $attempts = $_SESSION[$key];
        $elapsed = time() - $attempts['first_attempt'];
        
        return max(0, 900 - $elapsed); // 15 minutes = 900 secondes
    }
    
    /**
     * Valide et nettoie les données d'entrée
     * @param array $data Données à valider
     * @return array Données validées
     */
    public function validateConfigData($data) {
        $validated = [];
        
        // Validation Lydia
        $validated['lydia'] = [
            'url' => $this->validateURL($data['lydia_url'] ?? ''),
            'objectif' => $this->validateInteger($data['lydia_objectif'] ?? 0, 1, 999999)
        ];
        
        // Validation Discord
        $validated['discord'] = [
            'webhook_url' => $this->validateURL($data['discord_webhook_url'] ?? '', true),
            'actif' => isset($data['discord_actif'])
        ];
        
        // Validation Apparence
        $validated['apparence'] = [
            'couleur_debut' => $this->validateColor($data['apparence_couleur_debut'] ?? '#ffc400'),
            'couleur_fin' => $this->validateColor($data['apparence_couleur_fin'] ?? '#ff6600'),
            'couleur_bordure' => $this->validateColor($data['apparence_couleur_bordure'] ?? '#ffffff'),
            'couleur_fond' => $this->validateString($data['apparence_couleur_fond'] ?? 'rgba(0,0,0,0.7)', 50),
            'couleur_texte' => $this->validateColor($data['apparence_couleur_texte'] ?? '#ffffff'),
            'largeur' => $this->validateInteger($data['apparence_largeur'] ?? 400, 100, 2000),
            'hauteur' => $this->validateInteger($data['apparence_hauteur'] ?? 50, 20, 200),
            'bordure_epaisseur' => $this->validateInteger($data['apparence_bordure_epaisseur'] ?? 3, 0, 20),
            'bordure_rayon' => $this->validateInteger($data['apparence_bordure_rayon'] ?? 10, 0, 50),
            'taille_police' => $this->validateInteger($data['apparence_taille_police'] ?? 20, 8, 72),
            'poids_police' => $this->validateInteger($data['apparence_poids_police'] ?? 900, 100, 900),
            'texte_personnalise' => $this->validateString($data['apparence_texte_personnalise'] ?? '', 200),
            'espacement_texte' => $this->validateInteger($data['apparence_espacement_texte'] ?? 10, 0, 100),
            'position_horizontale' => $this->validateEnum($data['apparence_position_horizontale'] ?? 'droite', ['gauche', 'droite']),
            'position_verticale' => $this->validateEnum($data['apparence_position_verticale'] ?? 'bas', ['haut', 'bas']),
            'marge_horizontale' => $this->validateInteger($data['apparence_marge_horizontale'] ?? 10, 0, 500),
            'marge_verticale' => $this->validateInteger($data['apparence_marge_verticale'] ?? 10, 0, 500)
        ];
        
        // Validation Audio
        $validated['audio'] = [
            'fichier' => $this->validateFilename($data['audio_fichier'] ?? 'caisse.mp3'),
            'volume' => $this->validateFloat($data['audio_volume'] ?? 0.7, 0.0, 1.0),
            'actif' => isset($data['audio_actif'])
        ];
        
        // Validation Technique
        $validated['technique'] = [
            'intervalle_maj' => $this->validateInteger($data['technique_intervalle_maj'] ?? 60000, 5000, 300000),
            'timeout_curl' => $this->validateInteger($data['technique_timeout_curl'] ?? 30, 5, 120),
            'duree_transition' => $this->validateInteger($data['technique_duree_transition'] ?? 1, 0, 10),
            'fichier_donnees' => $this->validateFilename($data['technique_fichier_donnees'] ?? 'data.json'),
            'user_agent' => $this->validateString($data['technique_user_agent'] ?? '', 500)
        ];
        
        // Validation Messages
        $validated['messages'] = [
            'chargement' => $this->validateString($data['messages_chargement'] ?? 'Chargement...', 100),
            'erreur' => $this->validateString($data['messages_erreur'] ?? 'Erreur de chargement', 100),
            'format_montant' => $this->validateString($data['messages_format_montant'] ?? '%s€ / %s€', 50),
            'discord_titre_contribution' => $this->validateString($data['messages_discord_titre_contribution'] ?? '🎉 Nouvelle contribution !', 100),
            'discord_titre_mise_a_jour' => $this->validateString($data['messages_discord_titre_mise_a_jour'] ?? '📊 Montant mis à jour', 100),
            'discord_titre_actualisation' => $this->validateString($data['messages_discord_titre_actualisation'] ?? '🔄 Données actualisées', 100),
            'discord_footer' => $this->validateString($data['messages_discord_footer'] ?? 'Cagnotte Twitch', 100)
        ];
        
        return $validated;
    }
    
    /**
     * Valide une URL
     * @param string $url URL à valider
     * @param bool $allowEmpty Autoriser une URL vide
     * @return string URL validée
     */
    private function validateURL($url, $allowEmpty = false) {
        $url = trim($url);
        
        if (empty($url) && $allowEmpty) {
            return '';
        }
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("URL invalide: $url");
        }
        
        return $url;
    }
    
    /**
     * Valide un entier dans une plage
     * @param mixed $value Valeur à valider
     * @param int $min Valeur minimale
     * @param int $max Valeur maximale
     * @return int Entier validé
     */
    private function validateInteger($value, $min, $max) {
        $int = filter_var($value, FILTER_VALIDATE_INT);
        
        if ($int === false || $int < $min || $int > $max) {
            throw new InvalidArgumentException("Valeur entière invalide: $value (doit être entre $min et $max)");
        }
        
        return $int;
    }
    
    /**
     * Valide un nombre décimal dans une plage
     * @param mixed $value Valeur à valider
     * @param float $min Valeur minimale
     * @param float $max Valeur maximale
     * @return float Nombre validé
     */
    private function validateFloat($value, $min, $max) {
        $float = filter_var($value, FILTER_VALIDATE_FLOAT);
        
        if ($float === false || $float < $min || $float > $max) {
            throw new InvalidArgumentException("Valeur décimale invalide: $value (doit être entre $min et $max)");
        }
        
        return $float;
    }
    
    /**
     * Valide une chaîne de caractères
     * @param string $value Valeur à valider
     * @param int $maxLength Longueur maximale
     * @return string Chaîne validée
     */
    private function validateString($value, $maxLength) {
        $value = trim($value);
        
        if (strlen($value) > $maxLength) {
            throw new InvalidArgumentException("Chaîne trop longue: maximum $maxLength caractères");
        }
        
        // Nettoyer les caractères dangereux
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        return $value;
    }
    
    /**
     * Valide une couleur hexadécimale
     * @param string $color Couleur à valider
     * @return string Couleur validée
     */
    private function validateColor($color) {
        $color = trim($color);
        
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            throw new InvalidArgumentException("Couleur invalide: $color (format attendu: #RRGGBB)");
        }
        
        return $color;
    }
    
    /**
     * Valide une valeur dans une énumération
     * @param string $value Valeur à valider
     * @param array $allowedValues Valeurs autorisées
     * @return string Valeur validée
     */
    private function validateEnum($value, $allowedValues) {
        if (!in_array($value, $allowedValues, true)) {
            throw new InvalidArgumentException("Valeur invalide: $value (valeurs autorisées: " . implode(', ', $allowedValues) . ")");
        }
        
        return $value;
    }
    
    /**
     * Valide un nom de fichier
     * @param string $filename Nom de fichier à valider
     * @return string Nom de fichier validé
     */
    private function validateFilename($filename) {
        $filename = trim($filename);
        
        // Vérifier les caractères dangereux
        if (preg_match('/[^a-zA-Z0-9._-]/', $filename)) {
            throw new InvalidArgumentException("Nom de fichier invalide: $filename");
        }
        
        // Vérifier la longueur
        if (strlen($filename) > 255) {
            throw new InvalidArgumentException("Nom de fichier trop long: $filename");
        }
        
        return $filename;
    }
    
    /**
     * Valider les données selon le type spécifié
     * 
     * @param mixed $value Valeur à valider
     * @param string $type Type de validation
     * @param array $options Options de validation
     * @return array Résultat de la validation
     */
    public function validateData($value, $type, $options = []) {
        switch ($type) {
            case 'url':
                if (empty($value)) {
                    return ['valid' => false, 'message' => 'URL requise'];
                }
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return ['valid' => false, 'message' => 'URL invalide'];
                }
                return ['valid' => true, 'value' => $value];
                
            case 'integer':
                if (!is_numeric($value)) {
                    return ['valid' => false, 'message' => 'Doit être un nombre entier'];
                }
                $intValue = (int)$value;
                if (isset($options['min']) && $intValue < $options['min']) {
                    return ['valid' => false, 'message' => "Doit être supérieur ou égal à {$options['min']}"];
                }
                if (isset($options['max']) && $intValue > $options['max']) {
                    return ['valid' => false, 'message' => "Doit être inférieur ou égal à {$options['max']}"];
                }
                return ['valid' => true, 'value' => $intValue];
                
            case 'float':
                if (!is_numeric($value)) {
                    return ['valid' => false, 'message' => 'Doit être un nombre décimal'];
                }
                $floatValue = (float)$value;
                if (isset($options['min']) && $floatValue < $options['min']) {
                    return ['valid' => false, 'message' => "Doit être supérieur ou égal à {$options['min']}"];
                }
                if (isset($options['max']) && $floatValue > $options['max']) {
                    return ['valid' => false, 'message' => "Doit être inférieur ou égal à {$options['max']}"];
                }
                return ['valid' => true, 'value' => $floatValue];
                
            case 'string':
                if (isset($options['max_length']) && strlen($value) > $options['max_length']) {
                    return ['valid' => false, 'message' => "Doit contenir au maximum {$options['max_length']} caractères"];
                }
                if (isset($options['min_length']) && strlen($value) < $options['min_length']) {
                    return ['valid' => false, 'message' => "Doit contenir au minimum {$options['min_length']} caractères"];
                }
                // Vérifier les caractères dangereux
                if (preg_match('/[<>"\']/', $value)) {
                    return ['valid' => false, 'message' => 'Contient des caractères non autorisés'];
                }
                return ['valid' => true, 'value' => htmlspecialchars($value, ENT_QUOTES, 'UTF-8')];
                
            case 'color':
                if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                    return ['valid' => false, 'message' => 'Doit être une couleur hexadécimale valide (#RRGGBB)'];
                }
                return ['valid' => true, 'value' => $value];
                
            case 'enum':
                if (!isset($options['values']) || !in_array($value, $options['values'])) {
                    $allowedValues = isset($options['values']) ? implode(', ', $options['values']) : 'aucune valeur définie';
                    return ['valid' => false, 'message' => "Valeur non autorisée. Valeurs autorisées : {$allowedValues}"];
                }
                return ['valid' => true, 'value' => $value];
                
            case 'filename':
                if (empty($value)) {
                    return ['valid' => true, 'value' => $value]; // Fichier optionnel
                }
                // Vérifier les caractères dangereux dans les noms de fichiers
                if (preg_match('/[\/\\\\:*?"<>|]/', $value)) {
                    return ['valid' => false, 'message' => 'Nom de fichier contient des caractères non autorisés'];
                }
                // Vérifier l'extension
                $allowedExtensions = ['mp3', 'wav', 'ogg', 'mp4', 'webm'];
                $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));
                if (!empty($extension) && !in_array($extension, $allowedExtensions)) {
                    return ['valid' => false, 'message' => 'Extension de fichier non autorisée'];
                }
                return ['valid' => true, 'value' => $value];
                
            default:
                return ['valid' => false, 'message' => 'Type de validation non supporté'];
        }
    }
    
    /**
     * Vérifie un token d'API pour l'accès aux endpoints
     * @param string $token Token à vérifier
     * @return bool True si le token est valide
     */
    public function verifyAPIToken($token) {
        if (empty($token)) {
            return false;
        }
        
        // Générer un token basé sur la configuration et la date
        $expectedToken = hash('sha256', $this->config['admin']['password_hash'] . date('Y-m-d'));
        
        return hash_equals($expectedToken, $token);
    }
    
    /**
     * Génère un token d'API valide pour la journée courante
     * @return string Token d'API
     */
    public function generateAPIToken() {
        return hash('sha256', $this->config['admin']['password_hash'] . date('Y-m-d'));
    }
    
    /**
     * Vérifie la limitation de taux (rate limiting) pour une clé donnée
     * @param string $key Clé unique pour identifier la source
     * @param int $maxRequests Nombre maximum de requêtes
     * @param int $timeWindow Fenêtre de temps en secondes
     * @return bool True si la requête est autorisée
     */
    public function checkRateLimit($key, $maxRequests, $timeWindow) {
        $rateLimitFile = __DIR__ . '/logs/rate_limit.json';
        $rateLimitDir = dirname($rateLimitFile);
        
        // Créer le répertoire de logs s'il n'existe pas
        if (!is_dir($rateLimitDir)) {
            mkdir($rateLimitDir, 0755, true);
        }
        
        // Charger les données existantes
        $rateLimitData = [];
        if (file_exists($rateLimitFile)) {
            $content = file_get_contents($rateLimitFile);
            if ($content !== false) {
                $rateLimitData = json_decode($content, true) ?: [];
            }
        }
        
        $currentTime = time();
        $keyData = $rateLimitData[$key] ?? ['requests' => [], 'blocked_until' => 0];
        
        // Vérifier si la clé est bloquée
        if ($keyData['blocked_until'] > $currentTime) {
            return false;
        }
        
        // Nettoyer les anciennes requêtes
        $keyData['requests'] = array_filter($keyData['requests'], function($timestamp) use ($currentTime, $timeWindow) {
            return ($currentTime - $timestamp) < $timeWindow;
        });
        
        // Vérifier si la limite est atteinte
        if (count($keyData['requests']) >= $maxRequests) {
            // Bloquer pour la durée de la fenêtre de temps
            $keyData['blocked_until'] = $currentTime + $timeWindow;
            $rateLimitData[$key] = $keyData;
            
            // Sauvegarder les données
            file_put_contents($rateLimitFile, json_encode($rateLimitData), LOCK_EX);
            
            return false;
        }
        
        // Ajouter la requête actuelle
        $keyData['requests'][] = $currentTime;
        $keyData['blocked_until'] = 0;
        $rateLimitData[$key] = $keyData;
        
        // Sauvegarder les données
        file_put_contents($rateLimitFile, json_encode($rateLimitData), LOCK_EX);
        
        return true;
    }
    
    /**
     * Enregistre un événement de sécurité dans les logs
     * @param string $event Type d'événement
     * @param array $data Données associées à l'événement
     */
    public function logSecurityEvent($event, $data = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // Ajouter les données supplémentaires
        if (!empty($data)) {
            $logEntry['data'] = $data;
        }
        
        $logFile = __DIR__ . '/logs/security.log';
        
        // Créer le dossier logs s'il n'existe pas
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Écrire dans le log
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    }
}
?>