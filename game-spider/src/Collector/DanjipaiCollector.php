<?php

namespace GameSpider\Collector;

use GameSpider\Fetcher\PageFetcher;
use GameSpider\Extractor\ContentExtractor;

class DanjipaiCollector extends BaseCollector
{
    private const BASE_URL = 'https://www.danjipai.com';

    private const CATEGORIES = [
        'RPG' => '角色扮演',
        'ACT' => '动作游戏',
        'AVG' => '冒险游戏',
        'SLG' => '策略棋牌',
        'RTS' => '即时战略',
        'FTG' => '格斗游戏',
        'STG' => '射击游戏',
        'PZL' => '益智休闲',
        'SPG' => '体育竞技',
        'RCG' => '赛车竞速',
        'SIM' => '模拟经营',
        'MSC' => '音乐游戏',
        'tools' => '工具补丁',
        'Other' => '其他游戏',
    ];

    public function getName(): string
    {
        return 'danjipai';
    }

    public function getCategories(): array
    {
        return self::CATEGORIES;
    }

    public function getListUrl(string $category, int $page): string
    {
        if ($page === 1) {
            return self::BASE_URL . "/{$category}/";
        }
        return self::BASE_URL . "/{$category}/list_{$page}.html";
    }

    public function getTotalPages(string $listHtml): int
    {
        if (preg_match('/list_(\d+)\.html"[^>]*class="endpage"/', $listHtml, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/class="endpage"[^>]*href="[^"]*list_(\d+)/', $listHtml, $m)) {
            return (int) $m[1];
        }
        return 0;
    }

    public function extractDetailUrls(string $listHtml): array
    {
        $urls = [];

        preg_match_all(
            '/<a\s+class="(?:softBox|contentWrap)"\s+href="([^"]+)"[^>]*>/i',
            $listHtml,
            $matches
        );

        if (!empty($matches[1])) {
            foreach ($matches[1] as $url) {
                if (str_starts_with($url, 'http')) {
                    $urls[] = $url;
                } else {
                    $urls[] = self::BASE_URL . $url;
                }
            }
        }

        return array_values(array_unique($urls));
    }

    public function extractTitle(string $detailHtml): string
    {
        $title = $this->extractor->extractFirst($detailHtml, 'h1.title');
        if ($title) {
            $title = preg_replace('#\s*/\s*[^/]+\s*$#', '', $title);
        }
        return $this->wrapTitle(trim($title ?? ''));
    }

    public function extractContent(string $detailHtml): string
    {
        $html = $this->extractor->extractFirstHtml($detailHtml, 'article.articleDetailGroup');
        if ($html === null) {
            return '';
        }

        $html = preg_replace(
            '/<(?:h2|p)[^>]*>\s*中文名称[^<]*(?:<br\s*\/?>(?:[^<]*)?){5,}发行日期[^<]*<\/(?:h2|p)>\s*/i',
            '',
            $html,
            1
        );

        $html = preg_replace(
            '/<(?:h2|p)[^>]*>(?:[^<]*<br\s*\/?>\s*){3,}[^<]*<\/(?:h2|p)>\s*(?:<(?:h2|p)[^>]*>\s*<br\s*\/?>\s*<\/(?:h2|p)>\s*)*/i',
            '',
            $html,
            1
        );

        return $this->sanitizeContent($html);
    }

    protected function extractAdditionalData(string $detailHtml): array
    {
        return $this->extractMeta($detailHtml);
    }

    public function extractMeta(string $detailHtml): array
    {
        $meta = [];

        $description = $this->extractor->extractFirst($detailHtml, '.description .detail p');
        if ($description) {
            $meta['description'] = trim($description);
        }

        $inlineFields = [
            '游戏大小' => 'gameSize',
            '游戏语言' => 'gameLanguage',
            '运行环境' => 'runtimeEnv',
            '更新时间' => 'updatedTime',
        ];

        $crawler = new \Symfony\Component\DomCrawler\Crawler($detailHtml);
        $labels = $crawler->filter('.softLabelWrap .label');
        foreach ($labels as $label) {
            $span = $label->getElementsByTagName('span')->item(0);
            $strong = $label->getElementsByTagName('strong')->item(0);
            if ($span && $strong) {
                $key = rtrim(trim($span->textContent), ':');
                $val = trim($strong->textContent);
                if (isset($inlineFields[$key])) {
                    $meta[$inlineFields[$key]] = $val;
                }
            }
        }

        if (isset($meta['gameSize'])) {
            $meta['gameSize'] = $this->convertToMb($meta['gameSize']);
        }

        if (!isset($meta['titleEn'])) {
            $articleHtml = $this->extractor->extractFirstHtml($detailHtml, 'article.articleDetailGroup');
            if ($articleHtml && preg_match('/英文名称\s*[：:]\s*(.+?)(?:<br\s*\/?>|<\/(?:p|h2|div)>|$)/iu', $articleHtml, $m)) {
                $rawEn = trim(strip_tags($m[1]));
                $english = preg_replace('/[^a-zA-Z0-9\s\'\-:\.!,&+()]/', '', $rawEn);
                $english = trim(preg_replace('/\s+/', ' ', $english));
                if ($english !== '') {
                    $meta['titleEn'] = "《{$english}》";
                }
            }
        }

        if (!isset($meta['titleEn'])) {
            $rawTitle = $this->extractor->extractFirst($detailHtml, 'h1.title');
            if ($rawTitle && preg_match('#\s*/\s*(.+)\s*$#', $rawTitle, $m)) {
                $rawEn = trim($m[1]);
                $english = preg_replace('/[^a-zA-Z0-9\s\'\-:\.!,&+()]/', '', $rawEn);
                $english = trim(preg_replace('/\s+/', ' ', $english));
                if ($english !== '') {
                    $meta['titleEn'] = "《{$english}》";
                }
            }
        }

        $articleHtml = $this->extractor->extractFirstHtml($detailHtml, 'article.articleDetailGroup');
        if ($articleHtml && preg_match('/类型\s*:\s*(.+?)(?:<\/(?:p|h2|div)>|<br\s*\/?>|$)/iu', $articleHtml, $m)) {
            $meta['gameType'] = trim(strip_tags($m[1]));
        }

        if (!isset($meta['gameType']) && preg_match('/<div[^>]*id="game_area_description"[^>]*>(.*?)<\/div>\s*<\/div>\s*<\/div>/is', $detailHtml, $section)) {
            if (preg_match('/类型\s*:\s*(.+?)(?:<\/(?:p|h2|div)>|<br\s*\/?>|$)/iu', $section[1], $m)) {
                $meta['gameType'] = trim(strip_tags($m[1]));
            }
        }

        if (isset($meta['gameType'])) {
            $meta['gameType'] = preg_replace('/\s*,\s*/', ', ', $meta['gameType']);
        }

        $meta['screenshots'] = $this->extractScreenshots($detailHtml);

        $coverHtml = $this->extractor->extractFirstHtml($detailHtml, '.thumbBox img');
        if ($coverHtml && preg_match('/src="([^"]+)"/i', $coverHtml, $m)) {
            // $meta['coverImage'] = trim(urldecode($m[1]));
            $meta['coverImage'] = $this->fullImageUrl($m[1], self::BASE_URL);
        }

        if (preg_match('/<div[^>]*id="game_area_description"[^>]*>(.*?)<\/div>\s*<\/div>\s*<\/div>/is', $detailHtml, $section)) {
            $steamFields = [
                '开发商' => 'developer',
                '发行商' => 'publisher',
                '系列' => 'series',
                '发行日期' => 'releaseDate',
            ];
            foreach ($steamFields as $label => $key) {
                if (preg_match('/' . preg_quote($label, '/') . '\s*:\s*(.+?)(?:<\/(?:p|h2|div)>|<br\s*\/?>|$)/iu', $section[1], $m)) {
                    $meta[$key] = trim(strip_tags($m[1]));
                }
            }
        }

        return $meta;
    }

    protected function getItemDate(string $detailHtml): ?string
    {
        $meta = $this->extractMeta($detailHtml);
        $updatedTime = $meta['updatedTime'] ?? '';
        if ($updatedTime === '') {
            return null;
        }

        $updatedTime = preg_replace('/\s+\d{1,2}:\d{2}(:\d{2})?$/', '', trim($updatedTime));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $updatedTime)) {
            return $updatedTime;
        }

        return null;
    }

    private function extractScreenshots(string $detailHtml): array
    {
        $html = $this->extractor->extractFirstHtml($detailHtml, '.screenshot');
        if ($html && preg_match_all('/<img[^>]+src="([^"]+)"/i', $html, $m)) {
            // return $m[1];
            if (!empty($m[1])) {
                return array_map(function($item) {
                    return $this->fullImageUrl($item, self::BASE_URL);
                }, $m[1]);

                
            }
        }
        return [];
    }

    private function convertToMb(string $size): float
    {
        $size = trim($size);
        if (preg_match('/^([\d.]+)\s*GB$/i', $size, $m)) {
            return round((float) $m[1] * 1024, 1);
        }
        if (preg_match('/^([\d.]+)\s*MB$/i', $size, $m)) {
            return round((float) $m[1], 1);
        }
        if (preg_match('/^([\d.]+)\s*KB$/i', $size, $m)) {
            return round((float) $m[1] / 1024, 1);
        }
        return 0;
    }

    // protected function fullImageUrl(string $imageUrl, string baseUrl = ''): string
    // {
    //     $fullImageUrl = trim(urldecode($imageUrl));
    //     $ext = pathinfo(parse_url($fullImageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
    //     if (strtolower($ext) === 'j') {
    //         $fullImageUrl = str_ireplace($fullImageUrl, '.j', '.jpg');
    //     }
    //     if (stristr($fullImageUrl, '?t=')) {
    //         $fullImageUrl = substr($fullImageUrl, 0, stripos($fullImageUrl, '?t='));
    //     }
    //     if (strpos($fullImageUrl, '/') === 0) {
    //         $fullImageUrl = self::BASE_URL . $fullImageUrl;
    //     }

    //     return $fullImageUrl;
    // }
}
