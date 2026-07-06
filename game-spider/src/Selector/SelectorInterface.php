<?php

namespace GameSpider\Selector;

interface SelectorInterface
{
    public function extract(string $html, string $expression): array;
    public function extractFirst(string $html, string $expression): ?string;
}
