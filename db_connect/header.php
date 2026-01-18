<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoSchool Ride</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php if(isset($_SESSION['user_id'])): ?>
<header>
    <a href = "index.php"><div class="logo"><strong>EcoSchool Ride</strong></div></a>   
    <div class="user-menu">
        <span>Bonjour, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Parent') ?></span>
        
        <?php if(isset($_SESSION['est_admin']) && $_SESSION['est_admin'] == 1): ?>
            <a href="admin.php" class="btn btn-admin" style="font-size:0.8em; margin-left:10px;">Admin</a>
        <?php endif; ?>
        
        <?php if(basename($_SERVER['PHP_SELF']) == 'admin.php'): ?>
             <a href="dashboard.php" style="margin-left:10px;">Voir Dashboard</a>
        <?php endif; ?>

        <a href="index.php?logout=true" style="color:#f44336; margin-left:15px;">Déconnexion</a>
    </div>
</header>
<?php endif; ?>

<div class="main-container">

<?php if(isset($_SESSION['message']) && !empty($_SESSION['message'])): ?>
    <?php 
        $type = $_SESSION['msg_type'] ?? 'error';
        $color = ($type == 'success') ? '#d4edda' : '#f8d7da'; 
        $text = ($type == 'success') ? '#155724' : '#721c24';
    ?>
    <div id="flash-message" style="background-color: <?= $color ?>; color: <?= $text ?>; border: 1px solid <?= $text ?>;">
        <?= $_SESSION['message'] ?>
    </div>
    <?php 
        unset($_SESSION['message']); 
        unset($_SESSION['msg_type']);
    ?>
<?php endif; ?>