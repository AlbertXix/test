<?php

class DetailController
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function execute(): array
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        $game = null;
        $gameContent = '';
        $gameTags = [];
        $screenshots = [];

        if ($id) {
            $stmt = $this->pdo->prepare('SELECT * FROM bo_game WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $game = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($game) {
                $gameContent = htmlspecialchars_decode($game['content'], ENT_QUOTES);

                $stmt = $this->pdo->prepare('SELECT t.tag_name FROM bo_tag t JOIN bo_game_tag gt ON gt.tag_id = t.id WHERE gt.game_id = :game_id');
                $stmt->execute([':game_id' => $id]);
                $gameTags = $stmt->fetchAll(\PDO::FETCH_COLUMN);

                $stmt = $this->pdo->prepare('SELECT image_local, image_url FROM bo_game_screenshot WHERE game_id = :game_id ORDER BY id ASC');
                $stmt->execute([':game_id' => $id]);
                $screenshots = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        }

        return [
            'game' => $game,
            'gameContent' => $gameContent,
            'gameTags' => $gameTags,
            'screenshots' => $screenshots,
        ];
    }
}
