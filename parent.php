<?php
// dashboard.php
session_start();
require_once 'db_connect/db.php';

// Vérification de sécurité : si pas connecté, on renvoie à l'accueil
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$id_parent = $_SESSION['user_id'];
$message = "";

// 1. TRAITEMENT DU FORMULAIRE D'AJOUT D'ENFANT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_child') {
    $prenom = htmlspecialchars($_POST['prenom_enfant']);
    $date_naiss = $_POST['date_naissance'];
    
    $stmt = $pdo->prepare("INSERT INTO enfants (prenom, date_naissance, id_parent) VALUES (?, ?, ?)");
    $stmt->execute([$prenom, $date_naiss, $id_parent]);
    $message = "Enfant ajouté avec succès !";
}

// 2. TRAITEMENT DE L'INSCRIPTION À UN TRAJET (Le point critique)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'inscrire_trajet') {
    $id_enfant = $_POST['id_enfant'];
    $id_trajet = $_POST['id_trajet'];

    // A. On récupère les infos du trajet (places totales)
    $stmt = $pdo->prepare("SELECT places_proposees FROM trajets WHERE id_trajet = ?");
    $stmt->execute([$id_trajet]);
    $trajet = $stmt->fetch();
    $limit = $trajet['places_proposees'];

    // B. On compte combien sont DÉJÀ validés sur ce trajet
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM inscriptions WHERE id_trajet = ? AND statut = 'VALIDE'");
    $stmt->execute([$id_trajet]);
    $count = $stmt->fetch();
    $inscrits = $count['total'];

    // C. La logique métier : Y a-t-il de la place ?
    if ($inscrits < $limit) {
        $nouveau_statut = 'VALIDE';
        $msg_specifique = "Inscription validée !";
    } else {
        $nouveau_statut = 'EN_ATTENTE';
        $msg_specifique = "Trajet complet. Enfant placé en liste d'attente.";
    }

    // D. On enregistre
    $stmt = $pdo->prepare("INSERT INTO inscriptions (id_enfant, id_trajet, statut) VALUES (?, ?, ?)");
    $stmt->execute([$id_enfant, $id_trajet, $nouveau_statut]);
    $message = $msg_specifique;
}

// --- RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE ---

// Liste des enfants du parent
$stmt = $pdo->prepare("SELECT * FROM enfants WHERE id_parent = ?");
$stmt->execute([$id_parent]);
$mes_enfants = $stmt->fetchAll();

// Liste des trajets disponibles (avec infos conducteur et véhicule)
$sql_trajets = "
    SELECT t.*, c.nom as nom_chauffeur, c.prenom as prenom_chauffeur, v.modele 
    FROM trajets t
    JOIN conducteurs c ON t.id_conducteur = c.id_conducteur
    JOIN vehicules v ON c.id_conducteur = v.id_conducteur
";
$liste_trajets = $pdo->query($sql_trajets)->fetchAll();

// Liste des inscriptions existantes pour ce parent
$sql_inscriptions = "
    SELECT i.*, e.prenom, t.point_depart, t.destination, t.horaire 
    FROM inscriptions i
    JOIN enfants e ON i.id_enfant = e.id_enfant
    JOIN trajets t ON i.id_trajet = t.id_trajet
    WHERE e.id_parent = ?
";
$stmt = $pdo->prepare($sql_inscriptions);
$stmt->execute([$id_parent]);
$mes_inscriptions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - EcoSchool Ride</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .card { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .badge-valide { background-color: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; }
        .badge-attente { background-color: #fff3cd; color: #856404; padding: 3px 8px; border-radius: 4px; }
        h2 { border-bottom: 2px solid #4CAF50; padding-bottom: 5px; }
    </style>
</head>
<body>

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>Bonjour, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
        
        <div style="display:flex; gap:10px;">
            <?php if(isset($_SESSION['est_admin']) && $_SESSION['est_admin'] == 1): ?>
                <a href="admin.php" style="background-color:#d9534f; color:white; padding:10px; text-decoration:none; border-radius:5px;">
                    ⚙️ Accès Admin
                </a>
            <?php endif; ?>
            
            <a href="logout.php" style="color:red; align-self:center;">Déconnexion</a>
        </div>

        <a href="logout.php" style="color:red;">Déconnexion</a>
    </div>

    <?php if(!empty($message)): ?>
        <p style="background:#eee; padding:10px; border-left:5px solid #4CAF50;"><?= $message ?></p>
    <?php endif; ?>

    <div class="card">
        <h2>1. Mes Enfants</h2>
        <ul>
            <?php foreach($mes_enfants as $enfant): ?>
                <li><?= htmlspecialchars($enfant['prenom']) ?> (Né le <?= $enfant['date_naissance'] ?>)</li>
            <?php endforeach; ?>
        </ul>
        
        <h3>Ajouter un enfant</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_child">
            <input type="text" name="prenom_enfant" placeholder="Prénom" required>
            <input type="date" name="date_naissance" required>
            <button type="submit">Ajouter</button>
        </form>
    </div>

    <div class="card">
        <h2>2. Inscrire un enfant à un trajet</h2>
        <?php if(count($mes_enfants) > 0): ?>
            <form method="POST">
                <input type="hidden" name="action" value="inscrire_trajet">
                
                <label>Quel enfant ?</label>
                <select name="id_enfant">
                    <?php foreach($mes_enfants as $enfant): ?>
                        <option value="<?= $enfant['id_enfant'] ?>"><?= htmlspecialchars($enfant['prenom']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Quel trajet ?</label>
                <select name="id_trajet" style="width:100%; margin:10px 0;">
                    <?php foreach($liste_trajets as $trajet): ?>
                        <option value="<?= $trajet['id_trajet'] ?>">
                            <?= $trajet['point_depart'] ?> -> <?= $trajet['destination'] ?> 
                            (<?= $trajet['horaire'] ?>) avec <?= $trajet['prenom_chauffeur'] ?> 
                            [<?= $trajet['places_proposees'] ?> places max]
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" style="background-color:#4CAF50; color:white; padding:10px; border:none;">Inscrire cet enfant</button>
            </form>
        <?php else: ?>
            <p><em>Veuillez d'abord ajouter un enfant ci-dessus.</em></p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>3. État des inscriptions</h2>
        <table>
            <tr>
                <th>Enfant</th>
                <th>Trajet</th>
                <th>Statut</th>
            </tr>
            <?php foreach($mes_inscriptions as $insc): ?>
            <tr>
                <td><?= htmlspecialchars($insc['prenom']) ?></td>
                <td>De <?= $insc['point_depart'] ?> à <?= $insc['horaire'] ?></td>
                <td>
                    <?php if($insc['statut'] == 'VALIDE'): ?>
                        <span class="badge-valide">CONFIRMÉ</span>
                    <?php else: ?>
                        <span class="badge-attente">EN LISTE D'ATTENTE</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</body>
</html>