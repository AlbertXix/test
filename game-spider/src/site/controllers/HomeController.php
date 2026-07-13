<?php

class HomeController
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function execute(): array
    {
        $tags = $this->pdo->query('SELECT id, tag_name FROM bo_tag ORDER BY id')->fetchAll(\PDO::FETCH_ASSOC);

        $latestByTag = [];
        foreach ($tags as $tag) {
            $stmt = $this->pdo->prepare('SELECT g.id, g.title, g.title_en, g.resource_size, g.cover_image, g.cover_image_local, g.created_time, g.description FROM bo_game g JOIN bo_game_tag gt ON gt.game_id = g.id WHERE gt.tag_id = :tag_id ORDER BY g.id DESC LIMIT 4');
            $stmt->execute([':tag_id' => $tag['id']]);
            $games = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($games)) {
                $latestByTag[] = ['tag_id' => $tag['id'], 'tag' => $tag['tag_name'], 'games' => $games];
            }
        }

        return [
            'tags' => $tags,
            'latestByTag' => $latestByTag,
        ];
    }
}
