<?php

class RankingsController
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
        $years = [];
        for ($y = 2026; $y >= 1996; $y--) {
            $stmt = $this->pdo->prepare("SELECT g.id, g.title, g.resource_size, g.cover_image_local, g.release_date FROM bo_game g WHERE YEAR(g.release_date) = :year ORDER BY g.score DESC, g.id DESC LIMIT 10");
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
