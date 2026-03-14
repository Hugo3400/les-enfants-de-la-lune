<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class MemberModel
{
    /** Rôles possibles pour un membre de l'association */
    public const ROLES = [
        'president'  => 'Président',
        'vice_president' => 'Vice président',
        'tresorier'  => 'Trésorier',
        'secretaire' => 'Secrétaire',
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
        return $pdo->query('SELECT * FROM members ORDER BY joined_at ASC, last_name ASC')->fetchAll();
    }

    /** Liste complète avec logement actif (JOIN), filtres et tri */
    public static function allWithActiveRentals(array $filters = [], string $sort = 'name_asc'): array
    {
        $pdo = Database::connection();
        $where = [];
        $params = [];

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $where[] = '(LOWER(COALESCE(m.first_name, "")) LIKE :search
                OR LOWER(COALESCE(m.last_name, "")) LIKE :search
                OR LOWER(COALESCE(m.email, "")) LIKE :search
                OR LOWER(COALESCE(m.phone, "")) LIKE :search)';
            $params[':search'] = '%' . mb_strtolower($search) . '%';
        }

        $memberRole = trim((string) ($filters['member_role'] ?? 'all'));
        if ($memberRole !== 'all' && array_key_exists($memberRole, self::ROLES)) {
            $where[] = 'm.role = :member_role';
            $params[':member_role'] = $memberRole;
        }

        $accountRole = trim((string) ($filters['account_role'] ?? 'all'));
        if ($accountRole === 'none') {
            $where[] = 'm.user_id IS NULL';
        } elseif ($accountRole === 'privileged') {
            $where[] = 'u.role IN ("webmaster", "admin", "moderator", "treasurer", "editor")';
        } elseif (in_array($accountRole, ['webmaster', 'admin', 'moderator', 'treasurer', 'editor', 'member'], true)) {
            $where[] = 'u.role = :account_role';
            $params[':account_role'] = $accountRole;
        }

        $housing = trim((string) ($filters['housing'] ?? 'all'));
        if ($housing === 'assigned') {
            $where[] = 'r.id IS NOT NULL';
        } elseif ($housing === 'unassigned') {
            $where[] = 'r.id IS NULL';
        }

        $payment = trim((string) ($filters['paye'] ?? 'all'));
        if (in_array($payment, ['oui', 'non', 'en_cours'], true)) {
            $where[] = 'm.paye = :paye';
            $params[':paye'] = $payment;
        }

        $orderBy = match ($sort) {
            'name_desc' => 'm.last_name DESC, m.first_name DESC',
            'joined_desc' => 'm.joined_at DESC, m.last_name ASC, m.first_name ASC',
            'joined_asc' => 'm.joined_at ASC, m.last_name ASC, m.first_name ASC',
            'member_role_asc' => 'm.role ASC, m.last_name ASC, m.first_name ASC',
            'member_role_desc' => 'm.role DESC, m.last_name ASC, m.first_name ASC',
            'account_role_asc' => 'u.role ASC, m.last_name ASC, m.first_name ASC',
            'account_role_desc' => 'u.role DESC, m.last_name ASC, m.first_name ASC',
            'housing_asc' => 'r.title ASC, m.last_name ASC, m.first_name ASC',
            'housing_desc' => 'r.title DESC, m.last_name ASC, m.first_name ASC',
            default => 'm.last_name ASC, m.first_name ASC',
        };

        $sql = 'SELECT m.*, r.title AS rental_title, r.location_label AS rental_location,
                       u.role AS linked_user_role, u.is_active AS linked_user_active
                FROM members m
                LEFT JOIN member_rentals mr ON mr.member_id = m.id AND mr.status = "active"
                LEFT JOIN rentals r ON r.id = mr.rental_id
                LEFT JOIN users u ON u.id = m.user_id';

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY ' . $orderBy;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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

    public static function findByEmail(string $email): ?array
    {
        $normalizedEmail = mb_strtolower(trim($email));
        if ($normalizedEmail === '') {
            return null;
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM members WHERE LOWER(COALESCE(email, "")) = :email LIMIT 1');
        $stmt->execute([':email' => $normalizedEmail]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    public static function attachUser(int $memberId, int $userId): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE members
             SET user_id = :user_id,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $memberId,
            ':user_id' => $userId,
        ]);
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
            'INSERT INTO members (first_name, last_name, email, phone, role, status, user_id, joined_at, notes,
             rib, recensement_bc, carte, carte_validite, situation, rdv_situation, paye,
             coupon_classic_bikes, coupon_seaton_sand, coupon_rex_dinner, coupon_yellow_jack, coupon_mojito)
             VALUES (:first_name, :last_name, :email, :phone, :role, :status, :user_id, :joined_at, :notes,
             :rib, :recensement_bc, :carte, :carte_validite, :situation, :rdv_situation, :paye,
             :coupon_classic_bikes, :coupon_seaton_sand, :coupon_rex_dinner, :coupon_yellow_jack, :coupon_mojito)'
        );
        $stmt->execute([
            ':first_name'          => $data['first_name'],
            ':last_name'           => $data['last_name'],
            ':email'               => $data['email'] ?: null,
            ':phone'               => $data['phone'] ?: null,
            ':role'                => $data['role'] ?? 'membre',
            ':status'              => $data['status'] ?? 'active',
            ':user_id'             => !empty($data['user_id']) ? (int) $data['user_id'] : null,
            ':joined_at'           => $data['joined_at'] ?: null,
            ':notes'               => $data['notes'] ?: null,
            ':rib'                 => $data['rib'] ?: null,
            ':recensement_bc'      => $data['recensement_bc'] ?: null,
            ':carte'               => $data['carte'] ?: null,
            ':carte_validite'      => $data['carte_validite'] ?: null,
            ':situation'           => $data['situation'] ?: null,
            ':rdv_situation'       => $data['rdv_situation'] ?: null,
            ':paye'                => $data['paye'] ?: null,
            ':coupon_classic_bikes'=> (int) ($data['coupon_classic_bikes'] ?? 0),
            ':coupon_seaton_sand'  => (int) ($data['coupon_seaton_sand'] ?? 0),
            ':coupon_rex_dinner'   => (int) ($data['coupon_rex_dinner'] ?? 0),
            ':coupon_yellow_jack'  => (int) ($data['coupon_yellow_jack'] ?? 0),
            ':coupon_mojito'       => (int) ($data['coupon_mojito'] ?? 0),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE members SET first_name = :first_name, last_name = :last_name, email = :email,
             phone = :phone, role = :role, status = :status, user_id = :user_id,
             joined_at = :joined_at, notes = :notes,
             rib = :rib, recensement_bc = :recensement_bc, carte = :carte,
             carte_validite = :carte_validite, situation = :situation,
             rdv_situation = :rdv_situation, paye = :paye,
             coupon_classic_bikes = :coupon_classic_bikes, coupon_seaton_sand = :coupon_seaton_sand,
             coupon_rex_dinner = :coupon_rex_dinner, coupon_yellow_jack = :coupon_yellow_jack,
             coupon_mojito = :coupon_mojito,
             updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            ':id'                  => $id,
            ':first_name'          => $data['first_name'],
            ':last_name'           => $data['last_name'],
            ':email'               => $data['email'] ?: null,
            ':phone'               => $data['phone'] ?: null,
            ':role'                => $data['role'] ?? 'membre',
            ':status'              => $data['status'] ?? 'active',
            ':user_id'             => !empty($data['user_id']) ? (int) $data['user_id'] : null,
            ':joined_at'           => $data['joined_at'] ?: null,
            ':notes'               => $data['notes'] ?: null,
            ':rib'                 => $data['rib'] ?: null,
            ':recensement_bc'      => $data['recensement_bc'] ?: null,
            ':carte'               => $data['carte'] ?: null,
            ':carte_validite'      => $data['carte_validite'] ?: null,
            ':situation'           => $data['situation'] ?: null,
            ':rdv_situation'       => $data['rdv_situation'] ?: null,
            ':paye'                => $data['paye'] ?: null,
            ':coupon_classic_bikes'=> (int) ($data['coupon_classic_bikes'] ?? 0),
            ':coupon_seaton_sand'  => (int) ($data['coupon_seaton_sand'] ?? 0),
            ':coupon_rex_dinner'   => (int) ($data['coupon_rex_dinner'] ?? 0),
            ':coupon_yellow_jack'  => (int) ($data['coupon_yellow_jack'] ?? 0),
            ':coupon_mojito'       => (int) ($data['coupon_mojito'] ?? 0),
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
