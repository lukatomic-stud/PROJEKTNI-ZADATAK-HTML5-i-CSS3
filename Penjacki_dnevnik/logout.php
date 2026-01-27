<?php
    session_unset();
    session_destroy();

    header("Location: index.php?menu=1");
    exit;
?>