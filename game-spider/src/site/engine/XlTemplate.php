<?php

/**
 * XlTemplate 模板引擎 — 支持 layout 嵌套、文件缓存（?update=y 强制刷新）
 */
class XlTemplate
{
    /** @var string 模板文件目录 */
    private $templateDir;
    /** @var string 缓存文件目录 */
    private $cacheDir;
    /** @var int 缓存有效期（秒） */
    private $ttl;

    /**
     * @param string $templateDir 模板目录
     * @param string $cacheDir 缓存目录
     * @param int $ttl 缓存 TTL（秒）
     */
    public function __construct(string $templateDir, string $cacheDir, int $ttl = 3600)
    {
        $this->templateDir = rtrim($templateDir, '/\\');
        $this->cacheDir = rtrim($cacheDir, '/\\');
        $this->ttl = $ttl;
    }

    /**
     * 渲染页面：先捕获页面内容，再嵌入 layout 输出
     * @param string $template 模板名（不含 .php）
     * @param array $data 模板变量
     * @param bool $skipCache 是否跳过缓存
     */
    public function render(string $template, array $data = [], bool $skipCache = false): void
    {
        $cacheKey = md5($template . serialize($data));
        $cacheFile = $this->cacheDir . '/' . $cacheKey . '.html';

        $update = isset($_GET['update']) && $_GET['update'] === 'y';

        // 缓存命中且未过期则直接输出
        if (!$update && !$skipCache && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $this->ttl)) {
            echo file_get_contents($cacheFile);
            return;
        }

        // 捕获页面内容
        $pageContent = $this->capture($template, $data);

        // 嵌入 layout 输出
        ob_start();
        extract($data);
        include $this->templateDir . '/layout.php';
        $output = ob_get_clean();

        if (!$skipCache) {
            file_put_contents($cacheFile, $output);
        }
        echo $output;
    }

    /**
     * 捕获模板的纯内容（不含 layout）
     * @param string $template 模板名
     * @param array $data 模板变量
     * @return string 渲染后的 HTML
     */
    public function capture(string $template, array $data = []): string
    {
        ob_start();
        extract($data);
        include $this->templateDir . '/' . $template . '.php';
        return ob_get_clean();
    }
}
