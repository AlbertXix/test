<?php

namespace GameSpider\Selector;

use Symfony\Component\DomCrawler\Crawler;

class XPathSelector implements SelectorInterface
{
    public function extract(string $html, string $expression): array
    {
        $crawler = new Crawler($html);
        $nodes = $crawler->filterXPath($expression);
        $results = [];

        foreach ($nodes as $node) {
            $text = $node->textContent;
            $text = trim(preg_replace('/\s+/', ' ', $text));
            if ($text !== '') {
                $results[] = $text;
            }
        }

        return $results;
    }

    public function extractFirst(string $html, string $expression): ?string
    {
        $results = $this->extract($html, $expression);
        return $results[0] ?? null;
    }
}
