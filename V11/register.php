<?php
require_once "includes/auth.php";

$errors = [];
$successMsg = "";
$generatedUsername = "";
$generatedPassword = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first = trim($_POST["firstName"] ?? "");
    $last  = trim($_POST["lastName"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $countryId = (int)($_POST["country_id"] ?? 0);
    $city  = trim($_POST["city"] ?? "");
    $street = trim($_POST["street"] ?? "");
    $birth = trim($_POST["birth_date"] ?? "");

    if ($first === "") $errors[] = "Ime je obavezno.";
    if ($last === "") $errors[] = "Prezime je obavezno.";
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Unesite ispravnu e-mail adresu.";
    if ($countryId <= 0) $errors[] = "Odaberite državu.";
    if ($city === "") $errors[] = "Grad je obavezan.";
    if ($street === "") $errors[] = "Ulica je obavezna.";
    if ($birth === "") $errors[] = "Datum rođenja je obavezan.";

    // email unique
    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) $errors[] = "Ova e-mail adresa je već registrirana.";
        $stmt->close();
    }

    if (!$errors) {
        $base = make_username_base($first, $last);
        $generatedUsername = generate_unique_username($conn, $base);

        // Auto generirana lozinka
        $generatedPassword = generate_password(10);

        // Hash lozinke (preporučeno)
        $hash = password_hash($generatedPassword, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users (username, first_name, last_name, email, country_id, city, street, birth_date, password_hash)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssssisiss",
            $generatedUsername, $first, $last, $email, $countryId, $city, $street, $birth, $hash
        );

        if ($stmt->execute()) {
            $successMsg = "Registracija uspješna! Vaši podaci za prijavu su generirani:";
        } else {
            $errors[] = "Greška pri spremanju korisnika.";
        }
        $stmt->close();
    }
}

// Učitaj države za select
$countries = [];
$res = $conn->query("SELECT id, name FROM countries ORDER BY name");
while ($row = $res->fetch_assoc()) $countries[] = $row;
?>

<section class="content">
    <h1>Registracija</h1>
    <h2>Unesite podatke za registraciju</h2>

    <?php if ($errors): ?>
        <div class="form-error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($successMsg): ?>
        <div class="form-success">
            <p><?php echo htmlspecialchars($successMsg); ?></p>
            <p><strong>Korisničko ime:</strong> <?php echo htmlspecialchars($generatedUsername); ?></p>
            <p><strong>Lozinka:</strong> <?php echo htmlspecialchars($generatedPassword); ?></p>
            <p><a href="index.php?menu=8">Idi na prijavu</a></p>
        </div>
    <?php endif; ?>

    <form class="contact-form" method="post" action="index.php?menu=7">
        <div class="form-row">
            <label for="firstName">Ime <span class="req">*</span></label>
            <input id="firstName" name="firstName" type="text" required>
        </div>

        <div class="form-row">
            <label for="lastName">Prezime <span class="req">*</span></label>
            <input id="lastName" name="lastName" type="text" required>
        </div>

        <div class="form-row">
            <label for="email">E-mail adresa <span class="req">*</span></label>
            <input id="email" name="email" type="email" required>
        </div>

        <div class="form-row">
            <label for="country_id">Država <span class="req">*</span></label>
            <select id="country_id" name="country_id" required>
                <option value="">Odaberi državu</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?php echo (int)$c["id"]; ?>">
                        <?php echo htmlspecialchars($c["name"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <label for="city">Grad <span class="req">*</span></label>
            <input id="city" name="city" type="text" required>
        </div>

        <div class="form-row">
            <label for="street">Ulica <span class="req">*</span></label>
            <input id="street" name="street" type="text" required>
        </div>

        <div class="form-row">
            <label for="birth_date">Datum rođenja <span class="req">*</span></label>
            <input id="birth_date" name="birth_date" type="date" required>
        </div>

        <button class="btn" type="submit">Registriraj se</button>
    </form>
</section>
