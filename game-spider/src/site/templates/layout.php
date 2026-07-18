<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>游戏基地</title>
<meta name="keywords" content="<?= htmlspecialchars($meta_keywords ?? '') ?>">
<meta name="description" content="<?= htmlspecialchars($meta_description ?? '') ?>">
<link rel="stylesheet" href="style.css?v=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<meta name="csrf-token" content="<?= $csrf_token ?>">
</head>
<body class="page-<?= $page ?>">

<!-- 顶部导航栏 -->
<header>
    <div class="header-inner">
        <?php if ($page === 'detail'): ?>
        <span onclick="javascript:history.back()" class="back-link">← 返回</span>
        <?php endif; ?>
        <a href="?page=home" class="logo">游戏基地</a>
        <!-- 桌面端搜索框（首页不显示） -->
        <?php if ($page !== 'home'): ?>
        <form class="nav-search" action="?page=search" method="get">
            <input type="hidden" name="from" value="topSearch">
            <input type="hidden" name="page" value="search">
            <input type="text" name="q" placeholder="搜索游戏..." autocomplete="on">
        </form>
        <?php endif; ?>
        <nav>
            <a href="?page=home" class="<?= $page === 'home' ? 'active' : '' ?>">首页</a>
            <a href="?page=games" class="<?= $page === 'games' ? 'active' : '' ?>">游戏</a>
            <a href="?page=rankings" class="<?= $page === 'rankings' ? 'active' : '' ?>">排名</a>
        </nav>
    </div>
</header>

<!-- 主体内容 -->
<main>
<?= $pageContent ?>
</main>

<footer>
    <p>&copy; 游戏基地</p>
</footer>

<!-- 回到顶部按钮 -->
<div id="back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'})">⬆</div>

<script>
window.addEventListener('scroll', function() {
    document.getElementById('back-to-top').classList.toggle('visible', window.scrollY > 300);
});
</script>
</body>
</html>
