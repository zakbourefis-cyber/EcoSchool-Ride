<?php
// dashboard.php
require_once 'db_connect/db.php';
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$id_parent = $_SESSION['user_id'];

// 1. AJOUT ENFANT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_child') {
    $prenom = htmlspecialchars($_POST['prenom_enfant']);
    $date_naiss = $_POST['date_naissance'];
    
    $stmt = $pdo->prepare("INSERT INTO enfants (prenom, date_naissance, id_parent) VALUES (?, ?, ?)");
    $stmt->execute([$prenom, $date_naiss, $id_parent]);
    
    $_SESSION['message'] = "Enfant ajouté !";
    $_SESSION['msg_type'] = "success";
    header("Location: parent.php"); exit();
}

// 2. INSCRIPTION TRAJET
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'inscrire_trajet') {
    $id_enfant = $_POST['id_enfant'];
    $id_trajet = $_POST['id_trajet'];

    // Vérif places
    $stmt = $pdo->prepare("SELECT places_proposees FROM trajets WHERE id_trajet = ?");
    $stmt->execute([$id_trajet]);
    $trajet = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM inscriptions WHERE id_trajet = ? AND statut = 'VALIDE'");
    $stmt->execute([$id_trajet]);
    $inscrits = $stmt->fetch()['total'];

    if ($inscrits < $trajet['places_proposees']) {
        $statut = 'VALIDE';
        $msg = "Inscription confirmée !";
        $type = "success";
    } else {
        $statut = 'EN_ATTENTE';
        $msg = "Trajet complet. Enfant placé en liste d'attente.";
        $type = "error"; // ou warning
    }

    $stmt = $pdo->prepare("INSERT INTO inscriptions (id_enfant, id_trajet, statut) VALUES (?, ?, ?)");
    $stmt->execute([$id_enfant, $id_trajet, $statut]);
    
    $_SESSION['message'] = $msg;
    $_SESSION['msg_type'] = $type;
    header("Location: parent.php"); exit();
}

// Récupération des données
$mes_enfants = $pdo->prepare("SELECT * FROM enfants WHERE id_parent = ?");
$mes_enfants->execute([$id_parent]);
$mes_enfants = $mes_enfants->fetchAll();

$sql_trajets = "SELECT t.*, c.nom, c.prenom FROM trajets t JOIN conducteurs c ON t.id_conducteur = c.id_conducteur";
$liste_trajets = $pdo->query($sql_trajets)->fetchAll();

$sql_inscriptions = "SELECT i.*, e.prenom, t.point_depart, t.destination, t.horaire FROM inscriptions i JOIN enfants e ON i.id_enfant = e.id_enfant JOIN trajets t ON i.id_trajet = t.id_trajet WHERE e.id_parent = ?";
$stmt = $pdo->prepare($sql_inscriptions);
$stmt->execute([$id_parent]);
$mes_inscriptions = $stmt->fetchAll();

require_once 'db_connect/header.php'; 
?>

<div class="card">
    <h2>1. Mes Enfants</h2>
    <ul>
        <?php foreach($mes_enfants as $e): ?>
            <li><?= htmlspecialchars($e['prenom']) ?></li>
        <?php endforeach; ?>
    </ul>
    <h3>Ajouter un enfant</h3>
    <form method="POST" class="row">
        <input type="hidden" name="action" value="add_child">
        <input type="text" name="prenom_enfant" placeholder="Prénom" required class="col">
        <input type="date" name="date_naissance" required class="col">
        <button type="submit">Ajouter</button>
    </form>
</div>

<div class="card">
    <h2>2. Inscrire un enfant</h2>
    <?php if(count($mes_enfants) > 0): ?>
        <form method="POST">
            <input type="hidden" name="action" value="inscrire_trajet">
            <div class="form-group">
                <label>Enfant :</label>
                <select name="id_enfant">
                    <?php foreach($mes_enfants as $e): echo "<option value='{$e['id_enfant']}'>{$e['prenom']}</option>"; endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Trajet :</label>
                <select name="id_trajet">
                    <?php foreach($liste_trajets as $t): ?>
                        <option value="<?= $t['id_trajet'] ?>">
                            <?= $t['point_depart'] ?> -> <?= $t['destination'] ?> (<?= $t['horaire'] ?>) - Chauffeur: <?= $t['prenom'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Inscrire</button>
        </form>
    <?php else: ?>
        <p>Ajoutez d'abord un enfant ci-dessus.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h2>3. Suivi des demandes</h2>
    <table>
        <tr><th>Enfant</th><th>Trajet</th><th>Statut</th></tr>
        <?php foreach($mes_inscriptions as $i): ?>
        <tr>
            <td><?= htmlspecialchars($i['prenom']) ?></td>
            <td><?= $i['point_depart'] ?> (<?= $i['horaire'] ?>)</td>
            <td>
                <span class="<?= $i['statut'] == 'VALIDE' ? 'badge-valide' : 'badge-attente' ?>">
                    <?= $i['statut'] ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require_once 'db_connect/footer.php'; ?>