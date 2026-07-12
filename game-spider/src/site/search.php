<?php
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("SELECT DISTINCT g.id, g.title, g.resource_size, g.cover_image_local, g.release_date FROM bo_game g WHERE g.title LIKE :q ORDER BY g.id DESC LIMIT 60");
    $stmt->execute([':q' => $like]);
    $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
?>

<section class="search-page">
    <div class="search-header">
        <form class="search-box" action="?page=search" method="get">
            <input type="hidden" name="page" value="search">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="搜索游戏名称..." autofocus>
            <button type="submit">搜索</button>
        </form>
    </div>

    <?php if ($q !== ''): ?>
    <p class="search-count">找到 <strong><?= count($results) ?></strong> 个与 "<?= htmlspecialchars($q) ?>" 相关的游戏</p>

    <?php if (empty($results)): ?>
    <p class="search-empty">未找到相关游戏，请尝试其他关键词</p>
    <?php else: ?>
    <div class="game-grid four-col">
        <?php foreach ($results as $g): ?>
        <a href="?page=detail&id=<?= $g['id'] ?>" class="game-card">
            <div class="game-cover" style="background-image: url('<?= htmlspecialchars($g['cover_image_local'] ?: '/Public/up/default.jpg') ?>')"></div>
            <div class="game-info">
                <h3><?= htmlspecialchars(mb_substr($g['title'], 0, 28)) ?></h3>
                <div class="game-meta">
                    <span class="size"><?= $g['resource_size'] ? round($g['resource_size'] / 1024, 1) . ' GB' : '' ?></span>
                    <span class="date"><?= $g['release_date'] ?? '' ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</section>
