<?php
    $id = (int)($_GET["id"] ?? 0);
    if ($id <= 0) {
        header("Location: index.php?menu=2");
        exit;
    }

    $stmt = $conn->prepare("
        SELECT n.*, u.username
        FROM news n JOIN users u ON u.id=n.author_id
        WHERE n.id=? AND n.is_approved=1 AND n.is_archived=0
        LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $news = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$news) {
        echo '<section class="content"><h1>Vijest nije dostupna</h1><p><a href="index.php?menu=2">← Natrag</a></p></section>';
        return;
    }

    $imgs = [];
    $st = $conn->prepare("SELECT image_path, caption FROM news_images WHERE news_id=? ORDER BY id ASC");
    $st->bind_param("i", $id);
    $st->execute();
    $r = $st->get_result();
    while ($r && ($row=$r->fetch_assoc())) $imgs[] = $row;
    $st->close();
?>

<article class="article-page">
    <h1><?php echo htmlspecialchars($news["title"]); ?></h1>
    <h2>Autor: <?php echo htmlspecialchars($news["username"]); ?></h2>

    <p class="meta">
        Objavljeno:
        <time datetime="<?php echo htmlspecialchars(date("Y-m-d", strtotime($news["created_at"]))); ?>">
            <?php echo htmlspecialchars(date("d. m. Y.", strtotime($news["created_at"]))); ?>
        </time>
    </p>

    <?php if ($imgs): ?>
        <section class="article-gallery" aria-label="Galerija slika članka">
            <?php foreach ($imgs as $im): ?>
                <figure>
                    <img src="<?php echo htmlspecialchars($im["image_path"]); ?>" alt="">
                    <figcaption><?php echo htmlspecialchars($im["caption"] ?? ""); ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <section class="article-text">
        <p><?php echo nl2br(htmlspecialchars($news["content"])); ?></p>

        <p class="back-link">
            <a href="index.php?menu=2">← Natrag na novosti</a>
        </p>
    </section>
</article>
