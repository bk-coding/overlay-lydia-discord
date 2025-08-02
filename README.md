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
├── config.example.php   # 📋 Modèle de configuration (à copier)
├── config.php          # ⚙️ Configuration personnelle (créé par vous)
├── index.php            # 🔧 Interface d'administration web
├── overlay.php          # 🎨 Générateur d'overlay HTML
├── update.php           # 🔄 Script de mise à jour Lydia
├── discord.php          # 💬 Système de notifications Discord
├── data.json            # 📊 Données de la cagnotte (généré automatiquement)
├── caisse.mp3           # 🔊 Son de contribution
├── .gitignore           # 🚫 Fichiers à exclure de Git
└── README.md            # 📖 Documentation du système
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

## 🔧 Maintenance

### ✅ Avantages de la configuration centralisée :
- **Un seul fichier à modifier** : `config.php`
- **Aucune modification de code** nécessaire
- **Sauvegarde facile** de votre configuration
- **Mise à jour simplifiée** du système

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

### L'overlay ne s'affiche pas :
- Vérifiez que le serveur PHP est démarré
- Vérifiez l'URL dans OBS : `http://localhost:8000/overlay-lydia-discord/overlay.php`

### Le son ne fonctionne pas :
- Vérifiez que `caisse.mp3` est présent
- Vérifiez `'actif' => true` dans la section audio de `config.php`

### Discord ne fonctionne pas :
- Vérifiez votre URL de webhook dans `config.php`
- Vérifiez `'actif' => true` dans la section discord

## 📞 Support

Pour toute question ou problème, vérifiez d'abord que votre configuration dans `config.php` est correcte. La plupart des problèmes viennent d'une mauvaise configuration de ce fichier.