<?php

namespace GameSpider\Exporter;

use GameSpider\Util\SnowflakeIdGenerator;

class MysqlExporter
{
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
                score, user_give_score, click, cover_image, content,
                download_total, download_url, baidu_url, xunlei_url,
                quark_url, created_by, updated_by
            ) VALUES (
                :id, :category_id, :title, :title_en, :keywords, :description, :resource_size,
                :system_platform, :home_page, :source_url, :developer,
                :release_date, :serial,
                :score, :user_give_score, :click, :cover_image, :content,
                :download_total, :download_url, :baidu_url, :xunlei_url,
                :quark_url, :created_by, :updated_by
            )
        ');

        $stmt->execute([
            ':id' => $gameId,
            ':category_id' => 1,
            ':title' => $item['title'] ?? '',
            ':title_en' => $item['titleEn'] ?? '',
            ':keywords' => $item['gameType'] ?? '',
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
            ':cover_image' => $item['coverImage'] ?? '',
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

        $stmt = $this->pdo->prepare('SELECT id FROM bo_tag WHERE tag_name = :tag_name LIMIT 1');
        $stmt->execute([':tag_name' => $tagName]);
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

        $stmt = $this->pdo->prepare('INSERT INTO bo_game_screenshot (game_id, image_url) VALUES (:game_id, :image_url)');

        foreach ($screenshots as $url) {
            if (is_string($url) && $url !== '') {
                $stmt->execute([':game_id' => $gameId, ':image_url' => $url]);
            }
        }
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
