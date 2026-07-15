<section class="rankings-page">
    <h1>游戏排名</h1>
    <p class="subtitle">历年最佳游戏回顾</p>

    <?php foreach ($years as $group): ?>
    <div class="year-block">
        <h2><?= $group['year'] ?> 年度最佳</h2>
        <div class="game-grid four-col">
            <?php foreach ($group['games'] as $i => $g): ?>
            <a href="?page=detail&id=<?= $g['id'] ?>" class="game-card ranked">
                <span class="rank"><?= $i + 1 ?></span>
                <div class="game-cover" style="background-image: url('<?= htmlspecialchars($g['cover_image_local'] ?: '/Public/up/nopic.jpg') ?>')"></div>
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
    </div>
    <?php endforeach; ?>
</section>
