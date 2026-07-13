<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>游戏基地</title>
<link rel="stylesheet" href="style.css?v=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</head>
<body>

<header>
    <div class="header-inner">
        <a href="?page=home" class="logo">游戏基地</a>
        <nav>
            <a href="?page=home" class="<?= $page === 'home' ? 'active' : '' ?>">首页</a>
            <a href="?page=games" class="<?= $page === 'games' ? 'active' : '' ?>">电脑游戏</a>
            <a href="?page=rankings" class="<?= $page === 'rankings' ? 'active' : '' ?>">游戏排名</a>
        </nav>
    </div>
</header>

<main>
<?= $pageContent ?>
</main>

<footer>
    <p>&copy; 游戏基地</p>
</footer>

<div id="back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'})">↑</div>

<script>
window.addEventListener('scroll', function() {
    document.getElementById('back-to-top').classList.toggle('visible', window.scrollY > 300);
});
</script>
</body>
</html>
