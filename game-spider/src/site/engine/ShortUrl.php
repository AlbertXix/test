<?php

/**
 * 短网址工具 — 生成短码、解析跳转目标
 */
class ShortUrl
{
    /** @var \PDO 数据库连接 */
    private $pdo;

    /** @var int 短码长度 */
    private $codeLength = 6;

    /** @var int 生成重试次数（防碰撞） */
    private $maxRetries = 5;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * 为目标 URL 生成唯一短码
     * @param string $targetUrl 原始长网址
     * @return string 短码（6 位十六进制）
     */
    public function generate(string $targetUrl): string
    {
        for ($i = 0; $i < $this->maxRetries; $i++) {
            $code = $this->randomCode();
            $stmt = $this->pdo->prepare('INSERT IGNORE INTO bo_short_url (short_code, target_url, created_time) VALUES (:code, :url, NOW())');
            $stmt->execute([':code' => $code, ':url' => $targetUrl]);
            if ($stmt->rowCount() > 0) {
                return $code;
            }
        }
        throw new \RuntimeException('Failed to generate unique short code after ' . $this->maxRetries . ' retries');
    }

    /**
     * 根据短码解析目标 URL
     * @param string $code 短码
     * @return string|null 目标 URL，不存在则返回 null
     */
    public function resolve(string $code): ?string
    {
        $stmt = $this->pdo->prepare('SELECT target_url FROM bo_short_url WHERE short_code = :code LIMIT 1');
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? $row['target_url'] : null;
    }

    /**
     * 根据目标 URL 查找已有短码
     * @param string $targetUrl 目标 URL
     * @return string|null 已存在的短码，不存在则返回 null
     */
    public function findByTargetUrl(string $targetUrl): ?string
    {
        $stmt = $this->pdo->prepare('SELECT short_code FROM bo_short_url WHERE target_url = :url LIMIT 1');
        $stmt->execute([':url' => $targetUrl]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? $row['short_code'] : null;
    }

    /**
     * 获取或创建短码：先查已存在，没有再生成
     * @param string $targetUrl 目标 URL
     * @return string 短码
     */
    public function getOrCreate(string $targetUrl): string
    {
        $existing = $this->findByTargetUrl($targetUrl);
        if ($existing !== null) {
            return $existing;
        }
        return $this->generate($targetUrl);
    }

    /** 生成随机十六进制短码 */
    private function randomCode(): string
    {
        return substr(bin2hex(random_bytes(3)), 0, $this->codeLength);
    }
}
