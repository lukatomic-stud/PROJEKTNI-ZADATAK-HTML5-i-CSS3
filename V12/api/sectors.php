<?php
    require_once "../includes/db.php";
    header("Content-Type: application/json; charset=utf-8");

    $cragId = (int)($_GET["crag_id"] ?? 0);
    if ($cragId <= 0) { echo json_encode([]); exit; }

    $stmt = $conn->prepare("SELECT id, name FROM sectors WHERE crag_id=? ORDER BY name");
    $stmt->bind_param("i", $cragId);
    $stmt->execute();
    $res = $stmt->get_result();

    $out = [];
    while ($res && ($row = $res->fetch_assoc())) $out[] = $row;

    $stmt->close();
    echo json_encode($out);
?>