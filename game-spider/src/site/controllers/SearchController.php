<?php

/**
 * 搜索控制器 — 按游戏名称模糊搜索，支持分页
 */
class SearchController
{
    /** @var \PDO 数据库连接 */
    private $pdo;
    /** @var BotDetector 爬虫检测器 */
    private $bot;

    public function __construct(\PDO $pdo, BotDetector $bot)
    {
        $this->pdo = $pdo;
        $this->bot = $bot;
    }

    /** 执行控制器逻辑，返回模板数据 */
    public function execute(): array
    {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $pageNum = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $perPage = 20;
        $total = 0;
        $results = [];

        if ($q !== '') {
            // 模糊搜索 + 可见性过滤
            $like = '%' . $q . '%';

            $countStmt = $this->pdo->prepare("SELECT COUNT(DISTINCT g.id) FROM bo_game g WHERE g.title LIKE :q AND g.visible = 1");
            $countStmt->execute([':q' => $like]);
            $total = (int) $countStmt->fetchColumn();

            if ($total > 0) {
                $maxPage = max(1, ceil($total / $perPage));
                $pageNum = min($pageNum, $maxPage);
                $offset = ($pageNum - 1) * $perPage;

                $sql = "SELECT DISTINCT g.id, g.title, g.resource_size, g.cover_image, g.cover_image_local, g.release_date, g.created_time " .
                    "FROM bo_game g WHERE g.title LIKE :q AND g.visible = 1 ORDER BY g.id DESC LIMIT $perPage OFFSET $offset";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':q' => $like]);
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if ($this->bot->isCrawler()) {
                    foreach ($results as &$g) {
                        $g['title'] = $this->bot->poisonText($g['title']);
                    }
                }
            }
        }

        return [
            'from' => $_GET['from'] ?? '',
            'q' => $q,
            'results' => $results,
            'pageNum' => $pageNum,
            'maxPage' => max(1, ceil($total / $perPage)),
            'total' => $total,
            'meta' => [
                'keywords' => '搜索结果',
                'description' => '游戏搜索结果页',
            ]
        ];
    }
}
