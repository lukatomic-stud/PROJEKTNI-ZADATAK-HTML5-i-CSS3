<?php
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php?menu=8");
    exit;
}
?>

<section class="content">
    <h1>Administracija</h1>
    <h2>Uspješno prijavljen</h2>

    <p>Pozdrav, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>!</p>
</section>
