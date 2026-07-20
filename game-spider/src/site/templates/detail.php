<?php if (!$game): ?>
<p>游戏不存在</p>
<?php else: ?>

<section class="detail-page">
    <!-- 游戏基本信息头 -->
    <div class="detail-header">
        <div class="detail-cover" style="background-image: url('<?= htmlspecialchars($game['cover_image_local'] ?: $game['cover_image'] ?: '/Public/up/nopic.jpg') ?>')"></div>
        <div class="detail-info">
            <h1><?= htmlspecialchars($game['title']) ?></h1>
            <div class="detail-meta">
                <?php if (!empty($gameTags)): ?>
                <p><strong>类型：</strong><?= htmlspecialchars(implode(' / ', $gameTags)) ?></p>
                <?php endif; ?>
                <?php if ($game['resource_size']): ?><p><strong>大小：</strong><?= intval($game['resource_size']) > 1024 ? round(intval($game['resource_size']) / 1024, 2) . ' GB' : intval($game['resource_size']) . ' MB' ?></p><?php endif; ?>
                <?php if ($game['release_date']): ?><p><strong>发行日期：</strong><?= $game['release_date'] ?></p><?php endif; ?>
                <?php if ($game['developer']): ?><p><strong>开发商：</strong><?= htmlspecialchars($game['developer']) ?></p><?php endif; ?>
                <?php if ($game['system_platform']): ?><p><strong>运行环境：</strong><?= htmlspecialchars($game['system_platform']) ?></p><?php endif; ?>
            </div>
            <?php if ($game['description']): ?>
            <div class="description"><?= htmlspecialchars($game['description']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 截图轮播 -->
    <?php if (!empty($screenshots)): ?>
    <div class="screenshot-gallery">
        <h2>游戏截图</h2>
        <div class="swiper screenshotSwiper">
            <div class="swiper-wrapper">
                <?php foreach ($screenshots as $s): ?>
                <div class="swiper-slide">
                    <div class="screenshot-item" data-src="<?= htmlspecialchars($s['image_local'] ?: $s['image_url']) ?>">
                        <img src="<?= htmlspecialchars($s['image_local'] ?: $s['image_url']) ?>" alt="游戏截图" loading="lazy">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <!-- 截图灯箱：点击大图 + 前后切换 + 键盘导航 -->
    <div class="lightbox" id="lightbox">
        <span class="lightbox-close">&times;</span>
        <span class="lightbox-nav lightbox-prev" id="lightboxPrev">&#10094;</span>
        <span class="lightbox-nav lightbox-next" id="lightboxNext">&#10095;</span>
        <img class="lightbox-img" id="lightboxImg">
    </div>


    <?php endif; ?>

    <!-- 游戏详细介绍内容 -->
    <?php if ($game['content']): ?>
    <div class="game-content">
        <div class="content-body"><?= $gameContent ?></div>
    </div>
    <?php endif; ?>

    <!-- 下载方式列表 -->
    <?php
    $downloads = [];
    if ($game['xunlei_url']) $downloads[] = ['key' => 'xunlei', 'label' => '迅雷云盘', 'url' => $game['xunlei_url']];
    if ($game['quark_url']) $downloads[] = ['key' => 'quark', 'label' => '夸克云盘', 'url' => $game['quark_url']];
    if ($game['baidu_url']) $downloads[] = ['key' => 'baidu', 'label' => '百度云盘', 'url' => $game['baidu_url']];
    if ($game['download_url']) $downloads[] = ['key' => 'direct', 'label' => '直接云盘', 'url' => $game['download_url']];
    ?>
    <?php if (!empty($downloads)): ?>
    <div class="download-wrapper">
        <h2>选择下载方式</h2>
        <div class="download-buttons">
            <?php foreach ($downloads as $d): ?>
            <button class="download-btn" data-game-id="<?= $game['id'] ?>" data-type="<?= $d['key'] ?>"><?= htmlspecialchars($d['label']) ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 二维码弹窗 -->
    <div class="qr-modal" id="qrModal">
        <div class="qr-modal-content">
            <span class="qr-modal-close">&times;</span>
            <h3>扫码下载</h3>
            <img id="qrImage" src="" alt="二维码">
        </div>
    </div>

    <?php endif; ?>
</section>
<?php endif; ?>
<script src="Public/js/detail<?= $isDev ? '' : '.min' ?>.js" defer></script>