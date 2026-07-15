<?php

class BotDetector
{
    private $redis;
    private $ip;

    private $rateLimit = 60;
    private $rateWindow = 60;

    private static $searchEngines = [
        'Googlebot', 'Bingbot', 'Slurp', 'DuckDuckBot', 'Baiduspider',
        'YandexBot', 'Sogou', 'Exabot', 'facebot', 'ia_archiver',
    ];

    public function __construct()
    {
        $this->ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        try {
            $this->redis = new \Redis();
            $this->redis->connect('127.0.0.1', 6379);
        } catch (\Exception $e) {
            $this->redis = null;
        }
    }

    public function isSearchEngine(): bool
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        foreach (self::$searchEngines as $bot) {
            if (stripos($ua, $bot) !== false) {
                return true;
            }
        }
        return false;
    }

    public function isBlocked(): bool
    {
        if (!$this->redis) return false;
        return (bool) $this->redis->exists("blocked:{$this->ip}");
    }

    public function getFingerprintScore(): int
    {
        $score = 0;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (empty($ua)) $score += 30;
        if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')) $score += 15;
        if (empty($_SERVER['HTTP_ACCEPT'] ?? '')) $score += 10;

        if (preg_match('/curl|wget|python-requests|java|okhttp|scrapy|httpclient|php|perl|ruby|fetch\s/i', $ua)) {
            $score += 40;
        }

        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && !isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $score += 10;
        }

        return $score;
    }

    public function checkRate(): bool
    {
        if (!$this->redis) return false;
        $key = "ratelimit:{$this->ip}";
        $count = $this->redis->incr($key);
        if ($count === 1) {
            $this->redis->expire($key, $this->rateWindow);
        }
        return $count > $this->rateLimit;
    }

    public function hasChallengeCookie(): bool
    {
        $token = $_COOKIE['_ch'] ?? '';
        if (!$token || !$this->redis) return false;
        return (bool) $this->redis->get("challenge:{$token}");
    }

    public function issueChallenge(): never
    {
        $token = bin2hex(random_bytes(16));
        if ($this->redis) {
            $this->redis->setex("challenge:{$token}", 300, $this->ip);
        }

        $redirect = $_SERVER['REQUEST_URI'] ?? '/';
        http_response_code(403);
        ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>验证中...</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f5f5f7; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
.card { background: #fff; border-radius: 16px; padding: 40px; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,0.08); max-width: 400px; width: 90%; }
.spinner { width: 40px; height: 40px; border: 4px solid #e5e5e7; border-top-color: #009ba2; border-radius: 50%; animation: spin .8s linear infinite; margin: 0 auto 20px; }
@keyframes spin { to { transform: rotate(360deg); } }
p { color: #666; font-size: 14px; line-height: 1.6; }
</style>
</head>
<body>
<div class="card">
    <div class="spinner"></div>
    <p>验证浏览器环境，请稍候...</p>
</div>
<script>
(function() {
    var d = new Date();
    d.setTime(d.getTime() + 86400 * 1000);
    document.cookie = '_ch=<?= $token ?>; expires=' + d.toUTCString() + '; path=/';
    setTimeout(function() {
        location.href = '<?= htmlspecialchars($redirect, ENT_QUOTES) ?>';
    }, 1500);
})();
</script>
</body>
</html>
<?php
        exit;
    }

    public function markCrawler(): void
    {
        if (!$this->redis) return;
        $this->redis->setex("crawler:{$this->ip}", 3600, '1');
    }

    public function blockIP(int $duration = 86400): void
    {
        if (!$this->redis) return;
        $this->redis->setex("blocked:{$this->ip}", $duration, '1');
    }

    public function isCrawler(): bool
    {
        if ($this->isSearchEngine()) return false;
        if (!$this->redis) return false;
        return (bool) $this->redis->get("crawler:{$this->ip}");
    }

    public function isHuman(): bool
    {
        if ($this->isSearchEngine()) return true;
        $score = $this->getFingerprintScore();
        $rateExceeded = $this->checkRate();
        $hasChallenge = $this->hasChallengeCookie();

        if ($score >= 40 || $rateExceeded) {
            if (!$hasChallenge) {
                return false;
            }
        }

        if ($score >= 60 || $rateExceeded) {
            $this->markCrawler();
        }

        return true;
    }

    public function poisonText(string $text): string
    {
        $chars = mb_str_split($text);
        if (count($chars) <= 2) return $text;
        $first = array_shift($chars);
        $last = array_pop($chars);
        for ($i = count($chars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            $tmp = $chars[$i];
            $chars[$i] = $chars[$j];
            $chars[$j] = $tmp;
        }
        return $first . implode('', $chars) . $last;
    }
}
