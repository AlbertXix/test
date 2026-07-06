<?php

namespace GameSpider\Collector;

use GameSpider\Fetcher\PageFetcher;
use GameSpider\Extractor\ContentExtractor;

class GxkgameCollector extends BaseCollector
{
    private const API_BASE = 'https://gxkgame.com/api';

    private const CATEGORIES = [
        'dzmx' => '动作冒险',
        'dzyx' => '动作游戏',
        'xxyz' => '休闲益智',
        'mnjy' => '模拟经营',
        'jsby' => '角色扮演',
        'clyx' => '策略游戏',
        'ktka' => '卡通可爱',
        'mxjm' => '冒险解谜',
        'kbmx' => '恐怖冒险',
        'sjyx' => '射击游戏',
        'scmx' => '生存冒险',
        'jszl' => '即时战略',
        'gdyx' => '格斗游戏',
        'tyjj' => '体育竞技',
        'sj' => '手机游戏',
        'dmsj' => '弹幕射击',
        'scjj' => '赛车竞技',
        'yyyx' => '音乐游戏',
        'qt' => '其他',
    ];

    public function getName(): string
    {
        return 'gxkgame';
    }

    public function getCategories(): array
    {
        return self::CATEGORIES;
    }

    public function getListUrl(string $category, int $page): string
    {
        return self::API_BASE . '/game/list?' . http_build_query([
            'page' => $page,
            'category' => $category,
            'type' => 1,
        ]);
    }

    public function getTotalPages(string $listHtml): int
    {
        $data = json_decode($listHtml, true);
        return $data['data']['totalPages'] ?? 0;
    }

    public function extractDetailUrls(string $listHtml): array
    {
        $data = json_decode($listHtml, true);
        $items = $data['data']['list'] ?? [];
        $urls = [];

        foreach ($items as $item) {
            $id = $item['articleId'] ?? '';
            if ($id) {
                $urls[] = self::API_BASE . '/game/getDetailById?' . http_build_query(['id' => $id]);
            }
        }

        return $urls;
    }

    public function extractTitle(string $detailHtml): string
    {
        $data = json_decode($detailHtml, true);
        return $data['data']['title'] ?? '';
    }

    public function extractContent(string $detailHtml): string
    {
        $data = json_decode($detailHtml, true);
        $content = $data['data']['contents'] ?? $data['data']['content'] ?? '';
        return is_string($content) ? $this->sanitizeContent($content) : '';
    }

    public function scrape(): array
    {
        $results = [];

        foreach ($this->getCategories() as $category => $label) {
            echo "[{$this->getName()}] Starting category: {$label} ({$category})\n";

            $page = 1;
            $hasMore = true;

            while ($hasMore) {
                echo "  Fetching page {$page}...\n";

                try {
                    $listUrl = $this->getListUrl($category, $page);
                    $json = $this->fetcher->fetchJson($listUrl);

                    $items = $json['data']['list'] ?? [];
                    $totalPages = $json['data']['totalPages'] ?? 0;

                    if (empty($items)) {
                        $hasMore = false;
                        break;
                    }

                    foreach ($items as $item) {
                        $id = $item['articleId'] ?? '';
                        if (!$id) {
                            continue;
                        }

                        try {
                            echo "    Fetching detail: {$id}\n";
                            $detailUrl = self::API_BASE . '/game/getDetailById';
                            $detail = $this->fetcher->fetchJson($detailUrl, ['id' => $id]);

                            $title = $detail['data']['title'] ?? '';
                            $rawContent = $detail['data']['contents'] ?? $detail['data']['content'] ?? '';

                            if ($title) {
                                $results[] = [
                                    'site' => $this->getName(),
                                    'category' => $label,
                                    'url' => $detailUrl . '?' . http_build_query(['id' => $id]),
                                    'title' => $title,
                                    'content' => is_string($rawContent) ? $this->sanitizeContent($rawContent) : '',
                                ];
                            }
                        } catch (\Exception $e) {
                            echo "    Error fetching detail {$id}: {$e->getMessage()}\n";
                        }
                    }

                    if ($page >= $totalPages) {
                        $hasMore = false;
                    } else {
                        $page++;
                    }
                } catch (\Exception $e) {
                    echo "  Error on page {$page}: {$e->getMessage()}\n";
                    $hasMore = false;
                }
            }

            echo "[{$this->getName()}] Completed category: {$label} ({$category})\n";
        }

        return $results;
    }
}
