<?php

class GamesController
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function execute(): array
    {
        $tags = $this->pdo->query('SELECT id, tag_name FROM bo_tag ORDER BY id')->fetchAll(\PDO::FETCH_ASSOC);

        $activeTagId = isset($_GET['tag_id']) ? (int) $_GET['tag_id'] : 0;
        $pageNum = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $perPage = 40;

        $where = '';
        $params = [];
        if ($activeTagId) {
            $where = 'JOIN bo_game_tag gt2 ON gt2.game_id = g.id AND gt2.tag_id = :tag_id';
            $params[':tag_id'] = $activeTagId;
        }

        $totalStmt = $this->pdo->prepare("SELECT COUNT(DISTINCT g.id) FROM bo_game g $where");
        $totalStmt->execute($params);
        $total = (int) $totalStmt->fetchColumn();
        $maxPage = max(1, ceil($total / $perPage));
        $offset = ($pageNum - 1) * $perPage;

        $sql = "SELECT DISTINCT g.id, g.title, g.resource_size, g.cover_image, g.cover_image_local, g.created_time FROM bo_game g $where ORDER BY g.id DESC LIMIT $perPage OFFSET $offset";
        $games = $this->pdo->prepare($sql);
        $games->execute($params);
        $games = $games->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'tags' => $tags,
            'activeTagId' => $activeTagId,
            'games' => $games,
            'pageNum' => $pageNum,
            'maxPage' => $maxPage,
        ];
    }
}
