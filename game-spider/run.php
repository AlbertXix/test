<?php

require_once __DIR__ . '/vendor/autoload.php';

use GameSpider\Container\Container;
use GameSpider\Selector\CssSelector;
use GameSpider\Selector\XPathSelector;
use GameSpider\Fetcher\PageFetcher;
use GameSpider\Extractor\ContentExtractor;
use GameSpider\Collector\DanjipaiCollector;
use GameSpider\Collector\GxkgameCollector;
use GameSpider\Exporter\JsonExporter;

echo "========================================\n";
echo "  Game Spider - Web Scraper\n";
echo "========================================\n\n";

$container = new Container();

$container->singleton(PageFetcher::class, fn() => new PageFetcher());
$container->singleton(CssSelector::class, fn() => new CssSelector());
$container->singleton(XPathSelector::class, fn() => new XPathSelector());

$container->singleton('danjipai', function ($c) {
    return new DanjipaiCollector(
        $c->get(PageFetcher::class),
        new ContentExtractor($c->get(CssSelector::class))
    );
});

$container->singleton('gxkgame', function ($c) {
    return new GxkgameCollector(
        $c->get(PageFetcher::class),
        new ContentExtractor($c->get(CssSelector::class))
    );
});

$mode = $argv[1] ?? 'help';

if ($mode === 'test') {
    $site = $argv[2] ?? '';
    $category = $argv[3] ?? '';

    if (!$site || !$category || !$container->has($site)) {
        echo "Usage: php run.php test <site> <category>\n";
        echo "Sites: danjipai, gxkgame\n";
        if ($site && $container->has($site)) {
            echo "\nCategories for {$site}:\n";
            foreach ($container->get($site)->getCategories() as $key => $label) {
                echo "  {$key} => {$label}\n";
            }
        }
        exit(1);
    }

    $collector = $container->get($site);

    if ($collector instanceof DanjipaiCollector) {
        $url = $collector->getListUrl($category, 1);
        echo "Testing {$site} - category: {$category}\n";
        echo "Fetching list: {$url}\n";

        $html = $container->get(PageFetcher::class)->fetch($url);
        $urls = $collector->extractDetailUrls($html);
        $totalPages = $collector->getTotalPages($html);
        echo "Found " . count($urls) . " detail URLs on page 1\n";
        if ($totalPages > 0) echo "Total pages: {$totalPages}\n";

        foreach (array_slice($urls, 0, 3) as $detailUrl) {
            echo "\n  Fetching detail: {$detailUrl}\n";
            $detailHtml = $container->get(PageFetcher::class)->fetch($detailUrl);
            $title = $collector->extractTitle($detailHtml);
            $content = $collector->extractContent($detailHtml);
            echo "  sourceUrl: {$detailUrl}\n";
            echo "  Title: {$title}\n";
            echo "  Content: " . mb_substr(strip_tags($content), 0, 150) . "...\n";
            if (method_exists($collector, 'extractMeta')) {
                $meta = $collector->extractMeta($detailHtml);
                foreach ($meta as $k => $v) {
                    echo "  {$k}: {$v}\n";
                }
            }
        }
    } elseif ($collector instanceof GxkgameCollector) {
        $url = $collector->getListUrl($category, 1);
        echo "Testing {$site} - category: {$category}\n";
        echo "Fetching list: {$url}\n";

        $json = $container->get(PageFetcher::class)->fetchJson($url);
        $items = $json['data']['list'] ?? [];
        $totalPages = $json['data']['totalPages'] ?? 0;
        echo "Found " . count($items) . " items on page 1\n";
        if ($totalPages > 0) echo "Total pages: {$totalPages}\n";

        foreach (array_slice($items, 0, 3) as $item) {
            $id = $item['articleId'] ?? '';
            if (!$id) continue;

            $detailApiUrl = 'https://gxkgame.com/api/game/getDetailById?' . http_build_query(['id' => $id]);
            echo "\n  Fetching detail ID: {$id}\n";
            $detailJson = $container->get(PageFetcher::class)->fetch($detailApiUrl);
            $title = $collector->extractTitle($detailJson);
            $content = $collector->extractContent($detailJson);
            echo "  sourceUrl: {$detailApiUrl}\n";
            echo "  Title: {$title}\n";
            echo "  Content: " . mb_substr(strip_tags($content), 0, 150) . "...\n";
        }
    }
} elseif ($mode === 'scrape') {
    $site = $argv[2] ?? 'all';
    $outputDir = $argv[3] ?? __DIR__ . '/output';

    $sites = $site === 'all' ? ['danjipai', 'gxkgame'] : [$site];

    foreach ($sites as $s) {
        if (!$container->has($s)) {
            echo "Unknown site: {$s}\n";
            continue;
        }

        echo "\n--- Scraping {$s} ---\n";
        $collector = $container->get($s);
        $startTime = microtime(true);
        $results = $collector->scrape();
        $elapsed = round(microtime(true) - $startTime, 2);
        echo "\n--- {$s} complete: " . count($results) . " items in {$elapsed}s ---\n";

        $exporter = new JsonExporter();
        $files = $exporter->exportBySite($results, $outputDir);
        foreach ($files as $f) {
            echo "  Exported: {$f}\n";
        }
    }
} else {
    echo "Usage:\n";
    echo "  php run.php test <site> <category>    Test extraction for one category\n";
    echo "  php run.php scrape [site] [outdir]    Full scrape (site: danjipai|gxkgame|all)\n";
    echo "\nExamples:\n";
    echo "  php run.php test danjipai RPG\n";
    echo "  php run.php test gxkgame jsby\n";
    echo "  php run.php scrape all output/\n";
    echo "  php run.php scrape danjipai output/\n";
}
