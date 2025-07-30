# Système d'Overlay de Cagnotte - Configuration Centralisée

## 📋 Vue d'ensemble

Ce système d'overlay affiche une barre de progression pour une cagnotte Lydia avec notifications Discord et effets sonores. Toute la configuration est centralisée dans le fichier `config.php` pour faciliter la maintenance.

## 🚀 Installation

### 1. Cloner le projet
```bash
git clone https://github.com/VOTRE-USERNAME/VOTRE-REPO.git
cd barre
```

### 2. Configuration
```bash
# Copiez le fichier de configuration modèle
cp config.example.php config.php

# Éditez le fichier avec vos paramètres personnels
nano config.php  # ou votre éditeur préféré
```

### 3. Paramètres à modifier dans config.php
- **URL Lydia** : Remplacez `VOTRE_ID_CAGNOTTE` par votre vrai ID
- **Webhook Discord** : Remplacez `VOTRE_WEBHOOK_ID` et `VOTRE_WEBHOOK_TOKEN`
- **Objectif** : Définissez votre objectif de cagnotte
- **Apparence** : Personnalisez les couleurs et dimensions selon vos goûts

## 🗂️ Structure des fichiers

```
barre/
├── config.example.php   # 📋 Modèle de configuration (à copier)
├── config.php          # ⚙️ Configuration personnelle (créé par vous)
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
    'couleur_debut' => '#ffc400',    // Couleur de début du dégradé
    'couleur_fin' => '#ff6600',      // Couleur de fin du dégradé
    'couleur_bordure' => '#ffffff',  // Couleur de la bordure
    'largeur' => 400,                // Largeur en pixels
    'hauteur' => 50,                 // Hauteur en pixels
    'position_horizontale' => 'droite',  // 'gauche' ou 'droite'
    'position_verticale' => 'bas',       // 'haut' ou 'bas'
    // ... autres paramètres visuels
],
```

#### 4. Configuration Audio
```php
'audio' => [
    'fichier' => 'caisse.mp3',  // Nom du fichier audio
    'volume' => 0.7,            // Volume (0.0 à 1.0)
    'actif' => true,            // true/false pour activer/désactiver
],
```

## 🚀 Utilisation

### Pour OBS/Streamlabs :
**URL de l'overlay** : `http://localhost:8000/barre/overlay.php`

### Mise à jour automatique :
- Configurez un cron job pour exécuter `update.php` toutes les minutes
- Ou appelez manuellement : `http://localhost:8000/barre/update.php`

## 🔧 Maintenance

### ✅ Avantages de la configuration centralisée :
- **Un seul fichier à modifier** : `config.php`
- **Aucune modification de code** nécessaire
- **Sauvegarde facile** de votre configuration
- **Mise à jour simplifiée** du système

### 📝 Pour modifier votre configuration :
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

## 🆘 Dépannage

### L'overlay ne s'affiche pas :
- Vérifiez que le serveur PHP est démarré
- Vérifiez l'URL dans OBS : `http://localhost:8000/barre/overlay.php`

### Le son ne fonctionne pas :
- Vérifiez que `caisse.mp3` est présent
- Vérifiez `'actif' => true` dans la section audio de `config.php`

### Discord ne fonctionne pas :
- Vérifiez votre URL de webhook dans `config.php`
- Vérifiez `'actif' => true` dans la section discord

## 📞 Support

Pour toute question ou problème, vérifiez d'abord que votre configuration dans `config.php` est correcte. La plupart des problèmes viennent d'une mauvaise configuration de ce fichier.