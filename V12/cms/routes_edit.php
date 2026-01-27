<?php
  require_once "includes/guard.php";
  require_approved();
  require_role(["administrator", "editor"]);

  $id = (int)($_GET["id"] ?? 0);
  if ($id <= 0) {
      echo "<section class='content'><h1>CMS - Uredi smjer</h1><p>Neispravan ID.</p></section>";
      exit;
  }

  $stmt = $conn->prepare("
      SELECT r.*, s.id AS s_id, s.crag_id AS c_id
      FROM routes r
      JOIN sectors s ON s.id = r.sector_id
      WHERE r.id=?
      LIMIT 1
  ");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $item = $res ? $res->fetch_assoc() : null;
  $stmt->close();

  if (!$item) {
      echo "<section class='content'><h1>CMS - Uredi smjer</h1><p>Smjer nije pronađen.</p></section>";
      exit;
  }

  $errors = [];
  $success = "";

  $cragId = (int)($_GET["crag_id"] ?? (int)$item["c_id"]);
  $sectorId = (int)($_GET["sector_id"] ?? (int)$item["s_id"]);

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

  $name = $item["name"] ?? "";
  $grade = $item["grade"] ?? "";
  $routeType = $item["route_type"] ?? "sport";
  $lengthM = $item["length_m"] ?? "";
  $bolts = $item["bolts"] ?? "";
  $description = $item["description"] ?? "";

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $sectorId = (int)($_POST["sector_id"] ?? 0);
      $name = trim($_POST["name"] ?? "");
      $grade = trim($_POST["grade"] ?? "");
      $routeType = $_POST["route_type"] ?? "sport";
      $lengthM = trim($_POST["length_m"] ?? "");
      $bolts = trim($_POST["bolts"] ?? "");
      $description = trim($_POST["description"] ?? "");

      $allowedTypes = ["sport","trad","boulder","toprope"];

      if ($sectorId <= 0) $errors[] = "Odaberi sektor.";
      if ($name === "") $errors[] = "Naziv smjera je obavezan.";
      if (!in_array($routeType, $allowedTypes, true)) $errors[] = "Neispravan tip rute.";

      $lengthVal = null;
      $boltsVal = null;
      if ($lengthM !== "") {
          $lengthVal = (int)$lengthM;
          if ($lengthVal < 1 || $lengthVal > 2000) $errors[] = "Dužina mora biti 1–2000.";
      }
      if ($bolts !== "") {
          $boltsVal = (int)$bolts;
          if ($boltsVal < 0 || $boltsVal > 200) $errors[] = "Bolts mora biti 0–200.";
      }

      if (!$errors) {
          $stmt = $conn->prepare("SELECT id FROM sectors WHERE id=? LIMIT 1");
          $stmt->bind_param("i", $sectorId);
          $stmt->execute();
          $exists = $stmt->get_result()->num_rows === 1;
          $stmt->close();
          if (!$exists) $errors[] = "Sektor ne postoji.";
      }

      if (!$errors) {
          $stmt = $conn->prepare("
              UPDATE routes
              SET sector_id=?, name=?, grade=?, length_m=?, bolts=?, route_type=?, description=?
              WHERE id=?
              LIMIT 1
          ");
          $stmt->bind_param("ississsi", $sectorId, $name, $grade, $lengthVal, $boltsVal, $routeType, $description, $id);

          if ($stmt->execute()) {
              header("Location: index.php?menu=36&crag_id=" . $cragId . "&sector_id=" . $sectorId);
              exit;
          } else {
              $errors[] = "Greška pri spremanju.";
          }
          $stmt->close();
      }
  }
?>

<section class="content">
  <h1>CMS - Uredi smjer</h1>

  <?php if ($errors): ?>
    <div class="form-error"><ul>
      <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form class="contact-form" method="get" action="index.php">
    <input type="hidden" name="menu" value="38">
    <input type="hidden" name="id" value="<?php echo $id; ?>">

    <div class="form-row">
      <label for="crag_id">Penjalište</label>
      <select id="crag_id" name="crag_id">
        <option value="0"></option>
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
        <option value="0"><?php echo ($cragId>0 ? "Odaberi" : "Odaberi penjalište prvo"); ?></option>
        <?php foreach ($sectors as $s): ?>
          <option value="<?php echo (int)$s["id"]; ?>" <?php if ((int)$s["id"] === $sectorId) echo "selected"; ?>>
            <?php echo htmlspecialchars($s["name"]); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button class="btn" type="submit">Učitaj odabire</button>
  </form>

  <hr>

  <?php if ($sectorId <= 0): ?>
    <p>Odaberi penjalište i sektor gore, pa onda spremi smjer.</p>
  <?php else: ?>
    <form class="contact-form" method="post"
          action="index.php?menu=38&id=<?php echo $id; ?>&crag_id=<?php echo $cragId; ?>&sector_id=<?php echo $sectorId; ?>">

      <input type="hidden" name="sector_id" value="<?php echo $sectorId; ?>">

      <div class="form-row">
        <label for="name">Naziv smjera *</label>
        <input id="name" name="name" type="text" value="<?php echo htmlspecialchars($name); ?>" required>
      </div>

      <div class="form-row">
        <label for="grade">Težina</label>
        <input id="grade" name="grade" type="text" value="<?php echo htmlspecialchars($grade); ?>">
      </div>

      <div class="form-row">
        <label for="route_type">Tip</label>
        <select id="route_type" name="route_type">
          <option value="sport" <?php if ($routeType==="sport") echo "selected"; ?>>sport</option>
          <option value="trad" <?php if ($routeType==="trad") echo "selected"; ?>>trad</option>
          <option value="boulder" <?php if ($routeType==="boulder") echo "selected"; ?>>boulder</option>
          <option value="toprope" <?php if ($routeType==="toprope") echo "selected"; ?>>toprope</option>
        </select>
      </div>

      <div class="form-row">
        <label for="length_m">Dužina (m)</label>
        <input id="length_m" name="length_m" type="number" min="1" max="2000"
               value="<?php echo htmlspecialchars($lengthM); ?>">
      </div>

      <div class="form-row">
        <label for="bolts">Bolts</label>
        <input id="bolts" name="bolts" type="number" min="0" max="200"
               value="<?php echo htmlspecialchars($bolts); ?>">
      </div>

      <div class="form-row">
        <label for="description">Opis</label>
        <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
      </div>

      <button class="btn" type="submit">Spremi</button>
      <a class="btn" href="index.php?menu=36&crag_id=<?php echo $cragId; ?>&sector_id=<?php echo $sectorId; ?>" style="margin-left:10px;">Natrag</a>
    </form>
  <?php endif; ?>
</section>
