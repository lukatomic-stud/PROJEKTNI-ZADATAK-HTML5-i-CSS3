<?php
function require_login() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: index.php?menu=8");
        exit;
    }
}

function require_approved() {
    require_login();
}

function require_role(array $allowedRoles) {
    require_login();
    $role = $_SESSION["role"] ?? "user";
    if (!in_array($role, $allowedRoles, true)) {
        header("Location: index.php?menu=20");
        exit;
    }
}
