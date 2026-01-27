<?php
    $role = $_SESSION['role'] ?? 'user';
?>


<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>

    <meta name="description" content="<?php echo $pageDesc; ?>">
    <meta name="keywords" content="<?php echo $pageKeywords; ?>">
    <meta name="author" content="Luka Tomić">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="banner">

        <div class="banner-user">
            <?php if (!isset($_SESSION['user_id'])): ?>

                <a href="index.php?menu=7" class="user-link" title="Registracija">
                    <img src="images/icon-register.svg" alt="">
                    Registracija
                </a>

                <a href="index.php?menu=8" class="user-link" title="Prijava">
                    <img src="images/icon-login.svg" alt="">
                    Prijava
                </a>

            <?php else: ?>

                <?php if (
                    !empty($_SESSION['is_approved']) &&
                    (int)$_SESSION['is_approved'] === 1 &&
                    in_array($role, ['administrator', 'editor'], true)
                ): ?>
                    <a href="index.php?menu=20" class="user-link" title="CMS">
                        <img src="images/icon-cms.svg" alt="">
                        CMS
                    </a>
                <?php endif; ?>

                <span class="user-name" title="Prijavljeni korisnik">
                    <img src="images/icon-user.svg" alt="">
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>

                <a href="index.php?menu=10" class="user-link" title="Odjava">
                    <img src="images/icon-logout.svg" alt="">
                    Odjava
                </a>

            <?php endif; ?>
        </div>

        <h1>Penjački dnevnik</h1>
        <p>Bilježi svoje uspone i napredak</p>

    </div>
</header>
