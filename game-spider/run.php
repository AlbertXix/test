<?php

require_once __DIR__ . '/vendor/autoload.php';

error_reporting(E_ALL);
date_default_timezone_set('PRC');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\ErrorHandler;

$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);
$logger = new Logger('spider');
$logger->pushHandler(new StreamHandler($logDir . '/spider.log', Logger::DEBUG));
ErrorHandler::register($logger);

use GameSpider\Container\Container;
use GameSpider\Selector\CssSelector;
use GameSpider\Selector\XPathSelector;
use GameSpider\Fetcher\PageFetcher;
use GameSpider\Extractor\ContentExtractor;
use GameSpider\Collector\DanjipaiCollector;
use GameSpider\Collector\GxkgameCollector;
use GameSpider\Exporter\JsonExporter;
use GameSpider\Exporter\MysqlExporter;

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
                    echo "  {$k}: " . (is_array($v) ? '[' . implode(', ', $v) . ']' : $v) . "\n";
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

            $data = json_decode($detailJson, true);
            $rawContent = $data['data']['contents'] ?? $data['data']['content'] ?? '';
            $screenshots = is_string($rawContent) ? $collector->extractScreenshots($rawContent) : [];
            $coverImage = $data['data']['imglink'] ?? '';
            $releaseDate = $data['data']['publishDate'] ?? '';
            $developer = $data['data']['developer'] ?? '';
            $series = $data['data']['series'] ?? '';

            echo "  sourceUrl: {$detailApiUrl}\n";
            echo "  Title: {$title}\n";
            echo "  Content: " . mb_substr(strip_tags($content), 0, 150) . "...\n";
            echo "  CoverImage: {$coverImage}\n";
            echo "  ReleaseDate: {$releaseDate}\n";
            echo "  Developer: {$developer}\n";
            echo "  Series: {$series}\n";
            echo "  Screenshots: " . (empty($screenshots) ? '(none)' : '[' . implode(', ', $screenshots) . ']') . "\n";
        }
    }
} elseif ($mode === 'scrape') {
    $site = $argv[2] ?? 'all';
    $outputTarget = $argv[3] ?? 'output';

    $minDate = null;
    $startPage = null;
    $endPage = null;
    $category = null;

    for ($i = 2; $i < count($argv); $i++) {
        if ($argv[$i] === '--start-page' && isset($argv[$i + 1])) {
            $startPage = (int) $argv[$i + 1];
            $i++;
        } elseif ($argv[$i] === '--end-page' && isset($argv[$i + 1])) {
            $endPage = (int) $argv[$i + 1];
            $i++;
        } elseif ($argv[$i] === '--category' && isset($argv[$i + 1])) {
            $category = $argv[$i + 1];
            $i++;
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $argv[$i])) {
            $minDate = $argv[$i];
        }
    }

    if ($minDate !== null) {
        echo "  Filter: only items >= {$minDate}\n";
    }
    if ($startPage !== null || $endPage !== null) {
        echo "  Page range: " . ($startPage ?? 1) . " - " . ($endPage ?? 'end') . "\n";
    }
    if ($category !== null) {
        echo "  Category: {$category}\n";
    }

    $sites = $site === 'all' ? ['danjipai', 'gxkgame'] : [$site];

    $exporter = null;
    if ($outputTarget === 'db') {
        $dbConfig = require __DIR__ . '/src/Config/database.config.php';
        $pdo = new \PDO($dbConfig['dsn'], $dbConfig['username'], $dbConfig['password']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $exporter = new MysqlExporter($pdo);
        echo "  Export target: database ({$dbConfig['dsn']})\n";
    }

    foreach ($sites as $s) {
        if (!$container->has($s)) {
            echo "Unknown site: {$s}\n";
            continue;
        }

        echo "\n--- Scraping {$s} ---\n";
        $collector = $container->get($s);
        if ($minDate !== null) {
            $collector->setMinDate($minDate);
        }
        if ($startPage !== null || $endPage !== null) {
            $collector->setPageRange($startPage, $endPage);
        }
        if ($category !== null) {
            $collector->setCategory($category);
        }
        $startTime = microtime(true);

        if ($exporter !== null) {
            $pdo->beginTransaction();
            $pageHandler = function ($pageItems) use ($exporter, $pdo) {
                $inserted = $exporter->exportBySite($pageItems);
                echo "    Page committed: {$inserted} games\n";
                $pdo->commit();
                $pdo->beginTransaction();
            };
            $collector->scrape($pageHandler);
            $pdo->commit();
        } else {
            $results = $collector->scrape();
            $jsonExporter = new JsonExporter();
            $files = $jsonExporter->exportBySite($results, $outputTarget);
            foreach ($files as $f) {
                echo "  Exported: {$f}\n";
            }
        }

        $elapsed = round(microtime(true) - $startTime, 2);
        echo "\n--- {$s} complete in {$elapsed}s ---\n";
    }
} else {
    echo "Usage:\n";
    echo "  php run.php test <site> <category>    Test extraction for one category\n";
    echo "  php run.php scrape [site] [outdir|db] Full scrape (site: danjipai|gxkgame|all)\n";
    echo "\nExamples:\n";
    echo "  php run.php test danjipai RPG\n";
    echo "  php run.php test gxkgame jsby\n";
    echo "  php run.php scrape all output/\n";
    echo "  php run.php scrape all db\n";
    echo "  php run.php scrape all db 2026-06-01\n";
    echo "  php run.php scrape danjipai db\n";
    echo "  php run.php scrape danjipai output/ 2026-01-01\n";
    echo "  php run.php scrape all db --start-page 5 --end-page 10\n";
    echo "  php run.php scrape gxkgame db 2026-06-01 --start-page 1 --end-page 3\n";
}
