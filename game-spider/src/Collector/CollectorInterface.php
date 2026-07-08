<?php

namespace GameSpider\Collector;

interface CollectorInterface
{
    public function getName(): string;

    public function getCategories(): array;

    public function getListUrl(string $category, int $page): string;

    public function getTotalPages(string $listHtml): int;

    public function extractDetailUrls(string $listHtml): array;

    public function extractTitle(string $detailHtml): string;

    public function extractContent(string $detailHtml): string;

    public function scrape(): array;

    public function fullImageUrl(string $imageUrl, string $baseUrl = ''): string;
}
