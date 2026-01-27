<?php
  require_once "includes/guard.php";
  require_approved();
  require_role(["administrator", "editor"]);

  $cragId = (int)($_GET["crag_id"] ?? 0);
  $sectorId = (int)($_GET["sector_id"] ?? 0);

  $crags = [];
  $res = $conn->query("SELECT id, name FROM crags ORDER BY name");
  while ($res && ($row = $res->fetch_assoc())) $crags[] = $row;

  $sectors = [];
  if ($cragId > 0) {
      $stmt = $conn->prepare("SELECT id, name FROM sectors WHERE crag_id=? ORDER BY name");
      $stmt->bind_param("i", $cragId);
      $stmt->execute();
      $res = $stmt->get_result();
      while ($res && ($row = $res->fetch_assoc())) $sectors[] = $row;
      $stmt->close();

      if ($sectorId > 0) {
          $ok = false;
          foreach ($sectors as $s) if ((int)$s["id"] === $sectorId) { $ok = true; break; }
          if (!$ok) $sectorId = 0;
      }
  }

  $items = [];
  if ($cragId > 0) {
      if ($sectorId > 0) {
          $stmt = $conn->prepare("
              SELECT r.id, r.name, r.grade, r.route_type, r.length_m, r.bolts, r.created_at,
                    s.name AS sector_name, c.name AS crag_name
              FROM routes r
              JOIN sectors s ON s.id = r.sector_id
              JOIN crags c ON c.id = s.crag_id
              WHERE c.id = ? AND s.id = ?
              ORDER BY r.name
          ");
          $stmt->bind_param("ii", $cragId, $sectorId);
      } else {
          $stmt = $conn->prepare("
              SELECT r.id, r.name, r.grade, r.route_type, r.length_m, r.bolts, r.created_at,
                    s.name AS sector_name, c.name AS crag_name
              FROM routes r
              JOIN sectors s ON s.id = r.sector_id
              JOIN crags c ON c.id = s.crag_id
              WHERE c.id = ?
              ORDER BY s.name, r.name
          ");
          $stmt->bind_param("i", $cragId);
      }

      $stmt->execute();
      $res = $stmt->get_result();
      while ($res && ($row = $res->fetch_assoc())) $items[] = $row;
      $stmt->close();
  }
?>

<section class="content">
  <h1>CMS - Smjerovi</h1>

  <p>
    <a class="btn" href="index.php?menu=37<?php
      echo $cragId>0 ? "&crag_id=".$cragId : "";
      echo $sectorId>0 ? "&sector_id=".$sectorId : "";
    ?>">+ Novi smjer</a>
    <a class="btn" href="index.php?menu=30" style="margin-left:10px;">Penjališta</a>
    <a class="btn" href="index.php?menu=33" style="margin-left:10px;">Sektori</a>
  </p>

  <form class="contact-form" method="get" action="index.php">
    <input type="hidden" name="menu" value="36">

    <div class="form-row">
      <label for="crag_id">Penjalište</label>
      <select id="crag_id" name="crag_id">
        <option value="0">Odaberi</option>
        <?php foreach ($crags as $c): ?>
          <option value="<?php echo (int)$c["id"]; ?>" <?php if ((int)$c["id"] === $cragId) echo "selected"; ?>>
            <?php echo htmlspecialchars($c["name"]); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-row">
      <label for="sector_id">Sektor</label>
      <select id="sector_id" name="sector_id" <?php echo ($cragId>0 ? "" : "disabled"); ?>>
        <option value="0"><?php echo ($cragId>0 ? "Svi sektori" : "Odaberi penjalište prvo"); ?></option>
        <?php foreach ($sectors as $s): ?>
          <option value="<?php echo (int)$s["id"]; ?>" <?php if ((int)$s["id"] === $sectorId) echo "selected"; ?>>
            <?php echo htmlspecialchars($s["name"]); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button class="btn" type="submit">Primijeni</button>
  </form>

  <hr>

  <?php if ($cragId <= 0): ?>
    <p>Odaberi penjalište da vidiš rute.</p>
  <?php elseif (!$items): ?>
    <p>Nema ruta za odabrani filter.</p>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="cms-table">
        <thead>
          <tr>
            <th>Smjer</th>
            <th>Težina</th>
            <th>Tip</th>
            <th>Dužina</th>
            <th>Bolts</th>
            <th>Sektor</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars($r["name"]); ?></td>
              <td><?php echo htmlspecialchars($r["grade"] ?? ""); ?></td>
              <td><?php echo htmlspecialchars($r["route_type"]); ?></td>
              <td><?php echo htmlspecialchars($r["length_m"] ?? ""); ?></td>
              <td><?php echo htmlspecialchars($r["bolts"] ?? ""); ?></td>
              <td><?php echo htmlspecialchars($r["sector_name"]); ?></td>
              <td><a href="index.php?menu=38&id=<?php echo (int)$r["id"]; ?>">Uredi</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
