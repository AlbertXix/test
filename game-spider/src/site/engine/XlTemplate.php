<?php

class XlTemplate
{
    private $templateDir;
    private $cacheDir;
    private $ttl;

    public function __construct(string $templateDir, string $cacheDir, int $ttl = 3600)
    {
        $this->templateDir = rtrim($templateDir, '/\\');
        $this->cacheDir = rtrim($cacheDir, '/\\');
        $this->ttl = $ttl;
    }

    public function render(string $template, array $data = [], bool $skipCache = false): void
    {
        $cacheKey = md5($template . serialize($data));
        $cacheFile = $this->cacheDir . '/' . $cacheKey . '.html';

        $update = isset($_GET['update']) && $_GET['update'] === 'y';

        if (!$update && !$skipCache && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $this->ttl)) {
            echo file_get_contents($cacheFile);
            return;
        }

        $pageContent = $this->capture($template, $data);

        ob_start();
        extract($data);
        include $this->templateDir . '/layout.php';
        $output = ob_get_clean();

        if (!$skipCache) {
            file_put_contents($cacheFile, $output);
        }
        echo $output;
    }

    public function capture(string $template, array $data = []): string
    {
        ob_start();
        extract($data);
        include $this->templateDir . '/' . $template . '.php';
        return ob_get_clean();
    }
}
