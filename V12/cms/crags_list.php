<?php
    require_once "includes/guard.php";
    require_approved();
    require_role(["administrator", "editor"]);

    $items = [];
    $res = $conn->query("SELECT id, name, country, city, created_at FROM crags ORDER BY name");
    while ($res && ($row = $res->fetch_assoc())) $items[] = $row;
?>

<section class="content">
  <h1>CMS - Penjališta</h1>

  <p>
    <a class="btn" href="index.php?menu=31">+ Novo penjalište</a>
    <a class="btn" href="index.php?menu=33" style="margin-left:10px;">Sektori</a>
    <a class="btn" href="index.php?menu=36" style="margin-left:10px;">Smjerovi</a>
  </p>

  <?php if (!$items): ?>
    <p>Nema penjališta.</p>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="cms-table">
        <thead>
          <tr>
            <th>Naziv</th>
            <th>Država</th>
            <th>Grad</th>
            <th>Datum</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $c): ?>
            <tr>
              <td><?php echo htmlspecialchars($c["name"]); ?></td>
              <td><?php echo htmlspecialchars($c["country"] ?? ""); ?></td>
              <td><?php echo htmlspecialchars($c["city"] ?? ""); ?></td>
              <td><?php echo htmlspecialchars(date("d.m.Y.", strtotime($c["created_at"]))); ?></td>
              <td>
                <a href="index.php?menu=32&id=<?php echo (int)$c["id"]; ?>">Uredi</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
