<?php
  require_once "includes/guard.php";
  require_approved();
  require_role(["administrator", "editor"]);

  $id = (int)($_GET["id"] ?? 0);
  if ($id <= 0) {
      echo "<section class='content'><h1>CMS - Uredi penjalište</h1><p>Neispravan ID.</p></section>";
      exit;
  }

  $stmt = $conn->prepare("SELECT * FROM crags WHERE id=? LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $item = $res ? $res->fetch_assoc() : null;
  $stmt->close();

  if (!$item) {
      echo "<section class='content'><h1>CMS - Uredi penjalište</h1><p>Penjalište nije pronađeno.</p></section>";
      exit;
  }

  $errors = [];
  $success = "";

  $name = $item["name"] ?? "";
  $country = $item["country"] ?? "";
  $city = $item["city"] ?? "";
  $description = $item["description"] ?? "";

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $name = trim($_POST["name"] ?? "");
      $country = trim($_POST["country"] ?? "");
      $city = trim($_POST["city"] ?? "");
      $description = trim($_POST["description"] ?? "");

      if ($name === "") $errors[] = "Naziv je obavezan.";

      if (!$errors) {
          $stmt = $conn->prepare("UPDATE crags SET name=?, country=?, city=?, description=? WHERE id=? LIMIT 1");
          $stmt->bind_param("ssssi", $name, $country, $city, $description, $id);
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
  <h1>CMS - Uredi penjalište</h1>

  <?php if ($success): ?>
    <div class="form-success"><p><?php echo htmlspecialchars($success); ?></p></div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="form-error"><ul>
      <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form class="contact-form" method="post" action="index.php?menu=32&id=<?php echo $id; ?>">
    <div class="form-row">
      <label for="name">Naziv *</label>
      <input id="name" name="name" type="text" value="<?php echo htmlspecialchars($name); ?>" required>
    </div>

    <div class="form-row">
      <label for="country">Država</label>
      <input id="country" name="country" type="text" value="<?php echo htmlspecialchars($country); ?>">
    </div>

    <div class="form-row">
      <label for="city">Grad / mjesto</label>
      <input id="city" name="city" type="text" value="<?php echo htmlspecialchars($city); ?>">
    </div>

    <div class="form-row">
      <label for="description">Opis</label>
      <textarea id="description" name="description" rows="5">
        <?php echo htmlspecialchars($description); ?>
      </textarea>
    </div>

    <button class="btn" type="submit">Spremi</button>
  </form>

  <hr>

  <h2>Brisanje</h2>

  <form class="contact-form" method="post" action="index.php?menu=32&id=<?php echo $id; ?>">
    <input type="hidden" name="action" value="delete">

    <div class="form-row">
      <label class="checkbox-row">
        Obriši sve povezano (sektori, smjerovi i usponi) – OPREZ
        <input type="checkbox" name="force_delete" value="1">
      </label>
    </div>

    <div class="form-actions">
      <button class="btn" type="submit">Obriši penjalište</button>
      <a class="btn" href="index.php?menu=30">Natrag</a>
    </div>
  </form>
</section>
