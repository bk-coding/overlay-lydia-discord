# Documentation de Sécurité - Système de Cagnotte Overlay

## Vue d'ensemble

Ce document décrit toutes les mesures de sécurité implémentées dans le système de cagnotte overlay pour protéger contre les vulnérabilités courantes et les attaques malveillantes.

## Mesures de Sécurité Implémentées

### 1. Protection au niveau du serveur web (.htaccess)

#### Sécurité PHP
- Désactivation de l'affichage des erreurs PHP en production
- Limitation de la taille des uploads (2MB)
- Désactivation des fonctions PHP dangereuses (exec, shell_exec, system, etc.)
- Protection contre l'exécution de code PHP dans les uploads

#### Protection des fichiers sensibles
- Blocage de l'accès aux fichiers de configuration (`config.php`, `security.php`)
- Protection des fichiers de migration (`migrate_password.php`)
- Blocage de l'accès aux fichiers de données (`data.json`, `*.log`)
- Protection des fichiers d'environnement (`.env`)
- Blocage des fichiers de sauvegarde (`*.bak`, `*.backup`, `*.old`)

#### En-têtes de sécurité HTTP
- `X-Content-Type-Options: nosniff` - Prévient le MIME sniffing
- `X-Frame-Options: SAMEORIGIN` - Protection contre le clickjacking
- `X-XSS-Protection: 1; mode=block` - Protection XSS basique
- `Referrer-Policy: strict-origin-when-cross-origin` - Contrôle des referrers
- `Content-Security-Policy` - Politique de sécurité du contenu

#### Protection contre les attaques
- Règles anti-bots malveillants
- Protection contre les injections SQL
- Blocage des tentatives d'inclusion de fichiers
- Protection contre les attaques de timing avec mod_evasive
- Limitation de taux de requêtes

#### Performance et cache
- Compression gzip activée
- Cache des ressources statiques
- Optimisation des en-têtes de cache

### 2. Gestion des permissions de fichiers (secure_permissions.sh)

#### Permissions sécurisées
- Fichiers PHP : 644 (lecture/écriture propriétaire, lecture groupe/autres)
- Fichiers sensibles (config.php) : 600 (lecture/écriture propriétaire uniquement)
- Fichiers de données (data.json) : 666 (lecture/écriture pour tous)
- Répertoire logs : 755 (accès complet propriétaire, lecture/exécution autres)
- Fichiers logs : 666 (lecture/écriture pour tous)

#### Propriétaire des fichiers
- Configuration automatique du propriétaire web (`www-data` ou `_www` sur macOS)
- Vérification des droits sudo avant modification

### 3. Classe SecurityManager (security.php)

#### Authentification sécurisée
- Hachage des mots de passe avec `password_hash()` (bcrypt)
- Vérification sécurisée avec `password_verify()`
- Gestion des sessions avec régénération d'ID
- Limitation de taux pour les tentatives de connexion

#### Protection CSRF
- Génération de tokens CSRF uniques
- Vérification obligatoire pour toutes les actions sensibles
- Tokens liés à la session utilisateur

#### Validation des données
- Validation stricte des URLs (filter_var avec FILTER_VALIDATE_URL)
- Validation des entiers avec limites min/max
- Validation des couleurs hexadécimales
- Validation des noms de fichiers (caractères autorisés uniquement)
- Validation des chaînes avec limitation de longueur
- Validation des énumérations avec valeurs autorisées

#### Journalisation de sécurité
- Enregistrement de tous les événements de sécurité
- Horodatage précis avec microsecondes
- Capture de l'IP et du User-Agent
- Rotation automatique des logs

#### Protection contre les attaques
- Limitation de taux par IP
- Protection contre les attaques de force brute
- Validation stricte de toutes les entrées utilisateur

### 4. Sécurisation des fichiers individuels

#### update.php
- En-têtes de sécurité HTTP
- Limitation des méthodes HTTP (GET, POST uniquement)
- Authentification par token API (optionnelle)
- Limitation de taux de requêtes
- Journalisation des accès non autorisés
- Validation des données d'entrée

#### discord.php
- Protection contre l'accès direct non autorisé
- En-têtes de sécurité HTTP
- Validation des URLs de webhook Discord
- Validation des données avant envoi
- Limitation de taux pour prévenir le spam
- Gestion robuste des erreurs cURL
- Journalisation des événements de sécurité

#### overlay.php
- Vérification de l'origine des requêtes
- En-têtes de sécurité HTTP
- Configuration par défaut en cas d'erreur
- Validation et nettoyage de tous les paramètres
- Validation des couleurs hexadécimales
- Limitation des valeurs numériques
- Échappement HTML de toutes les sorties

#### index.php
- En-têtes de sécurité HTTP renforcés
- Content Security Policy stricte
- Limitation des méthodes HTTP
- Protection contre les attaques de timing
- Validation complète de tous les formulaires
- Tokens CSRF obligatoires
- Journalisation des modifications de configuration

### 5. Validation des données

#### Types de validation supportés
- **URL** : Validation avec filter_var et vérifications supplémentaires
- **Integer** : Validation avec limites min/max configurables
- **Float** : Validation avec limites min/max configurables
- **Color** : Validation du format hexadécimal (#RRGGBB)
- **String** : Validation avec limitation de longueur
- **Filename** : Validation avec caractères autorisés uniquement
- **Enum** : Validation contre une liste de valeurs autorisées

#### Règles de validation
- Toutes les entrées utilisateur sont validées
- Valeurs par défaut sécurisées en cas d'erreur
- Messages d'erreur informatifs sans révéler d'informations sensibles
- Échappement automatique des sorties HTML

### 6. Protection contre les vulnérabilités courantes

#### Injection SQL
- Aucune base de données utilisée (fichiers JSON)
- Validation stricte de toutes les entrées

#### Cross-Site Scripting (XSS)
- Échappement HTML avec `htmlspecialchars()`
- Content Security Policy restrictive
- Validation des entrées utilisateur

#### Cross-Site Request Forgery (CSRF)
- Tokens CSRF obligatoires pour toutes les actions
- Vérification de l'origine des requêtes

#### Inclusion de fichiers
- Aucune inclusion dynamique de fichiers
- Chemins de fichiers validés et sécurisés

#### Upload de fichiers
- Limitation de la taille des uploads
- Validation des types de fichiers
- Protection contre l'exécution de code uploadé

#### Attaques de force brute
- Limitation de taux par IP
- Délais progressifs après échecs
- Journalisation des tentatives

#### Déni de service (DoS)
- Limitation de taux de requêtes
- Timeouts configurés
- Protection mod_evasive

### 7. Configuration sécurisée

#### Fichiers de configuration
- Permissions restrictives (600)
- Validation de toutes les valeurs
- Valeurs par défaut sécurisées
- Protection contre l'accès web direct

#### Gestion des erreurs
- Pas d'affichage d'erreurs en production
- Journalisation sécurisée des erreurs
- Messages d'erreur génériques pour l'utilisateur

#### Sessions
- Configuration sécurisée des sessions PHP
- Régénération d'ID de session
- Cookies sécurisés (httponly, secure si HTTPS)

## Recommandations d'utilisation

### 1. Installation
1. Exécuter le script `secure_permissions.sh` après déploiement
2. Vérifier que le serveur web supporte les directives .htaccess
3. Configurer HTTPS en production
4. Modifier le mot de passe par défaut

### 2. Maintenance
1. Surveiller les logs de sécurité régulièrement
2. Mettre à jour les mots de passe périodiquement
3. Vérifier les permissions de fichiers
4. Surveiller les tentatives d'accès non autorisées

### 3. Monitoring
1. Vérifier les logs dans le répertoire `logs/`
2. Surveiller les événements de sécurité
3. Contrôler les tentatives de connexion échouées
4. Vérifier l'intégrité des fichiers de configuration

### 4. En cas d'incident
1. Vérifier les logs de sécurité
2. Changer tous les mots de passe
3. Vérifier l'intégrité des fichiers
4. Analyser les tentatives d'accès suspectes

## Limitations et considérations

### Limitations actuelles
- Pas de chiffrement des données au repos
- Authentification à facteur unique
- Pas de sauvegarde automatique des configurations

### Améliorations futures possibles
- Authentification à deux facteurs
- Chiffrement des fichiers de configuration
- Audit trail complet
- Intégration avec des systèmes de monitoring

## Contact et support

Pour toute question de sécurité ou signalement de vulnérabilité, veuillez consulter la documentation technique ou contacter l'administrateur système.

---

*Document généré automatiquement - Dernière mise à jour : $(date)*