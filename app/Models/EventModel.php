<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class EventModel
{
    public static function allVisible(): array
    {
        $pdo = Database::connection();
        return $pdo->query('SELECT * FROM events WHERE is_visible = 1 ORDER BY sort_order ASC, event_date ASC')->fetchAll();
    }

    public static function all(): array
    {
        $pdo = Database::connection();
        return $pdo->query('SELECT * FROM events ORDER BY sort_order ASC, created_at DESC')->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM events WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function create(array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO events (title, description, event_date, event_time, is_visible, sort_order, created_at, updated_at)
             VALUES (:title, :description, :event_date, :event_time, :is_visible, :sort_order, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
        );
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':event_date' => $data['event_date'] ?: null,
            ':event_time' => $data['event_time'] ?: null,
            ':is_visible' => $data['is_visible'] ? 1 : 0,
            ':sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE events
             SET title = :title, description = :description, event_date = :event_date,
                 event_time = :event_time, is_visible = :is_visible, sort_order = :sort_order,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':event_date' => $data['event_date'] ?: null,
            ':event_time' => $data['event_time'] ?: null,
            ':is_visible' => $data['is_visible'] ? 1 : 0,
            ':sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM events WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function count(): int
    {
        $pdo = Database::connection();
        return (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
    }

    public static function countVisible(): int
    {
        $pdo = Database::connection();
        return (int) $pdo->query('SELECT COUNT(*) FROM events WHERE is_visible = 1')->fetchColumn();
    }
}
