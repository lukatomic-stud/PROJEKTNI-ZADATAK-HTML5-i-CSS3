<?php
    session_start();
    require_once "includes/db.php";
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
            $pageFile = "news_article.php";
            break;
        case 7:
            $pageTitle = "Registracija | Penjački dnevnik";
            $pageFile = "register.php";
            break;
        case 8:
            $pageTitle = "Prijava | Penjački dnevnik";
            $pageFile = "login.php";
            break;
        case 9:
            $pageTitle = "Administracija | Penjački dnevnik";
            $pageFile = "admin.php";
            break;
        case 10:
            $pageTitle = "Odjava | Penjački dnevnik";
            $pageFile = "logout.php";
            break;
        case 11:
            $pageTitle = "Usponi | Penjački dnevnik";
            $pageDesc  = "Pregled javnih uspona drugih penjača.";
            $pageFile  = "ascents.php";
            break;
        case 12:
            $pageTitle = "Moj dnevnik | Penjački dnevnik";
            $pageDesc  = "Moji usponi i napredak.";
            $pageFile  = "my_ascents.php";
            break;
        case 13:
            $pageTitle = "Dodaj uspon | Penjački dnevnik";
            $pageFile  = "ascent_new.php";
            break;
        case 14:
            $pageTitle = "Uredi uspon | Penjački dnevnik";
            $pageFile  = "ascent_edit.php";
            break;
        case 20:
            $pageTitle = "CMS | Penjački dnevnik";
            $pageFile = "cms/dashboard.php";
            break;
        case 21:
            $pageTitle = "Korisnici | CMS";
            $pageFile = "cms/users.php";
            break;
        case 22:
            $pageTitle = "Nova vijest | CMS";
            $pageFile = "cms/news_new.php";
            break;    
        case 23:
            $pageTitle = "Popis vijesti | CMS";
            $pageFile = "cms/news_list.php";
            break;   
        case 24:
            $pageTitle = "Uredi vijest | CMS";
            $pageFile = "cms/news_edit.php";
            break;
        case 30: 
            $pageTitle="Penjališta | CMS"; 
            $pageFile="cms/crags_list.php"; 
            break;
        case 31: 
            $pageTitle="Novo penjalište | CMS"; 
            $pageFile="cms/crags_new.php"; 
            break;
        case 32: 
            $pageTitle="Uredi penjalište | CMS"; 
            $pageFile="cms/crags_edit.php"; 
            break;

        case 33: 
            $pageTitle="Sektori | CMS"; 
            $pageFile="cms/sectors_list.php"; 
            break;
        case 34: 
            $pageTitle="Novi sektor | CMS"; 
            $pageFile="cms/sectors_new.php"; 
            break;
        case 35: 
            $pageTitle="Uredi sektor | CMS"; 
            $pageFile="cms/sectors_edit.php"; 
            break;

        case 36: 
            $pageTitle="Smjerovi | CMS"; 
            $pageFile="cms/routes_list.php"; 
            break;
        case 37: 
            $pageTitle="Novi smjer | CMS"; 
            $pageFile="cms/routes_new.php"; 
            break;
        case 38: 
            $pageTitle="Uredi smjer | CMS"; 
            $pageFile="cms/routes_edit.php"; 
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
