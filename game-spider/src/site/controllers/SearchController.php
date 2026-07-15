<?php

class SearchController
{
    private $pdo;
    private $bot;

    public function __construct(\PDO $pdo, BotDetector $bot)
    {
        $this->pdo = $pdo;
        $this->bot = $bot;
    }

    public function execute(): array
    {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $pageNum = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $perPage = 20;
        $total = 0;
        $results = [];

        if ($q !== '') {
            $like = '%' . $q . '%';

            $countStmt = $this->pdo->prepare("SELECT COUNT(DISTINCT g.id) FROM bo_game g WHERE g.title LIKE :q");
            $countStmt->execute([':q' => $like]);
            $total = (int) $countStmt->fetchColumn();

            if ($total > 0) {
                $maxPage = max(1, ceil($total / $perPage));
                $pageNum = min($pageNum, $maxPage);
                $offset = ($pageNum - 1) * $perPage;

                $sql = "SELECT DISTINCT g.id, g.title, g.resource_size, g.cover_image, g.cover_image_local, g.release_date, g.created_time " .
                    "FROM bo_game g WHERE g.title LIKE :q ORDER BY g.id DESC LIMIT $perPage OFFSET $offset";
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
        ];
    }
}
