<?php
// inscription.php
require_once 'db_connect/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des nouveaux champs
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    
    $email = $_POST['email'];
    $tel = $_POST['telephone'];
    $pass = $_POST['password'];

    $pass_hash = password_hash($pass, PASSWORD_DEFAULT);

    try {
        // SQL mis à jour : on insère nom ET prenom
        $sql = "INSERT INTO parents (nom, prenom, email, mot_de_passe, telephone) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom, $prenom, $email, $pass_hash, $tel]);
        
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $message = "Cet email est déjà utilisé.";
        } else {
            $message = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - EcoSchool Ride</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; box-sizing: border-box; } /* box-sizing pour éviter que ça dépasse */
        button { background-color: #008CBA; color: white; padding: 10px 15px; border: none; cursor: pointer; }
        .row { display: flex; gap: 10px; } /* Pour mettre Nom et Prénom côte à côte */
        .col { flex: 1; }
    </style>
</head>
<body>

    <h1>Créer un compte Parent</h1>
    <a href="index.php">Retour à la connexion</a>

    <?php if(!empty($message)): ?>
        <p style="color:red;"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        
        <div class="form-group row">
            <div class="col">
                <label>Nom :</label>
                <input type="text" name="nom" required placeholder="Dupont">
            </div>
            <div class="col">
                <label>Prénom :</label>
                <input type="text" name="prenom" required placeholder="Jean">
            </div>
        </div>

        <div class="form-group">
            <label>Email :</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Téléphone :</label>
            <input type="text" name="telephone" required>
        </div>
        <div class="form-group">
            <label>Mot de passe :</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">S'inscrire</button>
    </form>

</body>
</html>