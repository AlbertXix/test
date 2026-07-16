<?php

/**
 * 爬虫检测器 — 通过指纹评分、速率限制、JS Challenge、数据投毒等方式识别并拦截爬虫
 */
class BotDetector
{
    /** @var \Redis|null Redis 实例，连接失败时为 null */
    private $redis;
    /** @var string 客户端 IP 地址 */
    private $ip;

    /** @var int 速率限制：窗口内最大请求数 */
    private $rateLimit = 60;
    /** @var int 速率限制：时间窗口（秒） */
    private $rateWindow = 60;

    /** 构造函数：获取客户端 IP，连接 Redis */
    public function __construct()
    {
        $this->ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        try {
            $config = require __DIR__ . '/../../Config/redis.config.php';
            $this->redis = new \Redis();
            $this->redis->connect($config['host'], $config['port'], $config['timeout'] ?? 2.0);
            if (!empty($config['password'])) {
                $this->redis->auth($config['password']);
            }
            if (isset($config['db'])) {
                $this->redis->select($config['db']);
            }
        } catch (\Exception $e) {
            $this->redis = null;
        }
    }

    /** 检查当前 IP 是否已被封禁 */
    public function isBlocked(): bool
    {
        if (!$this->redis) return false;
        return (bool) $this->redis->exists("blocked:{$this->ip}");
    }

    /**
     * 指纹评分：根据请求头缺失情况、UA 特征等计算爬虫嫌疑分数
     * @return int 0~100+，越高越可疑
     */
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

    /** 速率检查：当前 IP 在窗口期内是否超限 */
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

    /** 是否已通过 JS Challenge 验证（Session 标记） */
    public function hasPassedChallenge(): bool
    {
        return !empty($_SESSION['challenge_passed']);
    }

    /** 验证 Challenge Token 是否有效（Redis 中存储的 IP 匹配） */
    public function validateChallengeToken(string $token): bool
    {
        if (!$token || !$this->redis) return false;
        $key = "challenge:{$token}";
        $stored = $this->redis->get($key);
        if ($stored === false) return false;
        $this->redis->del($key);
        return $stored === $this->ip;
    }

    /** 标记当前 Session 已通过 Challenge 验证 */
    public function markPassed(): void
    {
        $_SESSION['challenge_passed'] = true;
    }

    /** 下发 JS Challenge 页面：生成 token，渲染自动提交的表单 */
    public function issueChallenge(): never
    {
        $token = bin2hex(random_bytes(16));
        if ($this->redis) {
            $this->redis->setex("challenge:{$token}", 300, $this->ip);
        }

        $action = $_SERVER['REQUEST_URI'] ?? '/';
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
    <form id="challenge" action="<?= htmlspecialchars($action, ENT_QUOTES) ?>" method="POST">
        <input type="hidden" name="_ch_token" value="<?= $token ?>">
    </form>
</div>
<script>document.getElementById('challenge').submit();</script>
</body>
</html>
<?php
        exit;
    }

    /** 标记当前 IP 为爬虫（Redis，1 小时过期） */
    public function markCrawler(): void
    {
        if (!$this->redis) return;
        $this->redis->setex("crawler:{$this->ip}", 3600, '1');
    }

    /** 封禁当前 IP（默认 24 小时） */
    public function blockIP(int $duration = 86400): void
    {
        if (!$this->redis) return;
        $this->redis->setex("blocked:{$this->ip}", $duration, '1');
    }

    /** 当前 IP 是否已被标记为爬虫 */
    public function isCrawler(): bool
    {
        if (!$this->redis) return false;
        return (bool) $this->redis->get("crawler:{$this->ip}");
    }

    /**
     * 报告 SQL 注入行为：累计计数，>= 2 次则永久封禁
     * @param Logger $logger 日志记录器
     */
    public function reportSqlInjection(Logger $logger): void
    {
        if (!$this->redis) return;
        $key = "sqli:{$this->ip}";
        $count = $this->redis->incr($key);
        $logger->warn("SQL injection detected from {$this->ip} (count: {$count})");
        if ($count >= 2) {
            $this->redis->set("blocked:{$this->ip}", '1');
            $logger->error("IP {$this->ip} permanently blocked for SQL injection");
        }
    }

    /**
     * 综合判断当前请求是否来自真人（高指纹/超限则弹 Challenge）
     * @return bool true=放行，false=需要验证
     */
    public function isHuman(): bool
    {
        $score = $this->getFingerprintScore();
        $rateExceeded = $this->checkRate();
        $hasChallenge = $this->hasPassedChallenge();

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

    /**
     * 数据投毒：打乱文本中间字符顺序（首尾保留），污染爬虫抓取到的数据
     * @param string $text 原始文本
     * @return string 打乱后的文本
     */
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
