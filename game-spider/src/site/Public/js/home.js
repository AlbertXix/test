// 生成星星粒子 + 鼠标视差效果
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

// 焦点图轮播（Swiper）
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
