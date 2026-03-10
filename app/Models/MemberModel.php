<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class MemberModel
{
    /** Rôles possibles pour un membre de l'association */
    public const ROLES = [
        'president'  => 'Président(e)',
        'tresorier'  => 'Trésorier(ère)',
        'secretaire' => 'Secrétaire',
        'bureau'     => 'Membre du bureau',
        'benevole'   => 'Bénévole',
        'membre'     => 'Membre',
    ];

    public const STATUSES = [
        'active'   => 'Actif',
        'inactive' => 'Inactif',
        'suspended' => 'Suspendu',
    ];

    public static function all(): array
    {
        $pdo = Database::connection();
        return $pdo->query('SELECT * FROM members ORDER BY last_name ASC, first_name ASC')->fetchAll();
    }

    public static function allActive(): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM members WHERE status = :status ORDER BY last_name ASC, first_name ASC');
        $stmt->execute([':status' => 'active']);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM members WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    public static function findByUserId(int $userId): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM members WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** Logements attribués au membre (actifs et historique) */
    public static function getRentals(int $memberId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT mr.*, r.title, r.location_label, r.price, r.description AS rental_description
             FROM member_rentals mr
             JOIN rentals r ON r.id = mr.rental_id
             WHERE mr.member_id = :member_id
             ORDER BY mr.status = "active" DESC, mr.assigned_at DESC'
        );
        $stmt->execute([':member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    /** Logement actif du membre (le dernier en cours) */
    public static function getActiveRental(int $memberId): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT mr.*, r.title, r.location_label, r.price, r.description AS rental_description
             FROM member_rentals mr
             JOIN rentals r ON r.id = mr.rental_id
             WHERE mr.member_id = :member_id AND mr.status = "active"
             ORDER BY mr.assigned_at DESC
             LIMIT 1'
        );
        $stmt->execute([':member_id' => $memberId]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** Attribuer un logement */
    public static function assignRental(
        int $memberId,
        int $rentalId,
        string $assignedAt,
        ?int $leaseDurationValue = null,
        ?string $leaseDurationUnit = null,
        ?string $notes = null
    ): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO member_rentals (member_id, rental_id, assigned_at, lease_duration_value, lease_duration_unit, status, notes)
             VALUES (:member_id, :rental_id, :assigned_at, :lease_duration_value, :lease_duration_unit, "active", :notes)'
        );
        $stmt->execute([
            ':member_id'   => $memberId,
            ':rental_id'   => $rentalId,
            ':assigned_at' => $assignedAt,
            ':lease_duration_value' => $leaseDurationValue,
            ':lease_duration_unit' => $leaseDurationUnit,
            ':notes'       => $notes,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** Libérer un logement */
    public static function releaseRental(int $assignmentId, string $releasedAt): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE member_rentals SET status = "released", released_at = :released_at WHERE id = :id'
        );
        $stmt->execute([':id' => $assignmentId, ':released_at' => $releasedAt]);
    }

    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO members (first_name, last_name, email, phone, role, status, user_id, joined_at, notes)
             VALUES (:first_name, :last_name, :email, :phone, :role, :status, :user_id, :joined_at, :notes)'
        );
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':email'      => $data['email'] ?: null,
            ':phone'      => $data['phone'] ?: null,
            ':role'       => $data['role'] ?? 'membre',
            ':status'     => $data['status'] ?? 'active',
            ':user_id'    => !empty($data['user_id']) ? (int) $data['user_id'] : null,
            ':joined_at'  => $data['joined_at'] ?: null,
            ':notes'      => $data['notes'] ?: null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE members SET first_name = :first_name, last_name = :last_name, email = :email,
             phone = :phone, role = :role, status = :status, user_id = :user_id,
             joined_at = :joined_at, notes = :notes, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            ':id'         => $id,
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':email'      => $data['email'] ?: null,
            ':phone'      => $data['phone'] ?: null,
            ':role'       => $data['role'] ?? 'membre',
            ':status'     => $data['status'] ?? 'active',
            ':user_id'    => !empty($data['user_id']) ? (int) $data['user_id'] : null,
            ':joined_at'  => $data['joined_at'] ?: null,
            ':notes'      => $data['notes'] ?: null,
        ]);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM members WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function count(): int
    {
        $pdo = Database::connection();
        return (int) $pdo->query('SELECT COUNT(*) FROM members')->fetchColumn();
    }

    public static function countActive(): int
    {
        $pdo = Database::connection();
        return (int) $pdo->query('SELECT COUNT(*) FROM members WHERE status = \'active\'')->fetchColumn();
    }

    public static function countByRole(string $role): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM members WHERE role = :role');
        $stmt->execute([':role' => $role]);
        return (int) $stmt->fetchColumn();
    }

    public static function countIncompleteProfiles(): int
    {
        $pdo = Database::connection();
        return (int) $pdo->query(
            'SELECT COUNT(*) FROM members
             WHERE TRIM(COALESCE(email, "")) = ""
                OR TRIM(COALESCE(phone, "")) = ""
                OR TRIM(COALESCE(joined_at, "")) = ""'
        )->fetchColumn();
    }
}
