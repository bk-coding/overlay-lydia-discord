# Système de Cagnotte Overlay Sécurisé

Un système complet et sécurisé pour afficher une barre de progression de cagnotte Lydia en overlay, avec notifications Discord automatiques.

## 🚀 Installation Rapide

1. **Cloner ou télécharger** les fichiers dans votre répertoire web
2. **Exécuter le script de déploiement** :
   ```bash
   php deploy.php
   ```
3. **Configurer** vos URLs dans le panneau d'administration
4. **Vérifier la sécurité** :
   ```bash
   php security_check.php
   ```
5. **Créer une sauvegarde** (optionnel) :
   ```bash
   php backup.php
   ```

## 📋 Fonctionnalités

### ✨ Interface Utilisateur
- **Panneau d'administration** sécurisé avec authentification
- **Configuration complète** de l'apparence et du comportement
- **Aperçu en temps réel** des modifications
- **Validation automatique** des paramètres

### 🔒 Sécurité Avancée
- **Authentification** avec hachage sécurisé des mots de passe
- **Protection CSRF** sur tous les formulaires
- **Limitation de taux** pour prévenir les attaques
- **Validation stricte** de toutes les entrées
- **Journalisation** des événements de sécurité
- **En-têtes de sécurité** HTTP configurés

### 🎨 Overlay Personnalisable
- **Apparence** entièrement configurable (couleurs, tailles, positions)
- **Effets sonores** avec contrôle du volume
- **Animations** fluides de la barre de progression
- **Messages** personnalisables

### 🔔 Notifications Discord
- **Webhooks Discord** pour les notifications automatiques
- **Embeds riches** avec informations détaillées
- **Notifications** lors des changements de montant
- **Test de connexion** intégré

### 🛠️ Scripts d'Administration
- **Script de déploiement** (`deploy.php`) pour l'installation automatique
- **Vérification de sécurité** (`security_check.php`) avec score détaillé
- **Système de sauvegarde** (`backup.php`) avec restauration
- **Gestionnaire de sécurité** (`security.php`) centralisé

## 📋 Prérequis

- **PHP 7.4+** avec les extensions :
  - `curl` (pour les requêtes Lydia)
  - `json` (pour le traitement des données)
- **Serveur web** (Apache, Nginx, ou serveur PHP intégré)
- **Compte Lydia** avec une cagnotte active
- **Webhook Discord** (optionnel, pour les notifications)

## 🚀 Installation

### 1. Cloner le projet
```bash
git clone https://github.com/bk-coding/overlay-lydia-discord.git
cd overlay-lydia-discord
```

### 2. Configuration
```bash
# Copiez le fichier de configuration modèle
cp config.example.php config.php

# Éditez le fichier avec vos paramètres personnels
nano config.php  # ou votre éditeur préféré
```

### 3. Configuration

#### Option A : Interface d'administration web (Recommandée)
1. Accédez à `http://localhost:8000/index.php`
2. Entrez le code de connexion (par défaut : `admin123`)
3. Modifiez vos paramètres via l'interface graphique
4. Sauvegardez automatiquement

#### Option B : Modification manuelle du fichier config.php
- **URL Lydia** : Remplacez `VOTRE_ID_CAGNOTTE` par votre vrai ID
- **Webhook Discord** : Remplacez `VOTRE_WEBHOOK_ID` et `VOTRE_WEBHOOK_TOKEN`
- **Objectif** : Définissez votre objectif de cagnotte
- **Apparence** : Personnalisez les couleurs et dimensions selon vos goûts
- **Texte personnalisé** : Ajoutez un texte au-dessus de la barre (optionnel)
- **Code d'administration** : Changez le code par défaut pour sécuriser l'accès

### 4. Démarrage du serveur
```bash
# Démarrez un serveur PHP local
php -S localhost:8000

# Ou utilisez votre serveur web préféré (Apache, Nginx, etc.)
```

## 🗂️ Structure des fichiers

```
overlay-lydia-discord/
├── .gitignore           # 🚫 Fichiers à exclure de Git
├── .htaccess            # 🔒 Configuration Apache (sécurité)
├── .user.ini            # 🔧 Configuration PHP (sécurité)
├── README.md            # 📖 Documentation du système
├── SECURITY.md          # 🛡️ Documentation sécurité
├── backup.php           # 💾 Script de sauvegarde automatique
├── backup/              # 📦 Dossier des sauvegardes
├── caisse.mp3           # 🔊 Son de contribution
├── config.example.php   # 📋 Modèle de configuration (à copier)
├── config.php           # ⚙️ Configuration personnelle (créé par vous)
├── data.json            # 📊 Données de la cagnotte (généré automatiquement)
├── deploy.php           # 🚀 Script de déploiement automatique
├── discord.php          # 💬 Système de notifications Discord
├── index.php            # 🔧 Interface d'administration web
├── logs/                # 📝 Dossier des logs de sécurité
├── overlay.php          # 🎨 Générateur d'overlay HTML
├── security.php         # 🛡️ Gestionnaire de sécurité
├── security_check.php   # ✅ Script de vérification sécurité
└── update.php           # 🔄 Script de mise à jour Lydia
```

## ⚙️ Configuration

### 🎯 Pour personnaliser votre overlay, modifiez UNIQUEMENT le fichier `config.php` :

#### 1. Configuration Lydia
```php
'lydia' => [
    'url' => 'https://pots.lydia.me/collect/pots?id=VOTRE-ID',  // Votre URL Lydia
    'objectif' => 500,  // Objectif en euros
],
```

#### 2. Configuration Discord
```php
'discord' => [
    'webhook_url' => 'https://discord.com/api/webhooks/VOTRE-WEBHOOK',
    'actif' => true,  // true/false pour activer/désactiver
],
```

#### 3. Configuration Visuelle
```php
'apparence' => [
    // Couleurs de la barre de progression (dégradé)
    'couleur_debut' => '#ffc400',    // Couleur de début du dégradé
    'couleur_fin' => '#ff6600',      // Couleur de fin du dégradé
    'couleur_bordure' => '#ffffff',  // Couleur de la bordure
    'couleur_fond' => 'rgba(0,0,0,0.7)',     // Couleur de fond (avec transparence)
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
    'texte_personnalise' => 'Objectif Stream',  // Texte à afficher (vide = pas de texte)
    'espacement_texte' => 10,        // Espacement entre le texte et la barre en pixels
    
    // Position de l'overlay
    'position_horizontale' => 'droite',  // 'gauche' ou 'droite'
    'position_verticale' => 'bas',       // 'haut' ou 'bas'
    'marge_horizontale' => 10,           // Marge depuis le bord horizontal
    'marge_verticale' => 10,             // Marge depuis le bord vertical
],
```

#### 4. Configuration Audio
```php
'audio' => [
    'fichier' => 'caisse.mp3',  // Nom du fichier audio
    'volume' => 0.7,            // Volume (0.0 à 1.0)
    'actif' => true,            // true/false pour activer/désactiver
    'formats_supportes' => [    // Formats audio supportés
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg'
    ],
],
```

#### 5. Configuration Technique
```php
'technique' => [
    'intervalle_maj' => 60000,       // Intervalle de mise à jour (ms)
    'timeout_curl' => 30,            // Timeout des requêtes (secondes)
    'duree_transition' => 1,         // Durée des transitions (secondes)
    'fichier_donnees' => 'data.json', // Fichier de données
],
```

#### 6. Messages Personnalisables
```php
'messages' => [
    'chargement' => 'Chargement...',
    'erreur' => 'Erreur de chargement',
    'format_montant' => '%s€ / %s€',
    // Messages Discord
    'discord_titre_contribution' => '🎉 Nouvelle contribution !',
    'discord_titre_mise_a_jour' => '📊 Montant mis à jour',
    'discord_footer' => 'Cagnotte Twitch',
],
```

#### 7. Configuration Administration
```php
'admin' => [
    'code_connexion' => 'admin123',      // Code de connexion (CHANGEZ-LE !)
    'duree_session' => 3600,             // Durée de session (1 heure)
    'nom_session' => 'cagnotte_admin',   // Nom de la session
],
```

> ⚠️ **SÉCURITÉ** : Changez immédiatement le code de connexion par défaut !

## 🚀 Utilisation

### Interface d'administration :
**URL d'administration** : `http://localhost:8000/index.php`
- Connectez-vous avec votre code d'administration
- Modifiez tous les paramètres en temps réel
- Testez vos modifications instantanément

### Pour OBS/Streamlabs :
**URL de l'overlay** : `http://localhost:8000/overlay.php`

### Mise à jour automatique :
- Configurez un cron job pour exécuter `update.php` toutes les minutes
- Ou appelez manuellement : `http://localhost:8000/update.php`

### 🛠️ Scripts d'Administration

#### Script de déploiement :
```bash
php deploy.php
```
- Configure automatiquement les permissions
- Crée les répertoires nécessaires (`logs/`, `backup/`)
- Génère les fichiers de configuration par défaut
- Vérifie la configuration PHP
- Lance une vérification de sécurité

#### Vérification de sécurité :
```bash
php security_check.php
```
- Analyse complète de la sécurité du système
- Score de sécurité global (0-100%)
- Recommandations d'amélioration
- Vérification des permissions de fichiers
- Contrôle de la configuration

#### Système de sauvegarde :
```bash
# Créer une sauvegarde
php backup.php

# Sauvegarde silencieuse avec nettoyage automatique
php backup.php --auto

# Créer une sauvegarde et nettoyer les anciennes
php backup.php --clean

# Lister les sauvegardes existantes
php backup.php --list

# Restaurer une sauvegarde spécifique
php backup.php --restore=sauvegarde_2025-08-02_17-06-54
```

**Fichiers sauvegardés :**
- `config.php` - Configuration principale
- `data.json` - Données de la cagnotte
- `.htaccess` - Configuration Apache
- `.user.ini` - Configuration PHP

## 🔧 Maintenance

### 🛡️ Sécurité

#### Vérification régulière :
```bash
# Vérifier le score de sécurité
php security_check.php

# Créer une sauvegarde avant modifications
php backup.php
```

#### Bonnes pratiques :
- **Changez le mot de passe** d'administration par défaut
- **Vérifiez les logs** régulièrement dans `logs/`
- **Créez des sauvegardes** avant les modifications importantes
- **Surveillez le score de sécurité** (objectif : >90%)

#### Logs de sécurité :
Les événements de sécurité sont enregistrés dans `logs/security.log` :
- Tentatives de connexion
- Modifications de configuration
- Erreurs de validation
- Accès non autorisés

### ✅ Avantages de la configuration centralisée :
- **Un seul fichier à modifier** : `config.php`
- **Aucune modification de code** nécessaire
- **Sauvegarde facile** de votre configuration
- **Mise à jour simplifiée** du système
- **Sécurité renforcée** avec validation automatique

### 📝 Pour modifier votre configuration :

#### Méthode recommandée (Interface web) :
1. Accédez à `http://localhost:8000/index.php`
2. Connectez-vous avec votre code d'administration
3. Modifiez les paramètres via l'interface
4. Sauvegardez (automatique)

#### Méthode manuelle :
1. Ouvrez `config.php`
2. Modifiez les valeurs selon vos besoins
3. Sauvegardez le fichier
4. Rechargez l'overlay dans OBS

## 🎨 Exemples de personnalisation

### Thème sombre :
```php
'couleur_debut' => '#333333',
'couleur_fin' => '#666666',
'couleur_bordure' => '#ffffff',
'couleur_fond' => 'rgba(0,0,0,0.9)',
```

### Thème coloré :
```php
'couleur_debut' => '#ff6b6b',
'couleur_fin' => '#4ecdc4',
'couleur_bordure' => '#45b7d1',
```

### Position coin supérieur gauche :
```php
'position_horizontale' => 'gauche',
'position_verticale' => 'haut',
```

### Exemples de texte personnalisé :
```php
// Texte d'objectif simple
'texte_personnalise' => 'Objectif Stream',

// Texte motivationnel
'texte_personnalise' => '🎯 Aidez-nous à atteindre notre objectif !',

// Texte d'événement
'texte_personnalise' => '🎉 Marathon Caritatif - Merci pour votre soutien',

// Texte avec émojis
'texte_personnalise' => '💰 Cagnotte du jour 💰',

// Désactiver le texte (pas de texte au-dessus)
'texte_personnalise' => '',

// Ajuster l'espacement
'espacement_texte' => 15,  // Plus d'espace entre le texte et la barre
```

## 🆘 Dépannage

### Diagnostic automatique :
```bash
# Vérification complète du système
php security_check.php

# Redéploiement en cas de problème
php deploy.php
```

### Problèmes courants :

1. **L'overlay ne s'affiche pas** :
   - Exécutez `php security_check.php` pour diagnostiquer
   - Vérifiez que `config.php` existe et est configuré
   - Contrôlez les permissions des fichiers
   - Consultez `logs/security.log` pour les erreurs

2. **Les notifications Discord ne fonctionnent pas** :
   - Testez votre webhook dans l'interface d'administration
   - Vérifiez que l'URL du webhook est correcte
   - Contrôlez les permissions du bot Discord
   - Consultez les logs dans `logs/`

3. **Erreur de permissions** :
   - Exécutez `php deploy.php` pour reconfigurer automatiquement
   - Vérifiez les permissions avec `php security_check.php`
   - Assurez-vous que le serveur web peut écrire dans le répertoire

4. **L'interface d'administration est inaccessible** :
   - Vérifiez que `config.php` existe
   - Contrôlez le code d'administration dans la configuration
   - Restaurez une sauvegarde si nécessaire : `php backup.php --list`

5. **Perte de données** :
   - Listez les sauvegardes : `php backup.php --list`
   - Restaurez la dernière sauvegarde : `php backup.php --restore=nom_sauvegarde`

### Récupération d'urgence :
```bash
# Restaurer la configuration par défaut
php deploy.php

# Créer une sauvegarde avant intervention
php backup.php

# Vérifier l'état du système
php security_check.php
```

## 📞 Support

Pour toute question ou problème, vérifiez d'abord que votre configuration dans `config.php` est correcte. La plupart des problèmes viennent d'une mauvaise configuration de ce fichier.