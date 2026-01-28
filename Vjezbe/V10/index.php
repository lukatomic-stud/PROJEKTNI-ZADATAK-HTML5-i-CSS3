<?php
$menu = 1;
if (isset($_GET['menu'])) {
    $menu = (int)$_GET['menu'];
}

$pageTitle = "Penjački dnevnik";
$pageDesc = "Penjački dnevnik za praćenje uspona i napretka.";
$pageKeywords = "penjanje, penjački dnevnik, bouldering, sportsko penjanje";

$pageFile = "home.php";

switch ($menu) {
    case 1:
        $pageTitle = "Početna | Penjački dnevnik";
        $pageDesc = "Penjački dnevnik za praćenje uspona i napretka.";
        $pageFile = "home.php";
        break;
    case 2:
        $pageTitle = "Novosti | Penjački dnevnik";
        $pageDesc = "Novosti i kratki članci o penjanju: trening, oprema, smjerovi i penjački događaji.";
        $pageFile = "news.php";
        break;
    case 3:
        $pageTitle = "Galerija | Penjački dnevnik";
        $pageDesc = "Galerija slika penjačkih trenutaka i uspona.";
        $pageFile = "gallery.php";
        break;
    case 4:
        $pageTitle = "O nama | Penjački dnevnik";
        $pageDesc = "O nama – Penjački dnevnik za praćenje uspona, treninga i napretka.";
        $pageFile = "about.php";
        break;
    case 5:
        $pageTitle = "Kontakt | Penjački dnevnik";
        $pageDesc = "Kontakt stranica penjačkog dnevnika.";
        $pageFile = "contact.php";
        break;
    case 6:
        $pageTitle = "Članak | Penjački dnevnik";
        $pageDesc = "Članak o boulderingu.";
        $pageFile = "article.php";
        break;
    default:
        $pageTitle = "Početna | Penjački dnevnik";
        $pageDesc = "Penjački dnevnik za praćenje uspona i napretka.";
        $pageFile = "home.php";
        $menu = 1;
        break;
}

include "includes/header.php";
include "includes/nav.php";
?>
<main>
    <?php include $pageFile; ?>
</main>
<?php include "includes/footer.php"; ?>
