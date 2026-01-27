<?php
$res = $conn->query("
    SELECT id, title, lead_image, content, created_at
    FROM news
    WHERE is_approved=1 AND is_archived=0
    ORDER BY created_at DESC
");
$items = [];
while ($res && ($row = $res->fetch_assoc())) $items[] = $row;

function make_excerpt($text, $limit = 170) {
    $t = trim(strip_tags($text ?? ""));
    if ($t === "") return "";
    if (function_exists("mb_strlen") && mb_strlen($t) > $limit) {
        return mb_substr($t, 0, $limit) . "…";
    }
    if (strlen($t) > $limit) {
        return substr($t, 0, $limit) . "…";
    }
    return $t;
}
?>

<section class="news-page">
    <h1>Novosti</h1>
    <h2>Kratki članci o penjanju, treningu i opremi</h2>

    <?php foreach ($items as $n): ?>
        <?php $excerpt = make_excerpt($n["content"], 170); ?>
        <article class="news-item">
            <a class="thumb" href="index.php?menu=6&id=<?php echo (int)$n["id"]; ?>">
                <img src="<?php echo htmlspecialchars($n["lead_image"] ?: "images/news-placeholder.jpg"); ?>" alt="">
            </a>

            <div class="news-text">
                <h3>
                    <a href="index.php?menu=6&id=<?php echo (int)$n["id"]; ?>">
                        <?php echo htmlspecialchars($n["title"]); ?>
                    </a>
                </h3>

                <?php if ($excerpt !== ""): ?>
                    <p><?php echo htmlspecialchars($excerpt); ?></p>
                <?php endif; ?>

                <time datetime="<?php echo htmlspecialchars(date("Y-m-d", strtotime($n["created_at"]))); ?>">
                    <?php echo htmlspecialchars(date("d. m. Y.", strtotime($n["created_at"]))); ?>
                </time>

                <a class="more" href="index.php?menu=6&id=<?php echo (int)$n["id"]; ?>">Više …</a>
            </div>
        </article>
    <?php endforeach; ?>

    <?php if (!$items): ?>
        <p>Nema odobrenih vijesti.</p>
    <?php endif; ?>
</section>
