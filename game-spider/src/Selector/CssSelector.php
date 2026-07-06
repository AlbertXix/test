<?php

namespace GameSpider\Selector;

use Symfony\Component\DomCrawler\Crawler;

class CssSelector implements SelectorInterface
{
    public function extract(string $html, string $expression): array
    {
        $crawler = new Crawler($html);
        $nodes = $crawler->filter($expression);
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

    public function extractAttribute(string $html, string $expression, string $attribute): array
    {
        $crawler = new Crawler($html);
        $nodes = $crawler->filter($expression);
        $results = [];

        foreach ($nodes as $node) {
            $val = $node->getAttribute($attribute);
            if ($val !== '') {
                $results[] = $val;
            }
        }

        return $results;
    }

    public function extractHtml(string $html, string $expression): array
    {
        $crawler = new Crawler($html);
        $nodes = $crawler->filter($expression);
        $results = [];

        foreach ($nodes as $node) {
            $results[] = $node->ownerDocument->saveHTML($node);
        }

        return $results;
    }
}
