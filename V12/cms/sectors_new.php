<?php
require_once "includes/guard.php";
require_approved();
require_role(["administrator", "editor"]);

$errors = [];
$success = "";

$cragId = (int)($_GET["crag_id"] ?? 0);

$crags = [];
$res = $conn->query("SELECT id, name FROM crags ORDER BY name");
while ($res && ($row = $res->fetch_assoc())) $crags[] = $row;

$name = "";
$description = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cragId = (int)($_POST["crag_id"] ?? 0);
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($cragId <= 0) $errors[] = "Odaberi penjalište.";
    if ($name === "") $errors[] = "Naziv sektora je obavezan.";

    if (!$errors) {
        $stmt = $conn->prepare("INSERT INTO sectors (crag_id, name, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $cragId, $name, $description);
        if ($stmt->execute()) {
            $success = "Sektor dodan!";
            $name = $description = "";
        } else {
            $errors[] = "Greška pri spremanju.";
        }
        $stmt->close();
    }
}
?>

<section class="content">
  <h1>CMS - Novi sektor</h1>

  <?php if ($success): ?>
    <div class="form-success"><p><?php echo htmlspecialchars($success); ?></p></div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="form-error"><ul>
      <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form class="contact-form" method="post" action="index.php?menu=34<?php echo $cragId>0 ? "&crag_id=".$cragId : ""; ?>">
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
    <a class="btn" href="index.php?menu=33<?php echo $cragId>0 ? "&crag_id=".$cragId : ""; ?>" style="margin-left:10px;">Natrag</a>
  </form>
</section>
