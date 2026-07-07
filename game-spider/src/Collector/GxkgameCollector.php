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

        foreach ($this->getCategoriesForScrape() as $category => $label) {
            echo "[{$this->getName()}] Starting category: {$label} ({$category})\n";

            $page = $this->startPage ?? 1;

            while (true) {
                echo "  Fetching page {$page}...\n";

                try {
                    $listUrl = $this->getListUrl($category, $page);
                    $json = $this->fetcher->fetchJson($listUrl);

                    $items = $json['data']['list'] ?? [];
                    $totalPages = $json['data']['totalPages'] ?? 0;

                    if (empty($items)) {
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

                            $screenshots = $detail['data']['screenshots'] ?? $detail['data']['images'] ?? [];
                            if (empty($screenshots) && is_string($rawContent)) {
                                $screenshots = $this->extractScreenshots($rawContent);
                            }

                            if ($this->minDate !== null) {
                                $publishDate = $detail['data']['publishDate'] ?? '';
                                $itemDate = $this->normalizeDate($publishDate);
                                if ($itemDate !== null && $itemDate < $this->minDate) {
                                    echo "    Skipping (date {$itemDate} < {$this->minDate}): {$title}\n";
                                    continue;
                                }
                            }

                            if ($title) {
                                $results[] = [
                                    'site' => $this->getName(),
                                    'category' => $label,
                                    'url' => $detailUrl . '?' . http_build_query(['id' => $id]),
                                    'title' => $title,
                                    'content' => is_string($rawContent) ? $this->sanitizeContent($rawContent) : '',
                                    'coverImage' => $detail['data']['imglink'] ?? '',
                                    'screenshots' => $screenshots,
                                    'releaseDate' => $detail['data']['publishDate'] ?? '',
                                    'developer' => $detail['data']['developer'] ?? '',
                                    'series' => $detail['data']['series'] ?? '',
                                    'titleEn' => $detail['data']['titleEn'] ?? '',
                                ];
                            }
                        } catch (\Exception $e) {
                            echo "    Error fetching detail {$id}: {$e->getMessage()}\n";
                        }
                    }

                    $page++;
                    $maxPage = $this->endPage ?? $totalPages;
                    if ($maxPage > 0 && $page > $maxPage) {
                        break;
                    }
                } catch (\Exception $e) {
                    echo "  Error on page {$page}: {$e->getMessage()}\n";
                    break;
                }
            }

            echo "[{$this->getName()}] Completed category: {$label} ({$category})\n";
        }

        return $results;
    }

    protected function getItemDate(string $detailHtml): ?string
    {
        $data = json_decode($detailHtml, true);
        $publishDate = $data['data']['publishDate'] ?? '';
        return $this->normalizeDate($publishDate);
    }

    private function normalizeDate(string $date): ?string
    {
        if ($date === '') {
            return null;
        }

        $date = preg_replace('/\s+\d{1,2}:\d{2}(:\d{2})?$/', '', trim($date));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        return null;
    }

    public function extractScreenshots(string $html): array
    {
        $screenshots = [];
        if (preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $html, $imgs)) {
            $crawler = new \Symfony\Component\DomCrawler\Crawler($html);
            $nodes = $crawler->filter('.ql-align-center img');
            $found = [];
            foreach ($nodes as $node) {
                if ($node instanceof \DOMElement) {
                    $src = $node->getAttribute('src');
                    if ($src) {
                        $found[] = $src;
                    }
                }
            }
            $screenshots = $found;
        }
        return $screenshots;
    }
}
