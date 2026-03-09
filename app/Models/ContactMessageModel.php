<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ContactMessageModel
{
    public static function create(array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO contact_messages (name, email, subject, category, message, created_at)
             VALUES (:name, :email, :subject, :category, :message, CURRENT_TIMESTAMP)'
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':subject' => $data['subject'],
            ':category' => $data['category'] ?? 'general',
            ':message' => $data['message'],
        ]);
    }

    public static function latest(int $limit = 20): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function countAll(): int
    {
        $pdo = Database::connection();
        return (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
    }

    public static function countUnread(): int
    {
        $pdo = Database::connection();
        return (int) $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
    }

    public static function markAsRead(int $id): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function markAllAsRead(): void
    {
        $pdo = Database::connection();
        $pdo->exec('UPDATE contact_messages SET is_read = 1 WHERE is_read = 0');
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }
}
