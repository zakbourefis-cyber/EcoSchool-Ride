<?php
// index.php
require_once 'db_connect/db.php';

//si déja connecté on revient dans parent
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: parent.php");
    exit();
}


// Déconnexion rapide
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Traitement Connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM parents WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id_parent'];
        $_SESSION['est_admin'] = $user['est_admin']; 
        $_SESSION['user_name'] = $user['prenom'] . " " . $user['nom'];
        
        header("Location: parent.php"); // On redirige vers dashboard.php
        exit();
    } else {
        $_SESSION['message'] = "Email ou mot de passe incorrect.";
        $_SESSION['msg_type'] = "error";
    }
}

// On inclut le header (qui affichera les erreurs s'il y en a)
require_once 'db_connect/header.php'; 
?>

<div class="card" style="max-width: 400px; margin: 0 auto;">
    <h1 class="text-center">Connexion</h1>
    <p class="text-center">Bienvenue sur EcoSchool Ride</p>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email :</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Mot de passe :</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn" style="width:100%">Se connecter</button>
    </form>
    
    <p class="text-center" style="margin-top:15px;">
        Pas de compte ? <a href="inscription.php">S'inscrire ici</a>
    </p>
</div>

<?php require_once 'db_connect/footer.php'; ?>