<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;

final class AccountingModel
{
    public static function latest(int $limit = 50): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM accounting_entries ORDER BY entry_date DESC, id DESC LIMIT :limit');
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function create(array $data): void
    {
        $pdo = Database::connection();
        $status = in_array((string) ($data['entry_status'] ?? 'draft'), ['draft', 'validated'], true)
            ? (string) $data['entry_status']
            : 'draft';

        $validatedAt = $status === 'validated' ? date('Y-m-d H:i:s') : null;
        $stmt = $pdo->prepare(
            'INSERT INTO accounting_entries (
                entry_type,
                account_code,
                payment_method,
                partner_tag,
                reference,
                entry_status,
                validated_at,
                label,
                amount,
                entry_date,
                notes,
                created_at
             ) VALUES (
                :entry_type,
                :account_code,
                :payment_method,
                :partner_tag,
                :reference,
                :entry_status,
                :validated_at,
                :label,
                :amount,
                :entry_date,
                :notes,
                CURRENT_TIMESTAMP
             )'
        );
        $stmt->execute([
            ':entry_type' => $data['entry_type'],
            ':account_code' => $data['account_code'],
            ':payment_method' => $data['payment_method'],
            ':partner_tag' => $data['partner_tag'] ?? 'none',
            ':reference' => $data['reference'],
            ':entry_status' => $status,
            ':validated_at' => $validatedAt,
            ':label' => $data['label'],
            ':amount' => $data['amount'],
            ':entry_date' => $data['entry_date'],
            ':notes' => $data['notes'],
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM accounting_entries WHERE id = :id AND entry_status <> :status');
        $stmt->execute([':id' => $id, ':status' => 'validated']);
        return $stmt->rowCount() > 0;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM accounting_entries WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        $entry = $stmt->fetch();
        return $entry !== false ? $entry : null;
    }

    public static function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, ['draft', 'validated'], true)) {
            return;
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE accounting_entries
             SET entry_status = :status,
                 validated_at = :validated_at
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':status' => $status,
            ':validated_at' => $status === 'validated' ? date('Y-m-d H:i:s') : null,
        ]);
    }

    public static function totals(): array
    {
        return self::totalsFor([]);
    }

    public static function filtered(array $filters = [], int $limit = 300): array
    {
        $pdo = Database::connection();

        [$conditions, $params] = self::buildFilterClauses($filters);
        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
        $sql = 'SELECT * FROM accounting_entries ' . $where . ' ORDER BY entry_date ASC, id ASC LIMIT :limit';

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function totalsFor(array $filters = []): array
    {
        $rows = self::filtered($filters, 3000);
        $income = 0.0;
        $expense = 0.0;
        foreach ($rows as $row) {
            $amount = (float) ($row['amount'] ?? 0);
            if ((string) ($row['entry_type'] ?? '') === 'income') {
                $income += $amount;
            } else {
                $expense += $amount;
            }
        }

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'count' => count($rows),
        ];
    }

    public static function monthOptions(int $limit = 24): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT DISTINCT substr(entry_date, 1, 7) AS month_value
             FROM accounting_entries
             WHERE entry_date IS NOT NULL AND entry_date <> ""
             ORDER BY month_value DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return array_values(array_filter(array_map(static fn(array $row): string => (string) ($row['month_value'] ?? ''), $rows)));
    }

    public static function weekOptions(int $limit = 24): array
    {
        $rows = self::latest(max(150, $limit * 12));
        $weeks = [];

        foreach ($rows as $row) {
            $date = (string) ($row['entry_date'] ?? '');
            if ($date === '') {
                continue;
            }

            try {
                $d = new DateTimeImmutable($date);
            } catch (\Throwable $exception) {
                continue;
            }

            $weekKey = sprintf('%s-W%s', $d->format('o'), $d->format('W'));
            $start = $d->setISODate((int) $d->format('o'), (int) $d->format('W'), 1);
            $end = $d->setISODate((int) $d->format('o'), (int) $d->format('W'), 7);

            $weeks[$weekKey] = [
                'key' => $weekKey,
                'label' => sprintf('Semaine %s (%s → %s)', $d->format('W'), $start->format('d/m/Y'), $end->format('d/m/Y')),
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ];
        }

        krsort($weeks);
        return array_slice(array_values($weeks), 0, max(1, $limit));
    }

    public static function weeklyBalances(int $limit = 12, string $status = 'all'): array
    {
        $filters = [];
        if (in_array($status, ['draft', 'validated'], true)) {
            $filters['status'] = $status;
        }

        $rows = self::filtered($filters, 5000);
        $groups = [];

        foreach ($rows as $row) {
            $date = (string) ($row['entry_date'] ?? '');
            if ($date === '') {
                continue;
            }

            try {
                $d = new DateTimeImmutable($date);
            } catch (\Throwable $exception) {
                continue;
            }

            $weekKey = sprintf('%s-W%s', $d->format('o'), $d->format('W'));
            if (!isset($groups[$weekKey])) {
                $start = $d->setISODate((int) $d->format('o'), (int) $d->format('W'), 1);
                $end = $d->setISODate((int) $d->format('o'), (int) $d->format('W'), 7);
                $groups[$weekKey] = [
                    'week_key' => $weekKey,
                    'week_label' => sprintf('S%s %s', $d->format('W'), $d->format('o')),
                    'start' => $start->format('Y-m-d'),
                    'end' => $end->format('Y-m-d'),
                    'income' => 0.0,
                    'expense' => 0.0,
                    'balance' => 0.0,
                    'count' => 0,
                ];
            }

            $amount = (float) ($row['amount'] ?? 0);
            $isIncome = ((string) ($row['entry_type'] ?? '')) === 'income';
            if ($isIncome) {
                $groups[$weekKey]['income'] += $amount;
            } else {
                $groups[$weekKey]['expense'] += $amount;
            }
            $groups[$weekKey]['balance'] = $groups[$weekKey]['income'] - $groups[$weekKey]['expense'];
            $groups[$weekKey]['count']++;
        }

        krsort($groups);
        return array_slice(array_values($groups), 0, max(1, $limit));
    }

    public static function accounts(): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->query('SELECT code, label, account_type FROM accounting_accounts WHERE is_active = 1 ORDER BY code ASC');
        return $stmt->fetchAll();
    }

    private static function buildFilterClauses(array $filters): array
    {
        $conditions = [];
        $params = [];

        $type = (string) ($filters['type'] ?? 'all');
        if (in_array($type, ['income', 'expense'], true)) {
            $conditions[] = 'entry_type = :entry_type';
            $params[':entry_type'] = $type;
        }

        $status = (string) ($filters['status'] ?? 'all');
        if (in_array($status, ['draft', 'validated'], true)) {
            $conditions[] = 'entry_status = :entry_status';
            $params[':entry_status'] = $status;
        }

        $paymentMethod = trim((string) ($filters['payment_method'] ?? 'all'));
        if (in_array($paymentMethod, ['transfer', 'cash', 'card', 'check', 'other'], true)) {
            $conditions[] = 'payment_method = :payment_method';
            $params[':payment_method'] = $paymentMethod;
        }

        $accountCode = trim((string) ($filters['account_code'] ?? 'all'));
        if ($accountCode !== '' && $accountCode !== 'all') {
            $conditions[] = 'account_code = :account_code';
            $params[':account_code'] = $accountCode;
        }

        $partnerTag = trim((string) ($filters['partner_tag'] ?? 'all'));
        if (in_array($partnerTag, ['classic', 'yellow', 'rex', 'mojito', 'seaton', 'none'], true)) {
            $conditions[] = 'partner_tag = :partner_tag';
            $params[':partner_tag'] = $partnerTag;
        }

        $week = trim((string) ($filters['week'] ?? ''));
        $range = self::weekRangeFromKey($week);
        if ($range !== null) {
            $conditions[] = 'entry_date >= :week_start';
            $conditions[] = 'entry_date <= :week_end';
            $params[':week_start'] = $range['start'];
            $params[':week_end'] = $range['end'];
        }

        return [$conditions, $params];
    }

    private static function weekRangeFromKey(string $weekKey): ?array
    {
        if (preg_match('/^(\d{4})-W(\d{2})$/', $weekKey, $matches) !== 1) {
            return null;
        }

        $year = (int) $matches[1];
        $week = (int) $matches[2];
        if ($week < 1 || $week > 53) {
            return null;
        }

        try {
            $monday = (new DateTimeImmutable())->setISODate($year, $week, 1);
            $sunday = (new DateTimeImmutable())->setISODate($year, $week, 7);
        } catch (\Throwable $exception) {
            return null;
        }

        return [
            'start' => $monday->format('Y-m-d'),
            'end' => $sunday->format('Y-m-d'),
        ];
    }
}
