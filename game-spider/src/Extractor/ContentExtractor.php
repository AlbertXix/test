<?php

namespace GameSpider\Extractor;

use GameSpider\Selector\SelectorInterface;

class ContentExtractor
{
    private SelectorInterface $selector;

    public function __construct(SelectorInterface $selector)
    {
        $this->selector = $selector;
    }

    public function setSelector(SelectorInterface $selector): void
    {
        $this->selector = $selector;
    }

    public function extract(string $html, string $expression): array
    {
        return $this->selector->extract($html, $expression);
    }

    public function extractFirst(string $html, string $expression): ?string
    {
        return $this->selector->extractFirst($html, $expression);
    }

    public function extractHtml(string $html, string $expression): array
    {
        if (method_exists($this->selector, 'extractHtml')) {
            return $this->selector->extractHtml($html, $expression);
        }
        return $this->extract($html, $expression);
    }

    public function extractFirstHtml(string $html, string $expression): ?string
    {
        $parts = $this->extractHtml($html, $expression);
        return $parts[0] ?? null;
    }
}
