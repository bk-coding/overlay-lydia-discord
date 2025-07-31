<?php
/**
 * Générateur de l'overlay HTML avec configuration centralisée
 * Ce fichier génère le HTML de l'overlay en utilisant les paramètres de config.php
 */

// Chargement de la configuration
$config = require_once __DIR__ . '/config.php';

// Extraction des paramètres pour simplifier l'utilisation
$apparence = $config['apparence'];
$audio = $config['audio'];
$technique = $config['technique'];
$messages = $config['messages'];

// Génération du CSS avec les paramètres configurés
$position_h = $apparence['position_horizontale'] === 'gauche' ? 'left' : 'right';
$position_v = $apparence['position_verticale'] === 'haut' ? 'top' : 'bottom';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <style>
    html, body { 
      margin: 0; 
      padding: 0; 
      width: 100%; 
      height: 100%; 
      background: transparent; 
      font-family: sans-serif;
      overflow: hidden;
    }
    body {
      display: flex;
      flex-direction: column;
      justify-content: <?= $position_v === 'top' ? 'flex-start' : 'flex-end' ?>;
      align-items: <?= $position_h === 'left' ? 'flex-start' : 'flex-end' ?>;
      padding: <?= $position_v === 'top' ? $apparence['marge_verticale'] : '0' ?>px <?= $position_h === 'right' ? $apparence['marge_horizontale'] : '0' ?>px <?= $position_v === 'bottom' ? $apparence['marge_verticale'] : '0' ?>px <?= $position_h === 'left' ? $apparence['marge_horizontale'] : '0' ?>px;
      box-sizing: border-box;
    }
    .overlay-wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: <?= !empty($apparence['texte_personnalise']) ? $apparence['espacement_texte'] : '0' ?>px;
    }
    .texte-personnalise {
      color: <?= $apparence['couleur_texte'] ?>;
      font-size: <?= $apparence['taille_police'] ?>px;
      font-weight: <?= $apparence['poids_police'] ?>;
      text-shadow: 
        2px 2px 4px rgba(0,0,0,1),
        -1px -1px 2px rgba(0,0,0,0.8),
        1px -1px 2px rgba(0,0,0,0.8),
        -1px 1px 2px rgba(0,0,0,0.8),
        0 0 8px rgba(0,0,0,0.7);
      text-align: center;
      margin: 0;
      padding: 0;
      white-space: nowrap;
      display: <?= !empty($apparence['texte_personnalise']) ? 'block' : 'none' ?>;
    }
    .container {
      width: <?= $apparence['largeur'] ?>px;
      height: <?= $apparence['hauteur'] ?>px;
      border: <?= $apparence['bordure_epaisseur'] ?>px solid <?= $apparence['couleur_bordure'] ?>;
      border-radius: <?= $apparence['bordure_rayon'] ?>px;
      overflow: hidden;
      background: <?= $apparence['couleur_fond'] ?>;
      position: relative;
      box-sizing: border-box;
    }
    .progress {
      height: 100%;
      background: linear-gradient(90deg, <?= $apparence['couleur_debut'] ?>, <?= $apparence['couleur_fin'] ?>);
      width: 0%;
      transition: width <?= $technique['duree_transition'] ?>s ease-in-out;
    }
    .label {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: <?= $apparence['couleur_texte'] ?>;
      font-size: <?= $apparence['taille_police'] ?>px;
      font-weight: <?= $apparence['poids_police'] ?>;
      text-shadow: 
        2px 2px 4px rgba(0,0,0,1),
        -1px -1px 2px rgba(0,0,0,0.8),
        1px -1px 2px rgba(0,0,0,0.8),
        -1px 1px 2px rgba(0,0,0,0.8),
        0 0 8px rgba(0,0,0,0.7);
      z-index: 100;
      pointer-events: none;
    }
  </style>
</head>
<body>
  <div class="overlay-wrapper">
    <?php if (!empty($apparence['texte_personnalise'])): ?>
    <div class="texte-personnalise"><?= htmlspecialchars($apparence['texte_personnalise']) ?></div>
    <?php endif; ?>
    
    <div class="container">
      <div class="progress" id="progress"></div>
      <div class="label" id="label"><?= htmlspecialchars($messages['chargement']) ?></div>
    </div>
  </div>

  <?php if ($audio['actif']): ?>
  <!-- Élément audio pour le son de caisse -->
  <audio id="caisseSound" preload="auto">
    <?php foreach ($audio['formats_supportes'] as $ext => $type): ?>
    <source src="<?= htmlspecialchars(str_replace('.mp3', '.' . $ext, $audio['fichier'])) ?>" type="<?= $type ?>">
    <?php endforeach; ?>
  </audio>
  <?php endif; ?>

  <script>
    // Configuration JavaScript depuis PHP
    const CONFIG = {
      audio: {
        actif: <?= json_encode($audio['actif']) ?>,
        volume: <?= $audio['volume'] ?>,
        fichier: <?= json_encode($audio['fichier']) ?>
      },
      technique: {
        intervalle_maj: <?= $technique['intervalle_maj'] ?>,
        fichier_donnees: <?= json_encode($technique['fichier_donnees']) ?>
      },
      messages: {
        erreur: <?= json_encode($messages['erreur']) ?>,
        format_montant: <?= json_encode($messages['format_montant']) ?>
      },
      lydia: {
        objectif: <?= $config['lydia']['objectif'] ?>
      }
    };

    let montantPrecedent = null;
    let audioInitialise = false;

    function initialiserAudio() {
      if (!audioInitialise && CONFIG.audio.actif) {
        const audio = document.getElementById('caisseSound');
        if (audio) {
          audio.volume = CONFIG.audio.volume;
          audio.muted = false;
          audio.load();
          audioInitialise = true;
        }
      }
    }

    function jouerSonCaisse() {
      if (!CONFIG.audio.actif) return;
      
      try {
        const audio = document.getElementById('caisseSound');
        if (!audio) return;
        
        audio.currentTime = 0;
        
        // Forcer la lecture même sans interaction utilisateur
        audio.muted = false;
        const playPromise = audio.play();
        
        if (playPromise !== undefined) {
          playPromise.catch(() => {
            // Si échec, essayer en mode muet puis démuet
            audio.muted = true;
            audio.play().then(() => {
              audio.muted = false;
            }).catch(() => {});
          });
        }
      } catch (error) {}
    }

    async function majBarre() {
      try {
        const res = await fetch(CONFIG.technique.fichier_donnees + "?cache=" + Date.now());
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        const data = await res.json();
        const montant = typeof data.montant === 'number' ? data.montant : 0;
        const objectif = typeof data.objectif === 'number' ? data.objectif : CONFIG.lydia.objectif;
        
        if (montantPrecedent !== null && montant > montantPrecedent) {
          initialiserAudio();
          jouerSonCaisse();
        }
        
        montantPrecedent = montant;
        
        const percent = objectif > 0 ? Math.min((montant / objectif) * 100, 100) : 0;
        document.getElementById("progress").style.width = percent + "%";
        
        const montantFormate = montant > 0 ? montant.toLocaleString('fr-FR') : '0';
        const objectifFormate = objectif > 0 ? objectif.toLocaleString('fr-FR') : CONFIG.lydia.objectif.toLocaleString('fr-FR');
        const texte = CONFIG.messages.format_montant.replace('%s', montantFormate).replace('%s', objectifFormate);
        document.getElementById("label").textContent = texte;
          
      } catch (e) {
        document.getElementById("label").textContent = CONFIG.messages.erreur;
      }
    }

    // Initialisation automatique de l'audio au chargement
    window.addEventListener('load', () => {
      initialiserAudio();
    });

    majBarre();
    setInterval(majBarre, CONFIG.technique.intervalle_maj);
  </script>
</body>
</html>