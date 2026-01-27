<?php
    require_once "../includes/db.php";
    header("Content-Type: application/json; charset=utf-8");

    $sectorId = (int)($_GET["sector_id"] ?? 0);
    if ($sectorId <= 0) { echo json_encode([]); exit; }

    $stmt = $conn->prepare("SELECT id, name, grade FROM routes WHERE sector_id=? ORDER BY name");
    $stmt->bind_param("i", $sectorId);
    $stmt->execute();
    $res = $stmt->get_result();

    $out = [];
    while ($res && ($row = $res->fetch_assoc())) $out[] = $row;

    $stmt->close();
    echo json_encode($out);
?>