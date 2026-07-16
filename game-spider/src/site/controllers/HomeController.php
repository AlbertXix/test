<?php

/**
 * 首页控制器 — 获取轮播焦点图、按标签分类的最新游戏列表
 */
class HomeController
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

        // 每个标签取最新 4 款可见游戏
        $latestByTag = [];
        foreach ($tags as $tag) {
            $stmt = $this->pdo->prepare('SELECT g.id, g.title, g.title_en, g.resource_size, g.cover_image, g.cover_image_local, g.created_time, g.description FROM bo_game g JOIN bo_game_tag gt ON gt.game_id = g.id WHERE gt.tag_id = :tag_id AND g.visible = 1 ORDER BY g.id DESC LIMIT 4');
            $stmt->execute([':tag_id' => $tag['id']]);
            $games = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            // 不足 4 款则不展示该分类
            if (count($games) >= 4) {
                if ($this->bot->isCrawler()) {
                    foreach ($games as &$g) {
                        $g['title'] = $this->bot->poisonText($g['title']);
                    }
                }
                $latestByTag[] = ['tag_id' => $tag['id'], 'tag' => $tag['tag_name'], 'games' => $games];
            }
        }

        // 取焦点图游戏（最多 10 个，is_top 优先）
        $focusGames = $this->pdo->query('SELECT id, title, cover_image, cover_image_local, description FROM bo_game WHERE is_focus = 1 AND visible = 1 ORDER BY is_top DESC, id DESC LIMIT 10')->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'tags' => $tags,
            'latestByTag' => $latestByTag,
            'focusGames' => $focusGames,
        ];
    }
}
