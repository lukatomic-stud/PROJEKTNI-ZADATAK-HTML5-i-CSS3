<?php
  require_once "includes/guard.php";
  require_approved();
  require_role(["administrator", "editor"]);

  $errors = [];
  $success = "";

  $name = "";
  $country = "";
  $city = "";
  $description = "";

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $name = trim($_POST["name"] ?? "");
      $country = trim($_POST["country"] ?? "");
      $city = trim($_POST["city"] ?? "");
      $description = trim($_POST["description"] ?? "");

      if ($name === "") $errors[] = "Naziv je obavezan.";

      if (!$errors) {
          $stmt = $conn->prepare("INSERT INTO crags (name, country, city, description) VALUES (?, ?, ?, ?)");
          $stmt->bind_param("ssss", $name, $country, $city, $description);
          if ($stmt->execute()) {
              $success = "Penjalište dodano!";
              $name = $country = $city = $description = "";
          } else {
              $errors[] = "Greška pri spremanju.";
          }
          $stmt->close();
      }
  }
?>

<section class="content">
  <h1>CMS - Novo penjalište</h1>

  <?php if ($success): ?>
    <div class="form-success"><p><?php echo htmlspecialchars($success); ?></p></div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="form-error"><ul>
      <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form class="contact-form" method="post" action="index.php?menu=31">
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
      <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
    </div>

    <button class="btn" type="submit">Spremi</button>
    <a class="btn" href="index.php?menu=30" style="margin-left:10px;">Natrag</a>
  </form>
</section>