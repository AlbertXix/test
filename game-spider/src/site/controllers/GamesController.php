<?php

/**
 * 游戏列表控制器 — 支持按标签筛选、分页查询
 */
class GamesController
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
        // 查询所有标签
        $tags = $this->pdo->query('SELECT id, tag_name FROM bo_tag ORDER BY id')->fetchAll(\PDO::FETCH_ASSOC);

        // 获取筛选参数和分页参数
        $activeTagId = isset($_GET['tag_id']) ? (int) $_GET['tag_id'] : 0;
        $pageNum = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $perPage = 20;

        // 构建查询条件（可见 + 可选标签过滤）
        $where = 'WHERE g.visible = 1';
        $join = '';
        $params = [];
        if ($activeTagId) {
            $join = 'JOIN bo_game_tag gt2 ON gt2.game_id = g.id AND gt2.tag_id = :tag_id';
            $params[':tag_id'] = $activeTagId;
        }

        // 计算总数和分页
        $totalStmt = $this->pdo->prepare("SELECT COUNT(DISTINCT g.id) FROM bo_game g $join $where");
        $totalStmt->execute($params);
        $total = (int) $totalStmt->fetchColumn();
        $maxPage = max(1, ceil($total / $perPage));
        $offset = ($pageNum - 1) * $perPage;

        // 查询当前页游戏
        $sql = "SELECT DISTINCT g.id, g.title, g.resource_size, g.cover_image, g.cover_image_local, g.created_time FROM bo_game g $join $where ORDER BY g.id DESC LIMIT $perPage OFFSET $offset";
        $games = $this->pdo->prepare($sql);
        $games->execute($params);
        $games = $games->fetchAll(\PDO::FETCH_ASSOC);

        if ($this->bot->isCrawler()) {
            foreach ($games as &$g) {
                $g['title'] = $this->bot->poisonText($g['title']);
            }
        }

        return [
            'tags' => $tags,
            'activeTagId' => $activeTagId,
            'games' => $games,
            'pageNum' => $pageNum,
            'maxPage' => $maxPage,
        ];
    }
}
