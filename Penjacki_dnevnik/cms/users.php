<?php
require_once "includes/guard.php";
require_role(["administrator"]);

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = (int)($_POST["user_id"] ?? 0);
    $newRole = $_POST["role"] ?? "";
    $approved = isset($_POST["is_approved"]) ? 1 : 0;

    $allowedRoles = ["user", "editor", "administrator"];
    if ($userId <= 0) $errors[] = "Neispravan korisnik.";
    if (!in_array($newRole, $allowedRoles, true)) $errors[] = "Neispravna rola.";

    if (!$errors && (int)$_SESSION["user_id"] === $userId && $newRole !== "administrator") {
        $errors[] = "Ne možeš sebi ukloniti administratorska prava.";
    }

    if (!$errors) {
        $stmt = $conn->prepare("UPDATE users SET role=?, is_approved=? WHERE id=?");
        $stmt->bind_param("sii", $newRole, $approved, $userId);
        if ($stmt->execute()) {
            $success = "Promjene spremljene.";
            if ((int)$_SESSION["user_id"] === $userId) {
                $_SESSION["role"] = $newRole;
                $_SESSION["is_approved"] = $approved;
            }
        } else {
            $errors[] = "Greška pri spremanju.";
        }
        $stmt->close();
    }
}

$users = [];
$res = $conn->query("SELECT id, username, first_name, last_name, email, role, is_approved, created_at FROM users ORDER BY created_at DESC");
while ($res && ($row = $res->fetch_assoc())) $users[] = $row;
?>

<section class="content">
    <h1>Korisnici</h1>
    <h2>Upravljanje rolama i odobrenjima</h2>

    <?php if ($success): ?>
        <div class="form-success"><p><?php echo htmlspecialchars($success); ?></p></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="form-error"><ul>
            <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <div style="overflow-x:auto;">
        <table class="cms-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Ime</th>
                    <th>Email</th>
                    <th>Rola</th>
                    <th>Odobren</th>
                    <th>Kreiran</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo (int)$u["id"]; ?></td>
                    <td><?php echo htmlspecialchars($u["username"]); ?></td>
                    <td><?php echo htmlspecialchars($u["first_name"]." ".$u["last_name"]); ?></td>
                    <td><?php echo htmlspecialchars($u["email"]); ?></td>

                    <td>
                        <form method="post" action="index.php?menu=21" class="inline-form">
                            <input type="hidden" name="user_id" value="<?php echo (int)$u["id"]; ?>">
                            <select name="role">
                                <option value="user" <?php if ($u["role"]==="user") echo "selected"; ?>>user</option>
                                <option value="editor" <?php if ($u["role"]==="editor") echo "selected"; ?>>editor</option>
                                <option value="administrator" <?php if ($u["role"]==="administrator") echo "selected"; ?>>administrator</option>
                            </select>
                    </td>

                    <td style="text-align:center;">
                        <input type="checkbox" name="is_approved" value="1" <?php if ((int)$u["is_approved"]===1) echo "checked"; ?>>
                    </td>

                    <td><?php echo htmlspecialchars($u["created_at"]); ?></td>

                    <td>
                        <button class="btn" type="submit">Spremi</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$users): ?>
                <tr><td colspan="8">Nema korisnika.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
