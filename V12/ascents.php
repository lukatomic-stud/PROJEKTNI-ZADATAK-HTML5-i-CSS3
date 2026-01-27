<?php
    $res = $conn->query("
        SELECT a.id, a.climbed_on, a.result, a.style, a.attempts, a.comment,
            u.username,
            r.name AS route_name, r.grade, r.route_type,
            s.name AS sector_name,
            c.name AS crag_name
        FROM ascents a
        JOIN users u ON u.id = a.user_id
        JOIN routes r ON r.id = a.route_id
        JOIN sectors s ON s.id = r.sector_id
        JOIN crags c ON c.id = s.crag_id
        WHERE a.is_public = 1
        ORDER BY a.climbed_on DESC, a.id DESC
        LIMIT 100
    ");

    $items = [];
    while ($res && ($row = $res->fetch_assoc())) $items[] = $row;
?>

<section class="content">
  <h1>Usponi</h1>
  <h2>Javni usponi drugih korisnika</h2>

  <?php if (!$items): ?>
    <p>Nema javnih uspona.</p>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="cms-table">
        <thead>
          <tr>
            <th>Datum</th>
            <th>Korisnik</th>
            <th>Penjalište</th>
            <th>Sektor</th>
            <th>Smjer</th>
            <th>Težina</th>
            <th>Rezultat</th>
            <th>Stil</th>
            <th>Pokušaji</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $a): ?>
          <tr>
            <td><?php echo htmlspecialchars(date("d.m.Y.", strtotime($a["climbed_on"]))); ?></td>
            <td><?php echo htmlspecialchars($a["username"]); ?></td>
            <td><?php echo htmlspecialchars($a["crag_name"]); ?></td>
            <td><?php echo htmlspecialchars($a["sector_name"]); ?></td>
            <td><?php echo htmlspecialchars($a["route_name"]); ?></td>
            <td><?php echo htmlspecialchars($a["grade"] ?? ""); ?></td>
            <td><?php echo htmlspecialchars($a["result"]); ?></td>
            <td><?php echo htmlspecialchars($a["style"]); ?></td>
            <td><?php echo (int)$a["attempts"]; ?></td>
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
