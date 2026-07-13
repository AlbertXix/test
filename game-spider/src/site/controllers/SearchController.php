<?php

class SearchController
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function execute(): array
    {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $results = [];

        if ($q !== '') {
            $like = '%' . $q . '%';
            $sql = "SELECT DISTINCT g.id, g.title, g.resource_size, g.cover_image, g.cover_image_local, g.release_date, g.created_time " .
                "FROM bo_game g WHERE g.title LIKE :q ORDER BY g.id DESC LIMIT 1000";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':q' => $like]);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return [
            'q' => $q,
            'results' => $results,
        ];
    }
}
