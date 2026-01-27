<?php
  require_once "includes/guard.php";
  require_approved();

  $userId = (int)($_SESSION["user_id"] ?? 0);

  $errors = [];
  $success = "";

  $cragId   = (int)($_GET["crag_id"] ?? 0);
  $sectorId = (int)($_GET["sector_id"] ?? 0);
  $routeIdGet = (int)($_GET["route_id"] ?? 0);

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
          foreach ($sectors as $s) if ((int)$s["id"] === $sectorId) { 
            $ok = true; 
            break; 
          }
          if (!$ok) $sectorId = 0;
      }
  }

  $routes = [];
  if ($sectorId > 0) {
      $stmt = $conn->prepare("SELECT id, name, grade FROM routes WHERE sector_id=? ORDER BY name");
      $stmt->bind_param("i", $sectorId);
      $stmt->execute();
      $res = $stmt->get_result();
      while ($res && ($row = $res->fetch_assoc())) $routes[] = $row;
      $stmt->close();

      if ($routeIdGet > 0) {
          $ok = false;
          foreach ($routes as $r) if ((int)$r["id"] === $routeIdGet) { 
            $ok = true; 
            break; 
          }
          if (!$ok) $routeIdGet = 0;
      }
  }

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $routeId   = (int)($_POST["route_id"] ?? 0);
      $climbedOn = trim($_POST["climbed_on"] ?? "");
      $result    = $_POST["result"] ?? "sent";
      $style     = $_POST["style"] ?? "redpoint";
      $attempts  = (int)($_POST["attempts"] ?? 1);
      $comment   = trim($_POST["comment"] ?? "");
      $isPublic  = isset($_POST["is_public"]) ? 1 : 0;

      $allowedResult = ["sent","failed"];
      $allowedStyle  = ["onsight","flash","redpoint","repeat","attempt"];

      if ($routeId <= 0) $errors[] = "Odaberi smjer.";
      if ($climbedOn === "") $errors[] = "Odaberi datum.";
      if (!in_array($result, $allowedResult, true)) $errors[] = "Neispravan rezultat.";
      if (!in_array($style, $allowedStyle, true)) $errors[] = "Neispravan stil.";
      if ($attempts < 1 || $attempts > 100) $errors[] = "Pokušaji moraju biti 1–100.";

      if (!$errors) {
          $stmt = $conn->prepare("SELECT id FROM routes WHERE id=? LIMIT 1");
          $stmt->bind_param("i", $routeId);
          $stmt->execute();
          $exists = $stmt->get_result()->num_rows === 1;
          $stmt->close();
          if (!$exists) $errors[] = "Odabrani smjer ne postoji.";
      }

      if (!$errors) {
          $stmt = $conn->prepare("
              INSERT INTO ascents (user_id, route_id, climbed_on, result, style, attempts, comment, is_public)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)
          ");
          $stmt->bind_param("iisssisi", $userId, $routeId, $climbedOn, $result, $style, $attempts, $comment, $isPublic);
          if ($stmt->execute()) {
              $success = "Uspon spremljen!";
          } else {
              $errors[] = "Greška pri spremanju uspona.";
          }
          $stmt->close();
      }
  }
?>

<section class="content">
  <h1>Dodaj uspon</h1>
  <h2>Odaberi penjalište → sektor → smjer</h2>

  <?php if ($success): ?>
    <div class="form-success">
      <p><?php echo htmlspecialchars($success); ?></p>
      <p><a href="index.php?menu=12">Pogledaj moj dnevnik</a></p>
    </div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="form-error"><ul>
      <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form class="contact-form" method="get" action="index.php">
    <input type="hidden" name="menu" value="13">

    <div class="form-row">
      <label for="crag_id">Penjalište</label>
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
      <label for="sector_id">Sektor</label>
      <select id="sector_id" name="sector_id" <?php echo ($cragId>0 ? "" : "disabled"); ?>>
        <option value=""><?php echo ($cragId>0 ? "Odaberi" : "Odaberi penjalište prvo"); ?></option>
        <?php foreach ($sectors as $s): ?>
          <option value="<?php echo (int)$s["id"]; ?>" <?php if ((int)$s["id"] === $sectorId) echo "selected"; ?>>
            <?php echo htmlspecialchars($s["name"]); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-row">
      <label for="route_id_get">Smjer</label>
      <select id="route_id_get" name="route_id" <?php echo ($sectorId>0 ? "" : "disabled"); ?>>
        <option value=""><?php echo ($sectorId>0 ? "Odaberi" : "Odaberi sektor prvo"); ?></option>
        <?php foreach ($routes as $r): ?>
          <option value="<?php echo (int)$r["id"]; ?>" <?php if ((int)$r["id"] === $routeIdGet) echo "selected"; ?>>
            <?php echo htmlspecialchars($r["name"] . (!empty($r["grade"]) ? " (" . $r["grade"] . ")" : "")); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button class="btn" type="submit">Učitaj odabire</button>
  </form>

  <hr>
  <?php if ($routeIdGet > 0): ?>
    <form class="contact-form" method="post" action="index.php?menu=13&crag_id=<?php echo $cragId; ?>&sector_id=<?php echo $sectorId; ?>&route_id=<?php echo $routeIdGet; ?>">
      <input type="hidden" name="route_id" value="<?php echo $routeIdGet; ?>">

      <div class="form-row">
        <label for="climbed_on">Datum</label>
        <input id="climbed_on" name="climbed_on" type="date" required>
      </div>

      <div class="form-row">
        <label for="result">Rezultat</label>
        <select id="result" name="result">
          <option value="sent">sent</option>
          <option value="failed">failed</option>
        </select>
      </div>

      <div class="form-row">
        <label for="style">Stil</label>
        <select id="style" name="style">
          <option value="onsight">onsight</option>
          <option value="flash">flash</option>
          <option value="redpoint" selected>redpoint</option>
          <option value="repeat">repeat</option>
          <option value="attempt">attempt</option>
        </select>
      </div>

      <div class="form-row">
        <label for="attempts">Pokušaji</label>
        <input id="attempts" name="attempts" type="number" min="1" max="100" value="1" required>
      </div>

      <div class="form-row">
        <label for="comment">Komentar</label>
        <textarea id="comment" name="comment" rows="4"></textarea>
      </div>

      <div class="form-row">
        <label class="checkbox-row">
          Javno (drugi vide ovaj uspon)
          <input type="checkbox" name="is_public" value="1" checked>
        </label>
      </div>


      <button class="btn" type="submit">Spremi uspon</button>
    </form>
  <?php else: ?>
    <p>Odaberi smjer gore i klikni <strong>Učitaj odabire</strong>, pa će se prikazati forma za spremanje uspona.</p>
  <?php endif; ?>
</section>
