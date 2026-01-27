<?php
    require_once "includes/guard.php";
    require_approved();

    $role = $_SESSION["role"] ?? "user";
    $userId = (int)($_SESSION["user_id"] ?? 0);

    $errors = [];
    $success = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $action = $_POST["action"] ?? "";
        $id = (int)($_POST["id"] ?? 0);

        if ($id <= 0) {
            $errors[] = "Neispravan ID.";
        } else {
            if ($action === "archive") {
                if (!in_array($role, ["editor", "administrator"], true)) {
                    $errors[] = "Nemaš pravo arhiviranja.";
                } else {
                    $actionStmt = $conn->prepare("UPDATE news SET is_archived = 1 WHERE id=?");
                    $actionStmt->bind_param("i", $id);
                    $actionStmt->execute();
                    $actionStmt->close();

                    header("Location: index.php?menu=23&ok=archived");
                    exit;
                }

            } elseif ($action === "unarchive") {
                if (!in_array($role, ["editor", "administrator"], true)) {
                    $errors[] = "Nemaš pravo.";
                } else {
                    $actionStmt = $conn->prepare("UPDATE news SET is_archived = 0 WHERE id=?");
                    $actionStmt->bind_param("i", $id);
                    $actionStmt->execute();
                    $actionStmt->close();

                    header("Location: index.php?menu=23&ok=unarchived");
                    exit;
                }

            } elseif ($action === "approve") {
                if ($role !== "administrator") {
                    $errors[] = "Samo administrator može odobriti.";
                } else {
                    $actionStmt = $conn->prepare("UPDATE news SET is_approved = 1 WHERE id=?");
                    $actionStmt->bind_param("i", $id);
                    $actionStmt->execute();
                    $actionStmt->close();

                    header("Location: index.php?menu=23&ok=approved");
                    exit;
                }

            } elseif ($action === "unapprove") {
                if ($role !== "administrator") {
                    $errors[] = "Samo administrator može.";
                } else {
                    $actionStmt = $conn->prepare("UPDATE news SET is_approved = 0 WHERE id=?");
                    $actionStmt->bind_param("i", $id);
                    $actionStmt->execute();
                    $actionStmt->close();

                    header("Location: index.php?menu=23&ok=unapproved");
                    exit;
                }

            } elseif ($action === "delete") {
                if ($role !== "administrator") {
                    $errors[] = "Samo administrator može brisati.";
                } else {
                    $actionStmt = $conn->prepare("DELETE FROM news WHERE id=?");
                    $actionStmt->bind_param("i", $id);
                    $actionStmt->execute();
                    $actionStmt->close();

                    header("Location: index.php?menu=23&ok=deleted");
                    exit;
                }

            } else {
                $errors[] = "Nepoznata akcija.";
            }
        }
    }

    if (isset($_GET["ok"])) {
        $ok = $_GET["ok"];
        if ($ok === "archived") $success = "Vijest je arhivirana.";
        elseif ($ok === "unarchived") $success = "Vijest je vraćena iz arhive.";
        elseif ($ok === "approved") $success = "Vijest je odobrena.";
        elseif ($ok === "unapproved") $success = "Odobrenje je uklonjeno.";
        elseif ($ok === "deleted") $success = "Vijest je obrisana.";
    }

    $items = [];

    if ($role === "user") {
        $listStmt = $conn->prepare("
            SELECT n.*, u.username
            FROM news n
            JOIN users u ON u.id = n.author_id
            WHERE n.author_id = ?
            ORDER BY n.created_at DESC
        ");
        $listStmt->bind_param("i", $userId);
        $listStmt->execute();
        $res = $listStmt->get_result();

        while ($res && ($row = $res->fetch_assoc())) {
            $items[] = $row;
        }
        $listStmt->close();
    } else {
        $res = $conn->query("
            SELECT n.*, u.username
            FROM news n
            JOIN users u ON u.id = n.author_id
            ORDER BY n.created_at DESC
        ");

        while ($res && ($row = $res->fetch_assoc())) {
            $items[] = $row;
        }
    }
?>

<section class="content">
    <h1>Popis vijesti</h1>
    <h2>Uređivanje i odobravanje</h2>

    <p>
        <a href="index.php?menu=20">← Natrag na CMS</a> |
        <a href="index.php?menu=22">+ Nova vijest</a>
    </p>

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
                    <th>Naslov</th>
                    <th>Autor</th>
                    <th>Datum</th>
                    <th>Odobreno</th>
                    <th>Arhiva</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $n): ?>
                <tr>
                    <td><?php echo (int)$n["id"]; ?></td>
                    <td><?php echo htmlspecialchars($n["title"]); ?></td>
                    <td><?php echo htmlspecialchars($n["username"]); ?></td>
                    <td><?php echo htmlspecialchars($n["created_at"]); ?></td>
                    <td><?php echo ((int)$n["is_approved"] === 1) ? "DA" : "NE"; ?></td>
                    <td><?php echo ((int)$n["is_archived"] === 1) ? "DA" : "NE"; ?></td>
                    <td>
                        <a href="index.php?menu=24&id=<?php echo (int)$n["id"]; ?>">Uredi</a>

                        <?php if (in_array($role, ["editor","administrator"], true)): ?>
                            <form method="post" action="index.php?menu=23" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo (int)$n["id"]; ?>">
                                <input type="hidden" name="action" value="<?php echo ((int)$n["is_archived"]===1) ? "unarchive" : "archive"; ?>">
                                <button class="btn" type="submit" style="padding:6px 10px;">
                                    <?php echo ((int)$n["is_archived"]===1) ? "Vrati" : "Arhiviraj"; ?>
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($role === "administrator"): ?>
                            <form method="post" action="index.php?menu=23" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo (int)$n["id"]; ?>">
                                <input type="hidden" name="action" value="<?php echo ((int)$n["is_approved"]===1) ? "unapprove" : "approve"; ?>">
                                <button class="btn" type="submit" style="padding:6px 10px;">
                                    <?php echo ((int)$n["is_approved"]===1) ? "Makni odobrenje" : "Odobri"; ?>
                                </button>
                            </form>

                            <form method="post" action="index.php?menu=23" style="display:inline;" onsubmit="return confirm('Obrisati vijest?');">
                                <input type="hidden" name="id" value="<?php echo (int)$n["id"]; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button class="btn" type="submit" style="padding:6px 10px;">Obriši</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$items): ?>
                <tr><td colspan="7">Nema vijesti.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
