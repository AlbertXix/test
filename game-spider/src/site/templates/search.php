<section class="search-page">
    <!-- 搜索表单（首页跳转来时不显示） -->
    <?php if (!$from): ?>
    <div class="search-header">
        <form class="search-box" action="?page=search" method="get">
            <input type="hidden" name="page" value="search">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="搜索游戏名称..." autofocus>
            <button type="submit">搜索</button>
        </form>
    </div>
    <?php endif; ?>
    <!-- 搜索结果 -->
    <?php if ($q !== ''): ?>
    <p class="search-count">找到 <strong><?= count($results) ?></strong> 个与 "<?= htmlspecialchars($q) ?>" 相关的游戏</p>

    <?php if (empty($results)): ?>
    <p class="search-empty">未找到相关游戏，请尝试其他关键词</p>
    <?php else: ?>
    <!-- 结果列表 -->
    <div class="game-grid four-col">
        <?php foreach ($results as $g): ?>
        <a href="?page=detail&id=<?= $g['id'] ?>" class="game-card">
            <div class="game-cover" style="background-image: url('<?= htmlspecialchars($g['cover_image'] ?: $g['cover_image_local'] ?: '/Public/up/nopic.jpg') ?>')"></div>
            <div class="game-info">
                <h3><?= htmlspecialchars(mb_substr($g['title'], 0, 28)) ?></h3>
                <div class="game-meta">
                    <span class="size"><?= intval($g['resource_size']) > 1024 ? round(intval($g['resource_size']) / 1024, 2) . ' GB' : intval($g['resource_size']) . ' MB' ?></span>
                    <span class="date"><?= date('Y-m-d', strtotime($g['created_time'])) ?? '' ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- 搜索结果分页 -->
    <div class="pagination">
        <?php if ($pageNum > 1): ?>
        <a href="?page=search&q=<?= urlencode($q) ?>&p=<?= $pageNum - 1 ?>">上一页</a>
        <?php endif; ?>
        <span>第 <?= $pageNum ?> / <?= $maxPage ?> 页</span>
        <?php if ($pageNum < $maxPage): ?>
        <a href="?page=search&q=<?= urlencode($q) ?>&p=<?= $pageNum + 1 ?>">下一页</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</section>
