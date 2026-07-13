<?php if (!$game): ?>
<p>游戏不存在</p>
<?php else: ?>

<section class="detail-page">
    <div class="detail-header">
        <div class="detail-cover" style="background-image: url('<?= htmlspecialchars($game['cover_image'] ?: $game['cover_image_local'] ?: '/Public/up/nopic.jpg') ?>')"></div>
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

    <?php if (!empty($screenshots)): ?>
    <div class="screenshot-gallery">
        <h2>游戏截图</h2>
        <div class="swiper screenshotSwiper">
            <div class="swiper-wrapper">
                <?php foreach ($screenshots as $s): ?>
                <div class="swiper-slide">
                    <div class="screenshot-item" data-src="<?= htmlspecialchars($s['image_url'] ?: $s['image_local']) ?>">
                        <img src="<?= htmlspecialchars($s['image_url'] ?: $s['image_local']) ?>" alt="游戏截图" loading="lazy">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <div class="lightbox" id="lightbox">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-img" id="lightboxImg">
    </div>

    <script>
    new Swiper('.screenshotSwiper', {
        slidesPerView: 1,
        spaceBetween: 16,
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        pagination: { el: '.swiper-pagination', clickable: true },
        breakpoints: {
            600: { slidesPerView: 2 },
            900: { slidesPerView: 3 }
        }
    });

    var lightbox = document.getElementById('lightbox');
    var lightboxImg = document.getElementById('lightboxImg');
    document.querySelectorAll('.screenshot-item').forEach(function(item) {
        item.addEventListener('click', function() {
            lightboxImg.src = this.dataset.src;
            lightbox.style.display = 'flex';
        });
    });
    lightbox.addEventListener('click', function() {
        this.style.display = 'none';
    });
    </script>
    <?php endif; ?>

    <?php if ($game['content']): ?>
    <div class="game-content">
        <div class="content-body"><?= $gameContent ?></div>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>
