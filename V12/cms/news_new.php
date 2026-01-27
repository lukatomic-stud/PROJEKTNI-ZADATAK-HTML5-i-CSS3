<?php
    require_once "includes/guard.php";
    require_approved();
    require_once "includes/upload.php";

    $role = $_SESSION["role"] ?? "user";

    $errors = [];
    $success = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $title = trim($_POST["title"] ?? "");
        $content = trim($_POST["content"] ?? "");

        if ($title === "") $errors[] = "Naslov je obavezan.";
        if ($content === "") $errors[] = "Tekst je obavezan.";

        $leadPath = null;
        if (isset($_FILES["lead_image"]) && $_FILES["lead_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $leadPath = save_uploaded_image($_FILES["lead_image"]);
            if ($leadPath === null) $errors[] = "Glavna slika mora biti JPG/PNG/WEBP.";
        }

        if (!$errors) {
            $isApproved = 0;

            $stmt = $conn->prepare("INSERT INTO news (author_id, title, lead_image, content, is_approved) VALUES (?, ?, ?, ?, ?)");
            $authorId = (int)$_SESSION["user_id"];
            $stmt->bind_param("isssi", $authorId, $title, $leadPath, $content, $isApproved);

            if ($stmt->execute()) {
                $newsId = $stmt->insert_id;

                if (isset($_FILES["gallery_images"]) && is_array($_FILES["gallery_images"]["name"])) {
                    $count = count($_FILES["gallery_images"]["name"]);
                    for ($i=0; $i<$count; $i++) {
                        if ($_FILES["gallery_images"]["error"][$i] === UPLOAD_ERR_NO_FILE) continue;
                        $file = [
                            "name" => $_FILES["gallery_images"]["name"][$i],
                            "type" => $_FILES["gallery_images"]["type"][$i],
                            "tmp_name" => $_FILES["gallery_images"]["tmp_name"][$i],
                            "error" => $_FILES["gallery_images"]["error"][$i],
                            "size" => $_FILES["gallery_images"]["size"][$i],
                        ];
                        $path = save_uploaded_image($file);
                        if ($path) {
                            $cap = trim($_POST["caption"][$i] ?? "");
                            $st2 = $conn->prepare("INSERT INTO news_images (news_id, image_path, caption) VALUES (?, ?, ?)");
                            $st2->bind_param("iss", $newsId, $path, $cap);
                            $st2->execute();
                            $st2->close();
                        }
                    }
                }

                $success = "Vijest je spremljena (čeka odobrenje administratora).";
            } else {
                $errors[] = "Greška pri spremanju vijesti.";
            }
            $stmt->close();
        }
    }
?>

<section class="content">
    <h1>Unos vijesti</h1>
    <h2>Dodaj novu vijest</h2>

    <p><a href="index.php?menu=20">← Natrag na CMS</a></p>

    <?php if ($success): ?>
        <div class="form-success"><p><?php echo htmlspecialchars($success); ?></p></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="form-error"><ul>
            <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <form class="contact-form" method="post" action="index.php?menu=22" enctype="multipart/form-data">
        <div class="form-row">
            <label for="title">Naslov</label>
            <input id="title" name="title" type="text" required>
        </div>

        <div class="form-row">
            <label for="lead_image">Glavna slika (thumbnail)</label>
            <input id="lead_image" name="lead_image" type="file" accept="image/*">
        </div>

        <div class="form-row">
            <label for="content">Tekst</label>
            <textarea id="content" name="content" rows="7" required></textarea>
        </div>

        <div class="form-row">
            <label for="gallery_images">Galerija slika (može više)</label>
            <input id="gallery_images" name="gallery_images[]" type="file" accept="image/*" multiple>
        </div>

        <button class="btn" type="submit">Spremi</button>
    </form>
</section>
