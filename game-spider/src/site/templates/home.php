<!-- Hero 区域：星空背景 + 雷达 + 搜索框 -->
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
            <input type="hidden" name="from" value="indexSearch">
            <input type="hidden" name="page" value="search">
            <input type="text" name="q" placeholder="搜索游戏名称..." autocomplete="off">
            <button type="submit">搜索</button>
        </form>
    </div>
</section>

<!-- 生成星星粒子 + 鼠标视差效果 -->
<script>
(function() {
    var hero = document.getElementById('hero');
    var stars = document.getElementById('stars');
    var heroW = hero.offsetWidth;
    var heroH = hero.offsetHeight;

    function generateStars() {
        stars.innerHTML = '';
        var count = 160;
        for (var i = 0; i < count; i++) {
            var el = document.createElement('i');
            el.className = 'star';
            var size = 1 + Math.random() * 2;
            el.style.width = size + 'px';
            el.style.height = size + 'px';
            el.style.left = Math.random() * 100 + '%';
            el.style.top = Math.random() * 100 + '%';
            var dur = 2 + Math.random() * 4;
            var delay = -Math.random() * 6;
            el.style.animation = 'twinkle ' + dur + 's ease-in-out ' + delay + 's infinite';
            if (size > 2) {
                el.classList.add('star-glow');
            }
            stars.appendChild(el);
        }
    }
    generateStars();

    var ticking = false;
    hero.addEventListener('mousemove', function(e) {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                var rect = hero.getBoundingClientRect();
                var x = (e.clientX - rect.left) / rect.width - 0.5;
                var y = (e.clientY - rect.top) / rect.height - 0.5;
                stars.style.transform = 'translate(' + (x * 30) + 'px, ' + (y * 20) + 'px)';
                ticking = false;
            });
            ticking = true;
        }
    });
    hero.addEventListener('mouseleave', function() {
        stars.style.transform = 'translate(0, 0)';
    });
})();
</script>

<!-- 焦点图轮播（Swiper） -->
<?php if (!empty($focusGames)): ?>
<div class="focus-slider">
    <div class="swiper focusSwiper">
        <div class="swiper-wrapper">
            <?php foreach ($focusGames as $fg): ?>
            <div class="swiper-slide">
                <a href="?page=detail&id=<?= $fg['id'] ?>" class="focus-slide-inner" style="background-image: url('<?= htmlspecialchars($fg['cover_image'] ?: $fg['cover_image_local'] ?: '/Public/up/nopic.jpg') ?>')">
                    <div class="focus-slide-title"><?= htmlspecialchars(mb_substr($fg['title'], 0, 50)) ?></div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
    </div>
</div>

<!-- Swiper 初始化配置 -->
<script>
new Swiper('.focusSwiper', {
    slidesPerView: 6,
    spaceBetween: 12,
    slidesOffsetBefore: 0,
    slidesOffsetAfter: 60,
    loop: true,
    autoplay: { delay: 4000, disableOnInteraction: false },
    navigation: { nextEl: '.focusSwiper .swiper-button-next', prevEl: '.focusSwiper .swiper-button-prev' },
    pagination: { el: '.focusSwiper .swiper-pagination', clickable: true },
    breakpoints: {
        0: { slidesPerView: 2, slidesOffsetBefore: 0, slidesOffsetAfter: 20 },
        500: { slidesPerView: 3, slidesOffsetBefore: 0, slidesOffsetAfter: 30 },
        800: { slidesPerView: 4, slidesOffsetBefore: 0, slidesOffsetAfter: 40 },
        1100: { slidesPerView: 6, slidesOffsetBefore: 0, slidesOffsetAfter: 60 }
    }
});
</script>
<?php endif; ?>

<!-- 分类游戏网格 -->
<div class="category-grid">
<?php foreach ($latestByTag as $group): ?>
<section class="category-section">
    <h2><span><?= htmlspecialchars($group['tag']) ?></span><a href="?page=games&tag_id=<?= $group['tag_id'] ?>" class="more-link">>></a></h2>
    <div class="game-grid">
        <?php foreach ($group['games'] as $g): ?>
        <a href="?page=detail&id=<?= $g['id'] ?>" class="game-card">
            <div class="game-cover" style="background-image: url('<?= htmlspecialchars_decode($g['cover_image'] ?: $g['cover_image_local'] ?: '/Public/up/nopic.jpg', ENT_QUOTES) ?>')"></div>
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
</section>
<?php endforeach; ?>
</div>
