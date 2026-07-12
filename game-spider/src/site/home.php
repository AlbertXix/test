<?php
$latestByTag = [];
foreach ($tags as $tag) {
    $stmt = $pdo->prepare('SELECT g.id, g.title, g.title_en, g.resource_size, g.cover_image, g.cover_image_local, g.release_date, g.description FROM bo_game g JOIN bo_game_tag gt ON gt.game_id = g.id WHERE gt.tag_id = :tag_id ORDER BY g.id DESC LIMIT 6');
    $stmt->execute([':tag_id' => $tag['id']]);
    $games = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    if (!empty($games)) {
        $latestByTag[] = ['tag' => $tag['tag_name'], 'games' => $games];
    }
}
?>

<section class="hero" id="hero">
    <div class="stars" id="stars"></div>
    <div class="shooting-star" id="shootingStar1"></div>
    <div class="shooting-star" id="shootingStar2"></div>
    <div class="radar-base">
        <div class="radar-dish"></div>
        <div class="radar-feed"></div>
        <div class="radar-line"></div>
        <div class="radar-glow"></div>
    </div>
    <div class="hero-content" id="heroContent">
        <p class="tagline">发现好游戏，畅玩无限</p>
        <form class="search-box" action="?page=search" method="get">
            <input type="hidden" name="page" value="search">
            <input type="text" name="q" placeholder="搜索游戏名称..." autocomplete="off">
            <button type="submit">搜索</button>
        </form>
    </div>
</section>

<script>
(function() {
    var hero = document.getElementById('hero');
    var stars = document.getElementById('stars');
    hero.addEventListener('mousemove', function(e) {
        var rect = hero.getBoundingClientRect();
        var x = (e.clientX - rect.left) / rect.width - 0.5;
        var y = (e.clientY - rect.top) / rect.height - 0.5;
        stars.style.transform = 'translate(' + (x * 6) + 'px, ' + (y * 4) + 'px)';
    });
    hero.addEventListener('mouseleave', function() {
        stars.style.transform = 'translate(0, 0)';
    });
})();
</script>

<div class="category-grid">
<?php foreach ($latestByTag as $group): ?>
<section class="category-section">
    <h2><?= htmlspecialchars($group['tag']) ?></h2>
    <div class="game-grid">
        <?php foreach ($group['games'] as $g): ?>
        <a href="?page=detail&id=<?= $g['id'] ?>" class="game-card">
            <div class="game-cover" style="background-image: url('<?= htmlspecialchars_decode($g['cover_image'] ?: $g['cover_image_local'] ?: '/Public/up/nopic.jpg', ENT_QUOTES) ?>')"></div>
            <div class="game-info">
                <h3><?= htmlspecialchars_decode(mb_substr($g['title'], 0, 30), ENT_QUOTES) ?></h3>
                <div class="game-meta">
                    <span class="size"><?= $g['resource_size'] ? round($g['resource_size'] / 1024, 1) . ' GB' : '' ?></span>
                    <span class="date"><?= $g['release_date'] ?? '' ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>
</div>
