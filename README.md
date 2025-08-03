# Système d'Overlay de Cagnotte Lydia

## 📋 Vue d'ensemble

Ce système d'overlay affiche une barre de progression pour une cagnotte Lydia avec notifications Discord et effets sonores. Toute la configuration est centralisée dans le fichier `config.php` pour faciliter la maintenance.

### ✨ Fonctionnalités :
- **Interface d'administration web** : Modifiez votre configuration via une interface graphique
- **Authentification sécurisée** : Protection par code de connexion et sessions
- **Configuration centralisée** : Tous les paramètres dans un seul fichier
- **Notifications Discord** : Alertes automatiques lors des contributions
- **Notifications Twitch** : Messages automatiques dans le chat Twitch lors des contributions
- **Effets sonores** : Son de caisse enregistreuse lors des dons
- **Personnalisation complète** : Couleurs, position, texte personnalisable
- **Scripts de test** : Outils pour tester les configurations et simuler des contributions

## 📋 Prérequis

- **PHP 7.4+** avec les extensions :
  - `curl` (pour les requêtes Lydia et Twitch)
  - `json` (pour le traitement des données)
- **Serveur web** (Apache, Nginx, ou serveur PHP intégré)
- **Compte Lydia** avec une cagnotte active
- **Webhook Discord** (optionnel, pour les notifications Discord)
- **Application Twitch** (optionnel, pour les notifications Twitch chat)

## 🚀 Installation

### 1. Télécharger le projet
Téléchargez tous les fichiers dans un dossier de votre serveur web.

### 2. Configuration initiale
```bash
# Copiez le fichier de configuration d'exemple
cp config.example.php config.php

# Modifiez le fichier avec vos paramètres personnels
nano config.php  # ou votre éditeur préféré
```

### 3. Configuration

#### Option A : Interface d'administration web (Recommandée)
1. Accédez à `http://votre-serveur/index.php`
2. Entrez le code de connexion (par défaut : `admin123`)
3. Modifiez vos paramètres via l'interface graphique
4. Sauvegardez automatiquement

#### Option B : Modification manuelle du fichier config.php
- **URL Lydia** : Modifiez l'URL de votre cagnotte Lydia
- **Webhook Discord** : Configurez votre webhook Discord
- **Objectif** : Définissez votre objectif de cagnotte
- **Apparence** : Personnalisez les couleurs et dimensions selon vos goûts
- **Texte personnalisé** : Ajoutez un texte au-dessus de la barre (optionnel)
- **Code d'administration** : ⚠️ **IMPORTANT** : Changez absolument le code par défaut `CHANGEZ_MOI` dans `config.php` pour sécuriser l'accès

### 3. Démarrage du serveur
```bash
# Démarrez un serveur PHP local pour les tests
php -S localhost:8000

# Ou utilisez votre serveur web préféré (Apache, Nginx, etc.)
```

### 4. Fichiers audio
Assurez-vous que le fichier `caisse.mp3` est présent dans le dossier pour les effets sonores.

## 🗂️ Structure des fichiers

```
overlay-lydia-discord/
├── .gitignore          # 🚫 Fichiers à ignorer par Git
├── config.example.php  # 📋 Fichier de configuration d'exemple
├── config.php          # ⚙️ Configuration centralisée du système (à créer)
├── index.php           # 🔧 Interface d'administration web
├── overlay.php         # 🎨 Générateur d'overlay HTML
├── update.php          # 🔄 Script de mise à jour Lydia
├── discord.php         # 💬 Système de notifications Discord
├── twitch.php          # 💜 Notifications Twitch Chat
├── test_discord.php    # 🧪 Script de test indépendant pour Discord
├── test_twitch.php     # 🧪 Script de test indépendant pour Twitch
├── test_contribution.php # 🧪 Script de test complet des notifications
├── data.json           # 📊 Données de la cagnotte (généré automatiquement)
├── caisse.mp3          # 🔊 Son de contribution
└── README.md           # 📖 Documentation du système
```

## ⚙️ Configuration

### 🎯 Pour personnaliser votre overlay, modifiez UNIQUEMENT le fichier `config.php` :

#### 1. Configuration Lydia
```php
'lydia' => [
    'url' => 'https://pots.lydia.me/collect/pots?id=VOTRE-ID-CAGNOTTE',  // Votre URL Lydia
    'objectif' => 500,  // Objectif en euros
],
```

#### 2. Configuration Discord
```php
'discord' => [
    'webhook_url' => 'https://discord.com/api/webhooks/VOTRE_WEBHOOK_ID/VOTRE_WEBHOOK_TOKEN',
    'actif' => true,  // true/false pour activer/désactiver
],
```

#### 3. Configuration Twitch (Optionnel)
```php
'twitch' => [
    'actif' => false,                    // true pour activer les messages Twitch
    'client_id' => 'VOTRE_CLIENT_ID',    // Client ID de votre application Twitch
    'access_token' => 'VOTRE_ACCESS_TOKEN',  // Token d'accès OAuth du bot
    'broadcaster_id' => 'VOTRE_BROADCASTER_ID',  // Votre ID utilisateur Twitch
    'bot_user_id' => 'VOTRE_BOT_USER_ID',        // ID utilisateur du bot
    'message_contribution' => '🎉 Merci pour la contribution de {contribution} ! On est maintenant à {total}€ sur {objectif}€ ({pourcentage}%) !',
    'message_test' => '🤖 Test du bot de cagnotte - Tout fonctionne !',
],
```

> 📋 **Configuration Twitch** : Pour configurer Twitch, vous devez créer une application sur [dev.twitch.tv](https://dev.twitch.tv/console/apps) et obtenir un token OAuth avec les permissions `user:write:chat`.

##### 📋 Guide détaillé pour obtenir les informations Twitch

**Étape 1 : Créer une application Twitch**
1. Rendez-vous sur [dev.twitch.tv/console/apps](https://dev.twitch.tv/console/apps)
2. Connectez-vous avec votre compte Twitch
3. Cliquez sur "Register Your Application"
4. Remplissez le formulaire :
   - **Name** : Nom de votre bot (ex: "MonBotCagnotte")
   - **OAuth Redirect URLs** : `http://localhost`
   - **Category** : "Chat Bot"
   - **Client Type** : "Public"
5. Cliquez sur "Create"
6. **Notez le Client ID** qui s'affiche → `client_id`

**Étape 2 : Obtenir un Access Token**
1. Construisez cette URL (remplacez `VOTRE_CLIENT_ID`) :
   ```
   https://id.twitch.tv/oauth2/authorize?client_id=VOTRE_CLIENT_ID&redirect_uri=http://localhost&response_type=token&scope=user:write:chat
   ```
2. Collez l'URL dans votre navigateur et appuyez sur Entrée
3. Autorisez l'application en cliquant sur "Authorize"
4. Vous serez redirigé vers une page d'erreur (c'est normal !)
5. Dans la barre d'adresse, copiez la partie après `access_token=` et avant `&scope` → `access_token`

**Étape 3 : Obtenir votre Broadcaster ID**
- **Option A (API Twitch)** : Exécutez cette commande (remplacez les valeurs) :
  ```bash
  curl -H "Authorization: Bearer VOTRE_ACCESS_TOKEN" \
       -H "Client-Id: VOTRE_CLIENT_ID" \
       "https://api.twitch.tv/helix/users?login=VOTRE_NOM_UTILISATEUR"
  ```
  Dans la réponse JSON, cherchez `"id"` → `broadcaster_id`

- **Option B (Site tiers)** : Allez sur [streamweasels.com/tools/convert-twitch-username-to-user-id](https://streamweasels.com/tools/convert-twitch-username-to-user-id), entrez votre nom d'utilisateur → `broadcaster_id`

**Étape 4 : Obtenir votre Bot User ID**
- Si le bot utilise le **même compte** que votre chaîne : `bot_user_id` = `broadcaster_id`
- Si vous avez un **compte séparé** pour le bot : répétez l'étape 3 avec le nom d'utilisateur du bot

**Récapitulatif des informations obtenues :**
```php
'client_id' => 'abc123...',         // De l'étape 1
'access_token' => 'xyz789...',      // De l'étape 2  
'broadcaster_id' => '123456789',    // De l'étape 3
'bot_user_id' => '123456789',       // De l'étape 4
```

> ⚠️ **Important** : Le token expire généralement après 60 jours. Gardez vos tokens secrets et ne les partagez jamais publiquement !

#### 4. Configuration Visuelle
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

#### 5. Configuration Audio
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

#### 6. Configuration Technique
```php
'technique' => [
    'intervalle_maj' => 60000,       // Intervalle de mise à jour (ms)
    'timeout_curl' => 30,            // Timeout des requêtes (secondes)
    'duree_transition' => 1,         // Durée des transitions (secondes)
    'fichier_donnees' => 'data.json', // Fichier de données
],
```

#### 7. Messages Personnalisables
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

#### 8. Configuration Administration
```php
'admin' => [
    'code_connexion' => 'CHANGEZ_MOI',   // Code de connexion (CHANGEZ-LE ABSOLUMENT !)
    'utilise_hash' => false,             // true = hash sécurisé (recommandé), false = texte brut
    'duree_session' => 3600,             // Durée de session (1 heure)
    'nom_session' => 'cagnotte_admin',   // Nom de la session
],
```

> ⚠️ **SÉCURITÉ** : Changez absolument le code de connexion `CHANGEZ_MOI` pour sécuriser votre interface d'administration !

#### 🔐 Sécurisation avancée du mot de passe (Recommandé)

Pour une sécurité maximale, utilisez un hash sécurisé :

1. **Générer un hash sécurisé** :
   ```bash
   # Modifiez le mot de passe dans generate_password.php
   nano generate_password.php
   
   # Exécutez le script pour générer le hash
   php generate_password.php
   ```

2. **Configurer le hash** :
   - Copiez le hash généré dans `config.php`
   - Changez `'utilise_hash' => true`
   - Supprimez `generate_password.php` après utilisation

3. **Exemple de configuration sécurisée** :
   ```php
   'admin' => [
       'code_connexion' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
       'utilise_hash' => true,
       'duree_session' => 3600,
       'nom_session' => 'cagnotte_admin',
   ],
   ```

## 🚀 Utilisation

### Interface d'administration :
**URL d'administration** : `http://votre-serveur/overlay-lydia-discord/index.php`
- Connectez-vous avec votre code d'administration
- Modifiez tous les paramètres en temps réel
- Testez vos modifications instantanément

#### Liens utiles disponibles dans l'interface :
- **📺 Voir l'overlay** : Aperçu direct de votre overlay (`overlay.php`)
- **🔄 Tester la mise à jour** : Actualisation manuelle des données Lydia (`update.php`)
- **💬 Tester Discord** : Test indépendant des notifications Discord (`test_discord.php`)
- **💜 Tester Twitch** : Test indépendant des messages Twitch (`test_twitch.php`)
- **🧪 Test complet** : Simulation d'une contribution complète avec tous les services (`test_contribution.php`)

### Pour OBS/Streamlabs :
**URL de l'overlay** : `http://votre-serveur/overlay-lydia-discord/overlay.php`

### Mise à jour automatique :
- Configurez un cron job pour exécuter `update.php` toutes les minutes
- Ou appelez manuellement : `http://votre-serveur/overlay-lydia-discord/update.php`

#### Fonctionnement en production :
Le système détecte automatiquement les nouvelles contributions et envoie :
- **Notification Discord** : Embed avec détails de la contribution (si configuré)
- **Message Twitch** : Message automatique dans le chat (si configuré)
- **Mise à jour overlay** : Actualisation en temps réel de la barre de progression
- **Effet sonore** : Son de caisse enregistreuse (si activé)

## 🔧 Maintenance

### ✅ Avantages de la configuration centralisée :
- **Un seul fichier à modifier** : `config.php`
- **Aucune modification de code** nécessaire
- **Sauvegarde facile** de votre configuration
- **Mise à jour simplifiée** du système

### 📝 Pour modifier votre configuration :

#### Méthode recommandée (Interface web) :
1. Accédez à `http://votre-serveur/overlay-lydia-discord/index.php`
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

## 🧪 Tests et Dépannage

### Tests de configuration

Vous pouvez tester chaque composant individuellement ou globalement :

1. **Test Discord indépendant** :
   ```bash
   php test_discord.php
   ```
   Ce script teste uniquement la configuration et l'envoi de notifications Discord.

2. **Test Twitch indépendant** (si configuré) :
   ```bash
   php test_twitch.php
   ```
   Ce script teste uniquement la configuration Twitch et l'envoi de messages dans le chat.

3. **Test complet de contribution** :
   ```bash
   php test_contribution.php
   ```
   Ce script simule une contribution complète et teste tous les services activés (Discord, Twitch, overlay, son).

### Problèmes courants

1. **L'overlay ne s'affiche pas** :
   - Vérifiez l'URL : `http://votre-serveur/overlay-lydia-discord/overlay.php`
   - Vérifiez que `data.json` existe et contient des données valides
   - Consultez la console du navigateur pour les erreurs JavaScript

2. **Fichiers manquants** :
   - Assurez-vous que `config.php` existe (copié depuis `config.example.php`)
   - Vérifiez que `caisse.mp3` est présent dans le dossier

3. **Problème de volume audio** :
   - Ajustez le paramètre `volume` dans la configuration (0.0 à 1.0)
   - Vérifiez que le navigateur autorise la lecture audio automatique

4. **Test du webhook Discord** :
   - Utilisez l'URL directement dans votre navigateur : `http://votre-serveur/overlay-lydia-discord/update.php`
   - Vérifiez la réponse JSON pour les erreurs

5. **Problèmes Twitch** :
   - **Token expiré** : Vérifiez que votre Access Token n'est pas expiré (durée ~60 jours)
   - **Permissions insuffisantes** : Assurez-vous que le bot a les permissions `user:write:chat`
   - **IDs incorrects** : Vérifiez que le Bot User ID et Broadcaster ID correspondent aux bons comptes
   - **Erreur 401** : Le token est invalide ou expiré, régénérez-le
   - **Erreur 403** : Le bot n'a pas les permissions nécessaires
   - **Messages non envoyés** : Le bot doit être connecté au chat (pas besoin d'être modérateur)
   - **Test via interface** : Utilisez le lien "💜 Tester Twitch" dans l'interface d'administration

### Interface d'administration

Si vous ne pouvez pas accéder à l'interface d'administration :
- Vérifiez que le code de connexion dans `config.php` est correct
- Assurez-vous que les sessions PHP fonctionnent sur votre serveur
- Consultez les logs d'erreur de votre serveur web

## 📞 Support

Pour toute question ou problème, vérifiez d'abord que votre configuration dans `config.php` est correcte. La plupart des problèmes viennent d'une mauvaise configuration de ce fichier.

### Outils de diagnostic disponibles :
- **Interface d'administration** : Testez chaque fonctionnalité individuellement via les liens utiles
- **Scripts de test indépendants** : 
  - `test_discord.php` : Test spécifique des notifications Discord
  - `test_twitch.php` : Test spécifique des messages Twitch
  - `test_contribution.php` : Test complet de tous les services
- **Logs d'erreur** : Consultez les logs de votre serveur web
- **Console navigateur** : Vérifiez les erreurs JavaScript dans l'overlay

Le système est conçu pour être robuste et informatif en cas d'erreur. Utilisez les outils de test intégrés pour diagnostiquer rapidement les problèmes de configuration.