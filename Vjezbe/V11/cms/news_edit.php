<?php
require_once "includes/guard.php";
require_approved();
require_once "includes/upload.php";

$role = $_SESSION["role"] ?? "user";
$userId = (int)$_SESSION["user_id"];
$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: index.php?menu=23");
    exit;
}

if ($role === "user") {
    $stmt = $conn->prepare("SELECT * FROM news WHERE id=? AND author_id=? LIMIT 1");
    $stmt->bind_param("ii", $id, $userId);
} else {
    $stmt = $conn->prepare("SELECT * FROM news WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $id);
}
$stmt->execute();
$res = $stmt->get_result();
$news = $res->fetch_assoc();
$stmt->close();

if (!$news) {
    header("Location: index.php?menu=23");
    exit;
}

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");

    if ($title === "") $errors[] = "Naslov je obavezan.";
    if ($content === "") $errors[] = "Tekst je obavezan.";

    $leadPath = $news["lead_image"];
    if (isset($_FILES["lead_image"]) && $_FILES["lead_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $tmp = save_uploaded_image($_FILES["lead_image"]);
        if ($tmp === null) $errors[] = "Glavna slika mora biti JPG/PNG/WEBP.";
        else $leadPath = $tmp;
    }

    if (!$errors) {
        $stmt = $conn->prepare("UPDATE news SET title=?, content=?, lead_image=? WHERE id=?");
        $stmt->bind_param("sssi", $title, $content, $leadPath, $id);
        $stmt->execute();
        $stmt->close();

        if (isset($_FILES["gallery_images"]) && is_array($_FILES["gallery_images"]["name"])) {
            $count = count($_FILES["gallery_images"]["name"]);
            for ($i=0; $i<$count; $i++) {
                if ($_FILES["gallery_images"]["error"][$i] === UPLOAD_ERR_NO_FILE) continue;
                $file = [
                    "name" => $_FILES["gallery_images"]["name"][$i],
                    "tmp_name" => $_FILES["gallery_images"]["tmp_name"][$i],
                    "error" => $_FILES["gallery_images"]["error"][$i],
                ];
                $fileFull = [
                    "name" => $_FILES["gallery_images"]["name"][$i],
                    "type" => $_FILES["gallery_images"]["type"][$i],
                    "tmp_name" => $_FILES["gallery_images"]["tmp_name"][$i],
                    "error" => $_FILES["gallery_images"]["error"][$i],
                    "size" => $_FILES["gallery_images"]["size"][$i],
                ];
                $path = save_uploaded_image($fileFull);
                if ($path) {
                    $cap = trim($_POST["caption"][$i] ?? "");
                    $st2 = $conn->prepare("INSERT INTO news_images (news_id, image_path, caption) VALUES (?, ?, ?)");
                    $st2->bind_param("iss", $id, $path, $cap);
                    $st2->execute();
                    $st2->close();
                }
            }
        }

        $success = "Vijest je spremljena.";
        $stmt = $conn->prepare("SELECT * FROM news WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $news = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

$imgs = [];
$st = $conn->prepare("SELECT id, image_path, caption FROM news_images WHERE news_id=? ORDER BY id ASC");
$st->bind_param("i", $id);
$st->execute();
$r2 = $st->get_result();
while ($r2 && ($row=$r2->fetch_assoc())) $imgs[] = $row;
$st->close();

if (isset($_GET["delimg"])) {
    $imgId = (int)$_GET["delimg"];
    if (in_array($role, ["editor","administrator"], true) || ($role==="user")) {
        $st = $conn->prepare("DELETE FROM news_images WHERE id=? AND news_id=?");
        $st->bind_param("ii", $imgId, $id);
        $st->execute();
        $st->close();
    }
    header("Location: index.php?menu=24&id=".$id);
    exit;
}
?>

<section class="content">
    <h1>Uredi vijest</h1>
    <h2><?php echo htmlspecialchars($news["title"]); ?></h2>

    <p><a href="index.php?menu=23">← Natrag na popis</a></p>

    <?php if ($success): ?><div class="form-success"><p><?php echo htmlspecialchars($success); ?></p></div><?php endif; ?>
    <?php if ($errors): ?><div class="form-error"><ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <form class="contact-form" method="post" action="index.php?menu=24&id=<?php echo $id; ?>" enctype="multipart/form-data">
        <div class="form-row">
            <label for="title">Naslov</label>
            <input id="title" name="title" type="text" value="<?php echo htmlspecialchars($news["title"]); ?>" required>
        </div>

        <div class="form-row">
            <label for="lead_image">Promijeni glavnu sliku (opcionalno)</label>
            <input id="lead_image" name="lead_image" type="file" accept="image/*">
            <?php if (!empty($news["lead_image"])): ?>
                <p style="margin:8px 0 0;"><img src="<?php echo htmlspecialchars($news["lead_image"]); ?>" alt="" style="max-width:240px;border-radius:6px;"></p>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <label for="content">Tekst</label>
            <textarea id="content" name="content" rows="8" required><?php echo htmlspecialchars($news["content"]); ?></textarea>
        </div>

        <div class="form-row">
            <label for="gallery_images">Dodaj slike u galeriju (opcionalno)</label>
            <input id="gallery_images" name="gallery_images[]" type="file" accept="image/*" multiple>
        </div>

        <button class="btn" type="submit">Spremi</button>
    </form>

    <h3>Galerija slika</h3>
    <?php if (!$imgs): ?>
        <p>Nema slika.</p>
    <?php else: ?>
        <div class="article-gallery">
            <?php foreach ($imgs as $im): ?>
                <figure>
                    <img src="<?php echo htmlspecialchars($im["image_path"]); ?>" alt="">
                    <figcaption>
                        <?php echo htmlspecialchars($im["caption"] ?? ""); ?>
                        <br>
                        <a href="index.php?menu=24&id=<?php echo $id; ?>&delimg=<?php echo (int)$im["id"]; ?>">Obriši sliku</a>
                    </figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
