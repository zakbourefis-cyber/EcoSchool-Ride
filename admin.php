<?php
// admin.php
session_start();
require_once 'db_connect/db.php';

$message = "";

// --- TRAITEMENT DES FORMULAIRES ---

// 1. Ajouter un Conducteur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_conducteur') {
    try {
        $stmt = $pdo->prepare("INSERT INTO conducteurs (nom, prenom, telephone, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['nom'], 
            $_POST['prenom'], 
            $_POST['telephone'], 
            $_POST['email']
        ]);
        $message = "Conducteur ajouté avec succès !";
    } catch (PDOException $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}

// 2. Ajouter un Véhicule
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_vehicule') {
    try {
        $stmt = $pdo->prepare("INSERT INTO vehicules (modele, immatriculation, capacite_totale, id_conducteur) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['modele'], 
            $_POST['immatriculation'], 
            $_POST['capacite'], 
            $_POST['id_conducteur']
        ]);
        $message = "Véhicule ajouté avec succès !";
    } catch (PDOException $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}

// 3. Ajouter un Trajet
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_trajet') {
    try {
        $stmt = $pdo->prepare("INSERT INTO trajets (point_depart, destination, horaire, places_proposees, id_conducteur) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['depart'], 
            $_POST['destination'], 
            $_POST['horaire'], 
            $_POST['places'], 
            $_POST['id_conducteur']
        ]);
        $message = "Trajet créé avec succès !";
    } catch (PDOException $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}

// --- RÉCUPÉRATION DES DONNÉES (Pour les listes déroulantes) ---
$liste_conducteurs = $pdo->query("SELECT * FROM conducteurs ORDER BY nom")->fetchAll();

// On récupère aussi les véhicules pour info
$liste_vehicules = $pdo->query("
    SELECT v.*, c.nom, c.prenom 
    FROM vehicules v 
    JOIN conducteurs c ON v.id_conducteur = c.id_conducteur
")->fetchAll();

// On récupère les trajets pour info
$liste_trajets = $pdo->query("
    SELECT t.*, c.nom, c.prenom 
    FROM trajets t 
    JOIN conducteurs c ON t.id_conducteur = c.id_conducteur
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - EcoSchool Ride</title>
    <style>
        body { font-family: sans-serif; max-width: 900px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
        h1 { text-align: center; color: #333; }
        .container { display: flex; flex-wrap: wrap; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex: 1; min-width: 300px; }
        .full-width { width: 100%; }
        h2 { border-bottom: 2px solid #2196F3; padding-bottom: 10px; margin-top: 0; font-size: 1.2em; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background-color: #2196F3; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin-top: 15px; width: 100%; }
        button:hover { background-color: #0b7dda; }
        .message { background-color: #dff0d8; color: #3c763d; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.9em; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>

    <h1>Gestion EcoSchool Ride</h1>
    <p style="text-align:center;"><a href="index.php">Retour à l'accueil</a></p>

    <?php if(!empty($message)): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <div class="container">
        
        <div class="card">
            <h2>1. Ajouter un Conducteur</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_conducteur">
                
                <label>Nom :</label>
                <input type="text" name="nom" required>
                
                <label>Prénom :</label>
                <input type="text" name="prenom" required>
                
                <label>Téléphone :</label>
                <input type="text" name="telephone" required>
                
                <label>Email :</label>
                <input type="email" name="email">
                
                <button type="submit">Enregistrer Conducteur</button>
            </form>
        </div>

        <div class="card">
            <h2>2. Ajouter un Véhicule</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_vehicule">
                
                <label>Pour quel conducteur ?</label>
                <select name="id_conducteur" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach($liste_conducteurs as $c): ?>
                        <option value="<?= $c['id_conducteur'] ?>"><?= $c['nom'] ?> <?= $c['prenom'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Modèle (ex: Renault Espace) :</label>
                <input type="text" name="modele" required>
                
                <label>Immatriculation :</label>
                <input type="text" name="immatriculation" required>
                
                <label>Capacité Totale (Nb places) :</label>
                <input type="number" name="capacite" required min="1">
                
                <button type="submit" style="background-color: #FF9800;">Enregistrer Véhicule</button>
            </form>
        </div>

        <div class="card">
            <h2>3. Créer un Trajet</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_trajet">
                
                <label>Conducteur :</label>
                <select name="id_conducteur" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach($liste_conducteurs as $c): ?>
                        <option value="<?= $c['id_conducteur'] ?>"><?= $c['nom'] ?> <?= $c['prenom'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Lieu de départ :</label>
                <input type="text" name="depart" required placeholder="Ex: Place de la Mairie">
                
                <label>Destination :</label>
                <input type="text" name="destination" value="École" required>
                
                <label>Horaire de départ :</label>
                <input type="time" name="horaire" required>
                
                <label>Places proposées pour ce trajet :</label>
                <input type="number" name="places" required min="1" placeholder="Ex: 3">
                <small style="color:gray;">Doit être <= à la capacité du véhicule.</small>
                
                <button type="submit" style="background-color: #4CAF50;">Publier le Trajet</button>
            </form>
        </div>

    </div>

    <div class="card full-width" style="margin-top: 20px;">
        <h2>Récapitulatif des Trajets Actifs</h2>
        <table>
            <tr>
                <th>Départ -> Arrivée</th>
                <th>Horaire</th>
                <th>Conducteur</th>
                <th>Places dispo</th>
            </tr>
            <?php foreach($liste_trajets as $t): ?>
            <tr>
                <td><?= $t['point_depart'] ?> -> <?= $t['destination'] ?></td>
                <td><?= $t['horaire'] ?></td>
                <td><?= $t['nom'] ?> <?= $t['prenom'] ?></td>
                <td><strong><?= $t['places_proposees'] ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</body>
</html>