<?php

class RankingsController
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function execute(): array
    {
        $years = [];
        for ($y = 2026; $y >= 1996; $y--) {
            $stmt = $this->pdo->prepare("SELECT g.id, g.title, g.resource_size, g.cover_image_local, g.release_date FROM bo_game g WHERE YEAR(g.release_date) = :year ORDER BY g.score DESC, g.id DESC LIMIT 10");
            $stmt->execute([':year' => $y]);
            $games = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($games)) {
                $years[] = ['year' => $y, 'games' => $games];
            }
        }

        return ['years' => $years];
    }
}
