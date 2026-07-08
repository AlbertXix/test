<?php

namespace GameSpider\Exporter;

use GameSpider\Util\SnowflakeIdGenerator;

class MysqlExporter
{
    public const UPLOAD_ROOT = '/var/upload/bocms';
    public const RELATIVE_ROOT = '/Public/up';

    private \PDO $pdo;
    private SnowflakeIdGenerator $idGenerator;

    public function __construct(\PDO $pdo, ?SnowflakeIdGenerator $idGenerator = null)
    {
        $this->pdo = $pdo;
        $this->idGenerator = $idGenerator ?? new SnowflakeIdGenerator();
    }

    public function exportBySite(array $data): int
    {
        $count = 0;
        foreach ($data as $item) {
            try {
                $this->insertGame($item);
                $count++;
            } catch (\Exception $e) {
                $errTitle = $item['title'] ?? '';
                echo "    Error inserting game '{$errTitle}': {$e->getMessage()}\n";
            }
        }
        return $count;
    }

    private function gameExists(string $title): bool
    {
        static $cache = [];

        if (array_key_exists($title, $cache)) {
            return $cache[$title];
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM bo_game WHERE title = :title');
        $stmt->execute([':title' => $title]);

        $cache[$title] = (int) $stmt->fetchColumn() > 0;
        return $cache[$title];
    }

    private function insertGame(array $item): void
    {
        $title = $item['title'] ?? '';
        if ($title === '' || $this->gameExists($title)) {
            if ($title !== '') {
                echo "    Skipping (duplicate): {$title}\n";
            }
            return;
        }

        $gameId = $this->idGenerator->generate();

        $stmt = $this->pdo->prepare('
            INSERT INTO bo_game (
                id, category_id, title, title_en, keywords, description, resource_size,
                system_platform, home_page, source_url, developer,
                release_date, serial,
                score, user_give_score, click, cover_image, cover_image_local, content,
                download_total, download_url, baidu_url, xunlei_url,
                quark_url, created_by, updated_by
            ) VALUES (
                :id, :category_id, :title, :title_en, :keywords, :description, :resource_size,
                :system_platform, :home_page, :source_url, :developer,
                :release_date, :serial,
                :score, :user_give_score, :click, :cover_image, :cover_image_local, :content,
                :download_total, :download_url, :baidu_url, :xunlei_url,
                :quark_url, :created_by, :updated_by
            )
        ');

        $coverImageLocal = '';
        $coverUrl = $item['coverImage'] ? trim(urldecode($item['coverImage'])) : '';
        if ($coverUrl !== '') {
            $coverUrl = $this->resolveImageUrl($coverUrl, $item['site'] ?? '');
            $local = $this->downloadImage($coverUrl, self::UPLOAD_ROOT . '/cover');
            if ($local !== null) {
                $coverImageLocal = $this->localToDbPath($local);
            }
        }

        $stmt->execute([
            ':id' => $gameId,
            ':category_id' => 1,
            ':title' => $item['title'] ?? '',
            ':title_en' => $item['titleEn'] ?? '',
            ':keywords' => $item['title'] ?? '',
            ':description' => $item['description'] ?? '',
            ':resource_size' => isset($item['gameSize']) ? (int) $item['gameSize'] : 0,
            ':system_platform' => $item['runtimeEnv'] ?? '',
            ':home_page' => '',
            ':source_url' => $item['url'] ?? '',
            ':developer' => $item['developer'] ?? '',
            ':release_date' => $this->normalizeDate($item['releaseDate'] ?? ''),
            ':serial' => $item['series'] ?? '',
            ':score' => 0,
            ':user_give_score' => 0,
            ':click' => 0,
            ':cover_image' => $coverUrl,
            ':cover_image_local' => $coverImageLocal,
            ':content' => $item['content'] ?? '',
            ':download_total' => 0,
            ':download_url' => '',
            ':baidu_url' => '',
            ':xunlei_url' => '',
            ':quark_url' => '',
            ':created_by' => $item['site'] ?? '',
            ':updated_by' => $item['site'] ?? '',
        ]);

        $this->insertGameTag($gameId, $item['gameType'] ?? '');
        $this->insertGameScreenshots($gameId, $item['screenshots'] ?? []);
    }

    private function insertGameTag(int $gameId, string $gameType): void
    {
        $gameType = str_replace(['，', '、'], ',', $gameType);
        $tags = array_map('trim', explode(',', $gameType));
        $tags = array_filter($tags, fn($t) => $t !== '');

        $inserted = false;

        foreach ($tags as $tagName) {
            $tagId = $this->lookupTagId($tagName);
            if ($tagId !== null) {
                $this->insertGameTagRow($gameId, $tagId);
                $inserted = true;
            }
        }

        if (!$inserted) {
            $tagId = $this->lookupTagId('其他游戏');
            if ($tagId !== null) {
                $this->insertGameTagRow($gameId, $tagId);
            }
        }
    }

    private function lookupTagId(string $tagName): ?int
    {
        static $cache = [];

        if (array_key_exists($tagName, $cache)) {
            return $cache[$tagName];
        }

        $stmt = $this->pdo->prepare('SELECT id FROM bo_tag WHERE tag_name = :tag_name OR tag_name LIKE :tag_name_like LIMIT 1');
        $stmt->execute([':tag_name' => $tagName, ':tag_name_like' => '%' . $tagName . '%']);
        $id = $stmt->fetchColumn();

        $cache[$tagName] = $id !== false ? (int) $id : null;
        return $cache[$tagName];
    }

    private function insertGameTagRow(int $gameId, int $tagId): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO bo_game_tag (game_id, tag_id) VALUES (:game_id, :tag_id)');
        $stmt->execute([':game_id' => $gameId, ':tag_id' => $tagId]);
    }

    private function insertGameScreenshots(int $gameId, array $screenshots): void
    {
        if (empty($screenshots)) {
            return;
        }

        $stmt = $this->pdo->prepare('INSERT INTO bo_game_screenshot (game_id, image_url, image_local) VALUES (:game_id, :image_url, :image_local)');

        foreach ($screenshots as $url) {
            if (is_string($url) && $url !== '') {
                $resolvedUrl = $this->resolveImageUrl($url, '');
                $local = $this->downloadImage($resolvedUrl, self::UPLOAD_ROOT . '/screenshot');
                $stmt->execute([
                    ':game_id' => $gameId,
                    ':image_url' => $url,
                    ':image_local' => $local !== null ? $this->localToDbPath($local) : '',
                ]);
            }
        }
    }

    private function downloadImage(string $url, string $destDir): ?string
    {
        $url = trim(urldecode($url));

        if (!is_dir($destDir)) {
            if (!mkdir($destDir, 0755, true)) {
                echo "    Error: failed to create directory {$destDir}\n";
                return null;
            }
        }

        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        if (!$ext || !preg_match('/^[a-zA-Z0-9]+$/', $ext)) {
            $ext = 'jpg';
        }

        $fileId = $this->idGenerator->generate();
        $destPath = rtrim($destDir, '/') . '/' . $fileId . '.' . $ext;

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            if ($this->doDownload($url, $destPath)) {
                return $destPath;
            }
            if ($attempt === 1) {
                echo "    Retrying: {$url}\n";
            }
        }

        echo "    Warning: failed to download {$url}\n";
        @unlink($destPath);
        return null;
    }

    private function doDownload(string $url, string $destPath): bool
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }

        $fp = fopen($destPath, 'wb');
        if (!$fp) {
            curl_close($ch);
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        return $httpCode === 200 && $error === '' && file_exists($destPath) && filesize($destPath) > 0;
    }

    private function resolveImageUrl(string $url, string $site): string
    {
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        $baseUrls = [
            'danjipai' => 'https://www.danjipai.com',
            'gxkgame' => 'https://gxkgame.com',
        ];

        $base = $baseUrls[$site] ?? '';
        if ($base === '') {
            return $url;
        }

        return rtrim($base, '/') . '/' . ltrim($url, '/');
    }

    private function localToDbPath(string $localPath): string
    {
        return str_replace(self::UPLOAD_ROOT, self::RELATIVE_ROOT, $localPath);
    }

    private function normalizeDate(string $date): ?string
    {
        $date = trim($date);
        if ($date === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        if (preg_match('/(\d{4})\s*年\s*(\d{1,2})\s*月\s*(\d{1,2})\s*日/', $date, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        $ts = strtotime($date);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return null;
    }
}
