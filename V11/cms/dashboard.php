<?php
require_once "includes/guard.php";
require_approved();

$role = $_SESSION["role"] ?? "user";
?>

<section class="content">
    <h1>CMS</h1>
    <h2>Administracija sadržaja</h2>

    <p>
        Pozdrav, 
        <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong> 
        (<?php echo htmlspecialchars($role); ?>).
    </p>

    <ul>
        <li><a href="index.php?menu=22">Unos nove vijesti</a></li>
        <li><a href="index.php?menu=23">Popis vijesti</a></li>

        <?php if ($role === "administrator"): ?>
            <li><a href="index.php?menu=21">Korisnici (role + odobrenje)</a></li>
        <?php endif; ?>
    </ul>
</section>
