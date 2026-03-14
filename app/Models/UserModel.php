<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class UserModel
{
    /** Rôles utilisateurs du système */
    public const ROLES = [
        'webmaster'  => 'Webmaster',
        'admin'      => 'Président',
        'moderator'  => 'Vice président',
        'treasurer'  => 'Trésorier',
        'editor'     => 'Secrétaire',
        'member'     => 'Membre',
    ];

    /** Permissions par rôle (cumulatives) */
    public const PERMISSIONS = [
        'webmaster' => ['*'],
        'admin'     => ['*'],
        'moderator' => ['dashboard', 'articles', 'articles.create', 'articles.edit', 'articles.delete',
                        'events', 'events.create', 'events.edit', 'events.delete',
                        'messages', 'messages.read', 'messages.manage',
                        'rentals', 'rentals.create', 'rentals.edit', 'rentals.delete',
                        'members', 'members.view',
                        'accounting.view'],
        'treasurer' => ['dashboard', 'members.view', 'accounting.view', 'accounting.manage'],
        'editor'    => ['dashboard', 'articles', 'articles.create', 'articles.edit',
                        'events', 'events.create', 'events.edit',
                        'messages', 'members.view'],
        'member'    => ['dashboard', 'members.view'],
    ];

    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => mb_strtolower(trim($email))]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    public static function all(): array
    {
        $pdo = Database::connection();
        return $pdo->query('SELECT id, name, email, role, is_active, created_at FROM users ORDER BY created_at DESC')->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password_hash, role, is_active)
             VALUES (:name, :email, :password_hash, :role, :is_active)'
        );
        $stmt->execute([
            ':name'          => $data['name'],
            ':email'         => mb_strtolower(trim($data['email'])),
            ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role'          => $data['role'] ?? 'member',
            ':is_active'     => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::connection();
        $fields = [
            'name = :name',
            'email = :email',
            'role = :role',
            'is_active = :is_active',
        ];
        $params = [
            ':id'        => $id,
            ':name'      => $data['name'],
            ':email'     => mb_strtolower(trim($data['email'])),
            ':role'      => $data['role'] ?? 'member',
            ':is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ];

        if (!empty($data['password'])) {
            $fields[] = 'password_hash = :password_hash';
            $params[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function count(): int
    {
        $pdo = Database::connection();
        return (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public static function hasPermission(string $role, string $permission): bool
    {
        $perms = self::PERMISSIONS[$role] ?? [];
        if (in_array('*', $perms, true)) {
            return true;
        }
        return in_array($permission, $perms, true);
    }
}
