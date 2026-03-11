<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class RentalModel
{
    public static function all(): array
    {
        $pdo = Database::connection();
        return $pdo->query('SELECT * FROM rentals ORDER BY created_at DESC')->fetchAll();
    }

    public static function allAvailable(): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM rentals WHERE status = :status ORDER BY created_at DESC');
        $stmt->execute([':status' => 'available']);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM rentals WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function countByStatus(string $status): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM rentals WHERE status = :status');
        $stmt->execute([':status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public static function create(array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO rentals (title, location_label, price, status, description, created_at, updated_at)
             VALUES (:title, :location_label, :price, :status, :description, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
        );
        $stmt->execute([
            ':title' => $data['title'],
            ':location_label' => $data['location_label'],
            ':price' => $data['price'],
            ':status' => $data['status'],
            ':description' => $data['description'],
        ]);
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE rentals
             SET title = :title,
                 location_label = :location_label,
                 price = :price,
                 status = :status,
                 description = :description,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':title' => $data['title'],
            ':location_label' => $data['location_label'],
            ':price' => $data['price'],
            ':status' => $data['status'],
            ':description' => $data['description'],
        ]);
    }

    public static function delete(int $id): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM rentals WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /** Récupère l'attribution active pour une location donnée */
    public static function currentAssignee(int $rentalId): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT mr.*, m.first_name, m.last_name, m.email AS member_email
             FROM member_rentals mr
             JOIN members m ON m.id = mr.member_id
             WHERE mr.rental_id = :rental_id AND mr.status = "active"
             ORDER BY mr.assigned_at DESC
             LIMIT 1'
        );
        $stmt->execute([':rental_id' => $rentalId]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** Récupère toutes les locations avec leurs occupants actuels */
    public static function allWithAssignees(string $zoneFilter = 'all'): array
    {
        $pdo = Database::connection();
        $sql =
            'SELECT r.*,
                    mr.id AS assignment_id,
                    mr.member_id,
                    mr.assigned_at,
                    mr.notes AS assignment_notes,
                    m.first_name,
                    m.last_name
             FROM rentals r
             LEFT JOIN member_rentals mr ON mr.rental_id = r.id AND mr.status = "active"
             LEFT JOIN members m ON m.id = mr.member_id';

        $params = [];
        if ($zoneFilter === 'paleto') {
            $sql .= ' WHERE LOWER(r.location_label) LIKE :paleto';
            $params[':paleto'] = '%paleto bay%';
        } elseif ($zoneFilter === 'route68') {
            $sql .= ' WHERE LOWER(r.location_label) LIKE :sandy OR LOWER(r.location_label) LIKE :route68';
            $params[':sandy'] = '%sandy shores%';
            $params[':route68'] = '%route 68%';
        }

        $sql .= ' ORDER BY r.created_at DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return $rows;
    }

    /** Attribuer une location à un membre */
    public static function assign(
        int $rentalId,
        int $memberId,
        string $assignedAt,
        ?int $leaseDurationValue = null,
        ?string $leaseDurationUnit = null,
        ?string $notes = null
    ): void
    {
        $pdo = Database::connection();

        // Libérer l'ancienne attribution active le cas échéant
        $stmt = $pdo->prepare(
            'UPDATE member_rentals SET status = "released", released_at = :now
             WHERE rental_id = :rental_id AND status = "active"'
        );
        $stmt->execute([':rental_id' => $rentalId, ':now' => $assignedAt]);

        // Créer la nouvelle
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

        // Passer la location en « non disponible »
        $stmt = $pdo->prepare('UPDATE rentals SET status = "unavailable", updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([':id' => $rentalId]);
    }

    /** Libérer une location */
    public static function release(int $rentalId, string $releasedAt): void
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'UPDATE member_rentals SET status = "released", released_at = :released_at
             WHERE rental_id = :rental_id AND status = "active"'
        );
        $stmt->execute([':rental_id' => $rentalId, ':released_at' => $releasedAt]);

        // Repasser la location en « disponible »
        $stmt = $pdo->prepare('UPDATE rentals SET status = "available", updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([':id' => $rentalId]);
    }

    /** Historique d'attributions pour une location */
    public static function assignmentHistory(int $rentalId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT mr.*, m.first_name, m.last_name
             FROM member_rentals mr
             JOIN members m ON m.id = mr.member_id
             WHERE mr.rental_id = :rental_id
             ORDER BY mr.assigned_at DESC'
        );
        $stmt->execute([':rental_id' => $rentalId]);
        return $stmt->fetchAll();
    }
}
