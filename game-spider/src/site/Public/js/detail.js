// 初始化截图 Swiper
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

// 灯箱图片切换逻辑
(function() {
    var items = document.querySelectorAll('.screenshot-item');
    var lightbox = document.getElementById('lightbox');
    var lightboxImg = document.getElementById('lightboxImg');
    var prevBtn = document.getElementById('lightboxPrev');
    var nextBtn = document.getElementById('lightboxNext');
    var currentIndex = -1;

    function updateNav() {
        prevBtn.style.display = currentIndex > 0 ? '' : 'none';
        nextBtn.style.display = currentIndex < items.length - 1 ? '' : 'none';
    }

    function showImage(index) {
        if (index < 0 || index >= items.length) return;
        currentIndex = index;
        lightboxImg.src = items[index].dataset.src;
        updateNav();
    }

    items.forEach(function(item, idx) {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            showImage(idx);
            lightbox.style.display = 'flex';
        });
    });

    prevBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        showImage(currentIndex - 1);
    });

    nextBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        showImage(currentIndex + 1);
    });

    lightbox.addEventListener('click', function() {
        this.style.display = 'none';
    });

    document.addEventListener('keydown', function(e) {
        if (lightbox.style.display !== 'flex') return;
        if (e.key === 'ArrowLeft') showImage(currentIndex - 1);
        else if (e.key === 'ArrowRight') showImage(currentIndex + 1);
        else if (e.key === 'Escape') lightbox.style.display = 'none';
    });
})();

// 下载按钮 → AJAX 请求生成二维码
(function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var qrModal = document.getElementById('qrModal');
    var qrImage = document.getElementById('qrImage');

    document.querySelectorAll('.download-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var gameId = this.dataset.gameId;
            var type = this.dataset.type;
            qrImage.src = '';
            qrModal.style.display = 'flex';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '?api=getDownloadQRCode', true);
            xhr.setRequestHeader('X-CSRF-Token', csrfToken);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var resp = JSON.parse(xhr.responseText);
                    qrImage.src = resp.qr;
                }
            };
            xhr.send('game_id=' + gameId + '&type=' + encodeURIComponent(type));
        });
    });

    qrModal.addEventListener('click', function() {
        this.style.display = 'none';
    });
})();
