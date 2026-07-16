<?php

/**
 * 年度排名控制器 — 按年份分组，每年前 10 名
 */
class RankingsController
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
        $years = [];
        // 从 2026 倒序遍历到 1996，取每年评分最高的 10 款可见游戏
        for ($y = 2026; $y >= 1996; $y--) {
            $stmt = $this->pdo->prepare("SELECT g.id, g.title, g.resource_size, g.cover_image_local, g.release_date FROM bo_game g WHERE YEAR(g.release_date) = :year AND g.visible = 1 ORDER BY g.score DESC, g.id DESC LIMIT 10");
            $stmt->execute([':year' => $y]);
            $games = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($games)) {
                if ($this->bot->isCrawler()) {
                    foreach ($games as &$g) {
                        $g['title'] = $this->bot->poisonText($g['title']);
                    }
                }
                $years[] = ['year' => $y, 'games' => $games];
            }
        }

        return ['years' => $years];
    }
}
