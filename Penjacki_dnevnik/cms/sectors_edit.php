<?php
  require_once "includes/guard.php";
  require_approved();
  require_role(["administrator", "editor"]);

  $id = (int)($_GET["id"] ?? 0);
  if ($id <= 0) {
      echo "<section class='content'><h1>CMS - Uredi sektor</h1><p>Neispravan ID.</p></section>";
      exit;
  }

  $stmt = $conn->prepare("SELECT * FROM sectors WHERE id=? LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $item = $res ? $res->fetch_assoc() : null;
  $stmt->close();

  if (!$item) {
      echo "<section class='content'><h1>CMS - Uredi sektor</h1><p>Sektor nije pronađen.</p></section>";
      exit;
  }

  $errors = [];
  $success = "";

  $crags = [];
  $res = $conn->query("SELECT id, name FROM crags ORDER BY name");
  while ($res && ($row = $res->fetch_assoc())) $crags[] = $row;

  $cragId = (int)($item["crag_id"] ?? 0);
  $name = $item["name"] ?? "";
  $description = $item["description"] ?? "";

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $cragId = (int)($_POST["crag_id"] ?? 0);
      $name = trim($_POST["name"] ?? "");
      $description = trim($_POST["description"] ?? "");

      if ($cragId <= 0) $errors[] = "Odaberi penjalište.";
      if ($name === "") $errors[] = "Naziv sektora je obavezan.";

      if (!$errors) {
          $stmt = $conn->prepare("UPDATE sectors SET crag_id=?, name=?, description=? WHERE id=? LIMIT 1");
          $stmt->bind_param("issi", $cragId, $name, $description, $id);
          if ($stmt->execute()) {
              $success = "Spremljeno!";
          } else {
              $errors[] = "Greška pri spremanju.";
          }
          $stmt->close();
      }
  }
?>

<section class="content">
  <h1>CMS - Uredi sektor</h1>

  <?php if ($success): ?>
    <div class="form-success"><p><?php echo htmlspecialchars($success); ?></p></div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="form-error"><ul>
      <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form class="contact-form" method="post" action="index.php?menu=35&id=<?php echo $id; ?>">
    <div class="form-row">
      <label for="crag_id">Penjalište *</label>
      <select id="crag_id" name="crag_id" required>
        <option value="">Odaberi</option>
        <?php foreach ($crags as $c): ?>
          <option value="<?php echo (int)$c["id"]; ?>" <?php if ((int)$c["id"] === $cragId) echo "selected"; ?>>
            <?php echo htmlspecialchars($c["name"]); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-row">
      <label for="name">Naziv sektora *</label>
      <input id="name" name="name" type="text" value="<?php echo htmlspecialchars($name); ?>" required>
    </div>

    <div class="form-row">
      <label for="description">Opis</label>
      <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
    </div>

    <button class="btn" type="submit">Spremi</button>
    <a class="btn" href="index.php?menu=33&crag_id=<?php echo $cragId; ?>" style="margin-left:10px;">Natrag</a>
  </form>
</section>
