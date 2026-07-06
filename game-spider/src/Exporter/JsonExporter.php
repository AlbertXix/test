<?php

namespace GameSpider\Exporter;

class JsonExporter
{
    public function export(array $data, string $filePath): void
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode data to JSON: ' . json_last_error_msg());
        }

        file_put_contents($filePath, $json);
    }

    public function exportBySite(array $data, string $outputDir): array
    {
        $grouped = [];
        foreach ($data as $item) {
            $site = $item['site'] ?? 'unknown';
            $grouped[$site][] = $item;
        }

        $files = [];
        foreach ($grouped as $site => $items) {
            $path = rtrim($outputDir, '/\\') . DIRECTORY_SEPARATOR . "{$site}.json";
            $this->export($items, $path);
            $files[] = $path;
        }

        return $files;
    }
}
