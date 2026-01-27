<?php
    require_once "includes/guard.php";
    require_approved();

    $userId = (int)($_SESSION["user_id"] ?? 0);

    $stmt = $conn->prepare("
        SELECT a.id, a.climbed_on, a.result, a.style, a.attempts, a.comment, a.is_public,
            r.name AS route_name, r.grade,
            s.name AS sector_name,
            c.name AS crag_name
        FROM ascents a
        JOIN routes r ON r.id = a.route_id
        JOIN sectors s ON s.id = r.sector_id
        JOIN crags c ON c.id = s.crag_id
        WHERE a.user_id = ?
        ORDER BY a.climbed_on DESC, a.id DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();

    $items = [];
    while ($res && ($row = $res->fetch_assoc())) $items[] = $row;
    $stmt->close();
?>

<section class="content">
  <h1>Moj dnevnik</h1>
  <h2>Moji usponi</h2>

  <p>
    <a href="index.php?menu=13">+ Dodaj uspon</a>
  </p>

  <?php if (!$items): ?>
    <p>Nema uspona. Dodaj prvi!</p>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="cms-table">
        <thead>
          <tr>
            <th>Datum</th>
            <th>Penjalište</th>
            <th>Sektor</th>
            <th>Smjer</th>
            <th>Težina</th>
            <th>Rezultat</th>
            <th>Stil</th>
            <th>Public</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $a): ?>
          <tr>
            <td><?php echo htmlspecialchars(date("d.m.Y.", strtotime($a["climbed_on"]))); ?></td>
            <td><?php echo htmlspecialchars($a["crag_name"]); ?></td>
            <td><?php echo htmlspecialchars($a["sector_name"]); ?></td>
            <td><?php echo htmlspecialchars($a["route_name"]); ?></td>
            <td><?php echo htmlspecialchars($a["grade"] ?? ""); ?></td>
            <td><?php echo htmlspecialchars($a["result"]); ?></td>
            <td><?php echo htmlspecialchars($a["style"]); ?></td>
            <td><?php echo ((int)$a["is_public"]===1) ? "DA" : "NE"; ?></td>
            <td><a href="index.php?menu=14&id=<?php echo (int)$a["id"]; ?>">Uredi</a></td>
          </tr>
          <?php if (!empty($a["comment"])): ?>
          <tr>
            <td colspan="9" style="background:#fafafa;">
              <em><?php echo nl2br(htmlspecialchars($a["comment"])); ?></em>
            </td>
          </tr>
          <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
