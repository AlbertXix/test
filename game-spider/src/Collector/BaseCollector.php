<?php

namespace GameSpider\Collector;

use GameSpider\Fetcher\PageFetcher;
use GameSpider\Extractor\ContentExtractor;

abstract class BaseCollector implements CollectorInterface
{
    protected PageFetcher $fetcher;
    protected ContentExtractor $extractor;

    public function __construct(PageFetcher $fetcher, ContentExtractor $extractor)
    {
        $this->fetcher = $fetcher;
        $this->extractor = $extractor;
    }

    protected function extractAdditionalData(string $detailHtml): array
    {
        return [];
    }

    protected function sanitizeContent(string $raw): string
    {
        $placeholders = [];
        $processed = preg_replace_callback('/<img[^>]+>/i', function ($m) use (&$placeholders) {
            $key = "\x00IMG" . count($placeholders) . "\x00";
            $placeholders[$key] = $m[0];
            return $key;
        }, $raw);

        $content = htmlspecialchars($processed, ENT_QUOTES);

        foreach ($placeholders as $key => $imgTag) {
            $content = str_replace($key, $imgTag, $content);
        }

        return $content;
    }

    public function scrape(): array
    {
        $results = [];

        foreach ($this->getCategories() as $category => $label) {
            echo "[{$this->getName()}] Starting category: {$label} ({$category})\n";

            $page = 1;
            $totalPages = null;

            while (true) {
                echo "  Fetching page {$page}...\n";

                try {
                    $listUrl = $this->getListUrl($category, $page);
                    $listHtml = $this->fetcher->fetch($listUrl);

                    if ($totalPages === null) {
                        $totalPages = $this->getTotalPages($listHtml);
                        if ($totalPages > 0) {
                            echo "  Total pages estimated: {$totalPages}\n";
                        }
                    }

                    $detailUrls = $this->extractDetailUrls($listHtml);

                    if (empty($detailUrls)) {
                        break;
                    }

                    foreach ($detailUrls as $url) {
                        try {
                            echo "    Fetching detail: {$url}\n";
                            $detailHtml = $this->fetcher->fetch($url);
                            $title = $this->extractTitle($detailHtml);
                            $content = $this->extractContent($detailHtml);

                            if ($title) {
                                $results[] = array_merge([
                                    'site' => $this->getName(),
                                    'category' => $label,
                                    'url' => $url,
                                    'title' => $title,
                                    'content' => $content,
                                ], $this->extractAdditionalData($detailHtml));
                            }
                        } catch (\Exception $e) {
                            echo "    Error fetching detail {$url}: {$e->getMessage()}\n";
                        }
                    }

                    $page++;
                    if ($totalPages !== null && $page > $totalPages) {
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
}
