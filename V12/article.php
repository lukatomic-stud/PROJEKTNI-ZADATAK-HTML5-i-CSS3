<?php
    $article = 1;
    if (isset($_GET['article'])) {
        $article = (int)$_GET['article'];
    }

    $allowed = [1, 2, 3, 4, 5, 6, 7];
    if (!in_array($article, $allowed, true)) {
        $article = 1;
    }

    include "articles/article{$article}.php";
?>