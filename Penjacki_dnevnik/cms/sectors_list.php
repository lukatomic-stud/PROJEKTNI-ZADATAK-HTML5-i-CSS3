<?php
  require_once "includes/guard.php";
  require_approved();
  require_role(["administrator", "editor"]);

  $cragId = (int)($_GET["crag_id"] ?? 0);

  $crags = [];
  $res = $conn->query("SELECT id, name FROM crags ORDER BY name");
  while ($res && ($row = $res->fetch_assoc())) $crags[] = $row;

  $items = [];
  if ($cragId > 0) {
      $stmt = $conn->prepare("
          SELECT s.id, s.name, s.created_at, c.name AS crag_name
          FROM sectors s
          JOIN crags c ON c.id = s.crag_id
          WHERE s.crag_id=?
          ORDER BY s.name
      ");
      
      $stmt->bind_param("i", $cragId);
      $stmt->execute();
      $res = $stmt->get_result();
      while ($res && ($row = $res->fetch_assoc())) $items[] = $row;
      $stmt->close();
  }
?>

<section class="content">
  <h1>CMS - Sektori</h1>

  <p>
    <a class="btn" href="index.php?menu=34<?php echo $cragId>0 ? "&crag_id=".$cragId : ""; ?>">+ Novi sektor</a>
    <a class="btn" href="index.php?menu=30" style="margin-left:10px;">Penjališta</a>
    <a class="btn" href="index.php?menu=36" style="margin-left:10px;">Smjerovi</a>
  </p>

  <form class="contact-form" method="get" action="index.php">
    <input type="hidden" name="menu" value="33">
    <div class="form-row">
      <label for="crag_id">Filtriraj po penjalištu</label>
      <select id="crag_id" name="crag_id">
        <option value="0">Odaberi</option>
        <?php foreach ($crags as $c): ?>
          <option value="<?php echo (int)$c["id"]; ?>" <?php if ((int)$c["id"] === $cragId) echo "selected"; ?>>
            <?php echo htmlspecialchars($c["name"]); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn" type="submit">Primijeni</button>
  </form>

  <hr>

  <?php if ($cragId <= 0): ?>
    <p>Odaberi penjalište da vidiš sektore.</p>
  <?php elseif (!$items): ?>
    <p>Nema sektora za odabrano penjalište.</p>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="cms-table">
        <thead>
          <tr>
            <th>Sektor</th>
            <th>Penjalište</th>
            <th>Datum</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $s): ?>
            <tr>
              <td><?php echo htmlspecialchars($s["name"]); ?></td>
              <td><?php echo htmlspecialchars($s["crag_name"]); ?></td>
              <td><?php echo htmlspecialchars(date("d.m.Y.", strtotime($s["created_at"]))); ?></td>
              <td><a href="index.php?menu=35&id=<?php echo (int)$s["id"]; ?>">Uredi</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
