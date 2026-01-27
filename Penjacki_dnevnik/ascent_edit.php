<?php
require_once "includes/guard.php";
require_approved();

$userId = (int)($_SESSION["user_id"] ?? 0);
$userRole = $_SESSION["user_role"] ?? "user";
$isPrivileged = in_array($userRole, ["administrator", "editor"], true);

$errors = [];
$success = "";

$ascentId = (int)($_GET["id"] ?? 0);
if ($ascentId <= 0) {
    echo "<section class='content'><h1>Uredi uspon</h1><p>Neispravan ID.</p></section>";
    exit;
}

if ($isPrivileged) {
    $stmt = $conn->prepare("
        SELECT a.*, 
               r.id AS r_id, r.name AS route_name, r.grade,
               s.id AS s_id, s.name AS sector_name,
               c.id AS c_id, c.name AS crag_name
        FROM ascents a
        JOIN routes r ON r.id = a.route_id
        JOIN sectors s ON s.id = r.sector_id
        JOIN crags c ON c.id = s.crag_id
        WHERE a.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $ascentId);
} else {
    $stmt = $conn->prepare("
        SELECT a.*, 
               r.id AS r_id, r.name AS route_name, r.grade,
               s.id AS s_id, s.name AS sector_name,
               c.id AS c_id, c.name AS crag_name
        FROM ascents a
        JOIN routes r ON r.id = a.route_id
        JOIN sectors s ON s.id = r.sector_id
        JOIN crags c ON c.id = s.crag_id
        WHERE a.id = ? AND a.user_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("ii", $ascentId, $userId);
}

$stmt->execute();
$res = $stmt->get_result();
$ascent = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$ascent) {
    echo "<section class='content'><h1>Uredi uspon</h1><p>Uspon nije pronađen ili nemaš pravo uređivanja.</p></section>";
    exit;
}

$cragId   = (int)($_GET["crag_id"] ?? (int)$ascent["c_id"]);
$sectorId = (int)($_GET["sector_id"] ?? (int)$ascent["s_id"]);
$routeIdGet = (int)($_GET["route_id"] ?? (int)$ascent["r_id"]);

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
        if (!$ok) { $sectorId = 0; $routeIdGet = 0; }
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
        foreach ($routes as $r) if ((int)$r["id"] === $routeIdGet) { $ok = true; break; }
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
        if ($isPrivileged) {
            $stmt = $conn->prepare("
                UPDATE ascents
                SET route_id=?, climbed_on=?, result=?, style=?, attempts=?, comment=?, is_public=?
                WHERE id=?
                LIMIT 1
            ");
            $stmt->bind_param("isssisii", $routeId, $climbedOn, $result, $style, $attempts, $comment, $isPublic, $ascentId);
        } else {
            $stmt = $conn->prepare("
                UPDATE ascents
                SET route_id=?, climbed_on=?, result=?, style=?, attempts=?, comment=?, is_public=?
                WHERE id=? AND user_id=?
                LIMIT 1
            ");
            $stmt->bind_param("isssisiii", $routeId, $climbedOn, $result, $style, $attempts, $comment, $isPublic, $ascentId, $userId);
        }

        if ($stmt->execute() && $stmt->affected_rows >= 0) {
            $success = "Uspon ažuriran!";

            header("Location: index.php?menu=14&id=" . $ascentId);
            exit;
        } else {
            $errors[] = "Greška pri spremanju promjena.";
        }
        $stmt->close();
    }
}
?>

<section class="content">
  <h1>Uredi uspon</h1>

  <h2>Trenutni odabir</h2>
  <p>
    <strong>Penjalište:</strong> <?php echo htmlspecialchars($ascent["crag_name"]); ?><br>
    <strong>Sektor:</strong> <?php echo htmlspecialchars($ascent["sector_name"]); ?><br>
    <strong>Smjer:</strong> <?php echo htmlspecialchars($ascent["route_name"]); ?>
    <?php if (!empty($ascent["grade"])) echo " (" . htmlspecialchars($ascent["grade"]) . ")"; ?>
  </p>

  <?php if ($errors): ?>
    <div class="form-error"><ul>
      <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <h2>Uredi detalje uspona</h2>

  <form class="contact-form" method="post"
        action="index.php?menu=14&id=<?php echo $ascentId; ?>&crag_id=<?php echo $cragId; ?>&sector_id=<?php echo $sectorId; ?>&route_id=<?php echo $routeIdGet; ?>">

    <input type="hidden" name="route_id" value="<?php echo $routeIdGet; ?>">

    <div class="form-row">
      <label for="climbed_on">Datum</label>
      <input id="climbed_on" name="climbed_on" type="date"
             value="<?php echo htmlspecialchars($ascent["climbed_on"]); ?>" required>
    </div>

    <div class="form-row">
      <label for="result">Rezultat</label>
      <select id="result" name="result">
        <option value="sent"  <?php if ($ascent["result"] === "sent") echo "selected"; ?>>sent</option>
        <option value="failed"<?php if ($ascent["result"] === "failed") echo "selected"; ?>>failed</option>
      </select>
    </div>

    <div class="form-row">
      <label for="style">Stil</label>
      <select id="style" name="style">
        <option value="onsight" <?php if ($ascent["style"] === "onsight") echo "selected"; ?>>onsight</option>
        <option value="flash"   <?php if ($ascent["style"] === "flash") echo "selected"; ?>>flash</option>
        <option value="redpoint"<?php if ($ascent["style"] === "redpoint") echo "selected"; ?>>redpoint</option>
        <option value="repeat"  <?php if ($ascent["style"] === "repeat") echo "selected"; ?>>repeat</option>
        <option value="attempt" <?php if ($ascent["style"] === "attempt") echo "selected"; ?>>attempt</option>
      </select>
    </div>

    <div class="form-row">
      <label for="attempts">Pokušaji</label>
      <input id="attempts" name="attempts" type="number" min="1" max="100"
             value="<?php echo (int)$ascent["attempts"]; ?>" required>
    </div>

    <div class="form-row">
      <label for="comment">Komentar</label>
      <textarea id="comment" name="comment" rows="4"><?php echo htmlspecialchars($ascent["comment"] ?? ""); ?></textarea>
    </div>

    <div class="form-row">
      <label>
        Javno (drugi vide ovaj uspon)
        <input type="checkbox" name="is_public" value="1" <?php if ((int)$ascent["is_public"] === 1) echo "checked"; ?>>
      </label>
    </div>

    <button class="btn" type="submit">Spremi promjene</button>
    <a class="btn" href="index.php?menu=12" style="margin-left:10px;">Natrag na moj dnevnik</a>
  </form>
</section>
