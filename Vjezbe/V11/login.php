<?php
$errors = [];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username === "" || $password === "") {
        $errors[] = "Unesite korisničko ime i lozinku.";
    } else {
        $stmt = $conn->prepare("
            SELECT id, username, password_hash, role, is_approved
            FROM users
            WHERE username = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();

            if (!password_verify($password, $user["password_hash"])) {
                $errors[] = "Pogrešna lozinka.";
            } 

            elseif ((int)$user["is_approved"] !== 1) {
                $errors[] = "Račun je registriran, ali još nije odobren od administratora.";
            } 

            else {
                $_SESSION["user_id"] = (int)$user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];
                $_SESSION["is_approved"] = (int)$user["is_approved"];

                $msg = "Uspješno prijavljen!";
            }
        } else {
            $errors[] = "Korisnik ne postoji.";
        }

        $stmt->close();
    }
}
?>

<section class="content">
    <h1>Prijava</h1>
    <h2>Unesite korisničko ime i lozinku</h2>

    <?php if ($msg): ?>
        <div class="form-success">
            <p><?php echo htmlspecialchars($msg); ?></p>
            <p>
                <a href="index.php?menu=20">Ulazak u administraciju</a>
            </p>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="form-error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="contact-form" method="post" action="index.php?menu=8">
        <div class="form-row">
            <label for="username">Korisničko ime</label>
            <input id="username" name="username" type="text" required>
        </div>

        <div class="form-row">
            <label for="password">Lozinka</label>
            <input id="password" name="password" type="password" required>
        </div>

        <button class="btn" type="submit">Prijavi se</button>
    </form>
</section>
