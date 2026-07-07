<?php

namespace GameSpider\Collector;

use GameSpider\Fetcher\PageFetcher;
use GameSpider\Extractor\ContentExtractor;

abstract class BaseCollector implements CollectorInterface
{
    protected PageFetcher $fetcher;
    protected ContentExtractor $extractor;
    protected ?string $minDate = null;
    protected ?int $startPage = null;
    protected ?int $endPage = null;
    protected ?string $category = null;

    public function __construct(PageFetcher $fetcher, ContentExtractor $extractor)
    {
        $this->fetcher = $fetcher;
        $this->extractor = $extractor;
    }

    public function setMinDate(?string $date): void
    {
        $this->minDate = $date;
    }

    public function setPageRange(?int $startPage, ?int $endPage): void
    {
        $this->startPage = $startPage;
        $this->endPage = $endPage;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    protected function getCategoriesForScrape(): array
    {
        if ($this->category !== null) {
            $label = $this->getCategories()[$this->category] ?? null;
            if ($label === null) {
                echo "  Warning: unknown category '{$this->category}', skipping\n";
                return [];
            }
            return [$this->category => $label];
        }
        return $this->getCategories();
    }

    protected function getItemDate(string $detailHtml): ?string
    {
        return null;
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

        $processed = preg_replace_callback('/<video[^>]*>.*?<\/video>/is', function ($m) use (&$placeholders) {
            $key = "\x00VID" . count($placeholders) . "\x00";
            $placeholders[$key] = $m[0];
            return $key;
        }, $processed);

        $content = htmlspecialchars($processed, ENT_QUOTES);

        foreach ($placeholders as $key => $tag) {
            $content = str_replace($key, $tag, $content);
        }

        return $content;
    }

    public function scrape(): array
    {
        $results = [];

        foreach ($this->getCategoriesForScrape() as $category => $label) {
            echo "[{$this->getName()}] Starting category: {$label} ({$category})\n";

            $page = $this->startPage ?? 1;
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

                            if ($this->minDate !== null) {
                                $itemDate = $this->getItemDate($detailHtml);
                                if ($itemDate !== null && $itemDate < $this->minDate) {
                                    echo "    Skipping (date {$itemDate} < {$this->minDate}): {$title}\n";
                                    continue;
                                }
                            }

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
                    $maxPage = $this->endPage ?? $totalPages;
                    if ($maxPage !== null && $page > $maxPage) {
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
