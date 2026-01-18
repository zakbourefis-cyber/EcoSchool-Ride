<?php
// index.php
session_start();
require_once 'db_connect/db.php'; // On inclut la connexion

$message = "";

// Traitement du formulaire de connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. On cherche l'utilisateur dans la base
    $stmt = $pdo->prepare("SELECT * FROM parents WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // 2. On vérifie le mot de passe
if ($user && password_verify($password, $user['mot_de_passe'])) {
        // Connexion réussie
        $_SESSION['user_id'] = $user['id_parent'];
        // Modif ici : on stocke aussi le statut admin
        $_SESSION['est_admin'] = $user['est_admin']; 
        $_SESSION['user_name'] = $user['prenom'] . " " . $user['nom'];
        
        header("Location: parent.php");
        exit();
    }
        
        // Redirection vers le tableau de bord (on créera cette page après)
        header("Location: parent.php");
        exit();
    } else {
        $message = "Email ou mot de passe incorrect.";
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoSchool Ride - Connexion</title>
</head>
<body>
    <link rel="stylesheet" href="style.css">
    <h1>Bienvenue sur EcoSchool Ride</h1>
    <p>La solution de covoiturage pour l'école de vos enfants.</p>

    <?php if(!empty($message)): ?>
        <p class="error"><?= $message ?></p>
    <?php endif; ?>

    <div style="border: 1px solid #ccc; padding: 20px; border-radius: 8px;">
        <h2>Connexion Parent</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Mot de passe :</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>
        
        <p>Pas encore de compte ? <a href="inscription.php">Créer un compte parent</a></p>
    </div>
<script src="script.js"></script>
</body>
<?php if(isset($_SESSION['message']) && !empty($_SESSION['message'])): ?>
    
    <?php $type = $_SESSION['msg_type'] ?? 'error'; ?>
    <?php $color = ($type == 'success') ? '#d4edda' : '#f8d7da'; ?>
    <?php $text_color = ($type == 'success') ? '#155724' : '#721c24'; ?>

    <div id="flash-message" style="
        background-color: <?= $color ?>; 
        color: <?= $text_color ?>; 
        padding: 15px; 
        margin: 20px 0; 
        border-radius: 5px; 
        border: 1px solid <?= $text_color ?>;">
        
        <?= $_SESSION['message'] ?>
    </div>

    <?php 
        unset($_SESSION['message']); 
        unset($_SESSION['msg_type']);
    ?>

<?php endif; ?>
</html>