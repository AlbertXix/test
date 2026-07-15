<section class="games-page">
    <form class="second-search" action="?page=search" method="get">
        <input type="hidden" name="page" value="search">
        <input type="text" name="q" placeholder="搜索游戏名称..." autocomplete="on">
    </form>
    <div class="tag-bar">
        <a href="?page=games" class="tag-btn <?= !$activeTagId ? 'active' : '' ?>">全部</a>
        <?php foreach ($tags as $t): ?>
        <a href="?page=games&tag_id=<?= $t['id'] ?>" class="tag-btn <?= $activeTagId === (int) $t['id'] ? 'active' : '' ?>"><?= htmlspecialchars($t['tag_name']) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="game-grid four-col">
        <?php foreach ($games as $g): ?>
        <a href="?page=detail&id=<?= $g['id'] ?>" class="game-card">
            <div class="game-cover" style="background-image: url('<?= ($g['cover_image'] ?: $g['cover_image_local']) ?: '/Public/up/nopic.jpg' ?>')"></div>
            <div class="game-info">
                <h3><?= htmlspecialchars_decode(mb_substr($g['title'], 0, 50), ENT_QUOTES) ?></h3>
                <div class="game-meta">
                    <span class="size"><?= intval($g['resource_size']) > 1024 ? round(intval($g['resource_size']) / 1024, 2) . ' GB' : intval($g['resource_size']) . ' MB' ?></span>
                    <span class="date"><?= date('Y-m-d', strtotime($g['created_time'])) ?? '' ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="pagination">
        <?php if ($pageNum > 1): ?>
        <a href="?page=games&p=<?= $pageNum - 1 ?>&tag_id=<?= $activeTagId ?>">上一页</a>
        <?php endif; ?>
        <span>第 <?= $pageNum ?> / <?= $maxPage ?> 页</span>
        <?php if ($pageNum < $maxPage): ?>
        <a href="?page=games&p=<?= $pageNum + 1 ?>&tag_id=<?= $activeTagId ?>">下一页</a>
        <?php endif; ?>
    </div>
</section>
