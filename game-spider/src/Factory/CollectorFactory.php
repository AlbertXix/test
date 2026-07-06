<?php

namespace GameSpider\Factory;

use GameSpider\Collector\CollectorInterface;
use GameSpider\Collector\DanjipaiCollector;
use GameSpider\Collector\GxkgameCollector;
use GameSpider\Selector\CssSelector;
use GameSpider\Selector\SelectorInterface;
use GameSpider\Selector\XPathSelector;

class CollectorFactory
{
    public static function create(string $site, array $dependencies = []): CollectorInterface
    {
        $fetcher = $dependencies['fetcher'] ?? null;
        $extractor = $dependencies['extractor'] ?? null;

        return match ($site) {
            'gxkgame' => new GxkgameCollector($fetcher, $extractor),
            'danjipai' => new DanjipaiCollector($fetcher, $extractor),
            default => throw new \InvalidArgumentException("Unknown site: {$site}"),
        };
    }

    public static function createWithSelector(string $site, string $selectorType = 'css'): CollectorInterface
    {
        $selector = self::createSelector($selectorType);
        $fetcher = new \GameSpider\Fetcher\PageFetcher();
        $extractor = new \GameSpider\Extractor\ContentExtractor($selector);

        return self::create($site, [
            'fetcher' => $fetcher,
            'extractor' => $extractor,
        ]);
    }

    public static function createSelector(string $type): SelectorInterface
    {
        return match ($type) {
            'xpath' => new XPathSelector(),
            'css' => new CssSelector(),
            default => throw new \InvalidArgumentException("Unknown selector type: {$type}"),
        };
    }
}
