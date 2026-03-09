<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class PostModel
{
    public const THEMES = [
        'general' => 'Général',
        'aide' => 'Aide',
        'administratif' => 'Administratif',
        'temoignage' => 'Témoignage',
    ];

    public static function latestPublished(int $limit = 3): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE is_published = 1 ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function allPublished(?string $theme = null): array
    {
        $pdo = Database::connection();

        if ($theme !== null && isset(self::THEMES[$theme])) {
            $stmt = $pdo->prepare('SELECT * FROM posts WHERE is_published = 1 AND theme = :theme ORDER BY created_at DESC');
            $stmt->execute([':theme' => $theme]);
            return $stmt->fetchAll();
        }

        return $pdo->query('SELECT * FROM posts WHERE is_published = 1 ORDER BY created_at DESC')->fetchAll();
    }

    public static function allAdmin(?string $theme = null): array
    {
        $pdo = Database::connection();

        if ($theme !== null && isset(self::THEMES[$theme])) {
            $stmt = $pdo->prepare('SELECT * FROM posts WHERE theme = :theme ORDER BY created_at DESC');
            $stmt->execute([':theme' => $theme]);
            return $stmt->fetchAll();
        }

        return $pdo->query('SELECT * FROM posts ORDER BY created_at DESC')->fetchAll();
    }

    public static function publishedThemeCounts(): array
    {
        $pdo = Database::connection();
        $rows = $pdo->query('SELECT theme, COUNT(*) AS total FROM posts WHERE is_published = 1 GROUP BY theme')->fetchAll();

        $counts = [];
        foreach (self::THEMES as $key => $_label) {
            $counts[$key] = 0;
        }

        foreach ($rows as $row) {
            $key = (string) ($row['theme'] ?? 'general');
            if (array_key_exists($key, $counts)) {
                $counts[$key] = (int) ($row['total'] ?? 0);
            }
        }

        return $counts;
    }

    public static function adminThemeCounts(): array
    {
        $pdo = Database::connection();
        $rows = $pdo->query('SELECT theme, COUNT(*) AS total FROM posts GROUP BY theme')->fetchAll();

        $counts = [];
        foreach (self::THEMES as $key => $_label) {
            $counts[$key] = 0;
        }

        foreach ($rows as $row) {
            $key = (string) ($row['theme'] ?? 'general');
            if (array_key_exists($key, $counts)) {
                $counts[$key] = (int) ($row['total'] ?? 0);
            }
        }

        return $counts;
    }

    public static function findPublishedBySlug(string $slug): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE slug = :slug AND is_published = 1 LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function create(array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO posts (title, slug, excerpt, content, theme, is_published, created_at, updated_at)
             VALUES (:title, :slug, :excerpt, :content, :theme, :is_published, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
        );
        $stmt->execute([
            ':title' => $data['title'],
            ':slug' => $data['slug'],
            ':excerpt' => $data['excerpt'],
            ':content' => $data['content'],
            ':theme' => $data['theme'] ?? 'general',
            ':is_published' => $data['is_published'] ? 1 : 0,
        ]);
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE posts
             SET title = :title, slug = :slug, excerpt = :excerpt, content = :content,
                 theme = :theme, is_published = :is_published, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':title' => $data['title'],
            ':slug' => $data['slug'],
            ':excerpt' => $data['excerpt'],
            ':content' => $data['content'],
            ':theme' => $data['theme'] ?? 'general',
            ':is_published' => $data['is_published'] ? 1 : 0,
        ]);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
