<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Request;
use App\Core\View;
use App\Models\AccountingModel;

final class AdminAccountingController
{
    public function index(): void
    {
        Auth::requirePermission('accounting.view');

        $week = Request::str($_GET, 'week');
        $entryType = Request::oneOf($_GET, 'type', ['all', 'income', 'expense'], 'all');
        $status = Request::oneOf($_GET, 'status', ['all', 'draft', 'validated'], 'all');
        $paymentMethod = Request::oneOf($_GET, 'payment_method', ['all', 'transfer', 'cash', 'card', 'check', 'other'], 'all');
        $accountCode = Request::str($_GET, 'account_code', 'all');
        if ($accountCode === '') {
            $accountCode = 'all';
        }

        $filters = [
            'week' => $week,
            'type' => $entryType,
            'status' => $status,
            'payment_method' => $paymentMethod,
            'account_code' => $accountCode,
        ];

        $entries = AccountingModel::filtered($filters, 500);
        $running = 0.0;
        foreach ($entries as &$entry) {
            $amount = (float) ($entry['amount'] ?? 0);
            $isIncome = ((string) ($entry['entry_type'] ?? '')) === 'income';

            $entry['debit'] = $isIncome ? 0.0 : $amount;
            $entry['credit'] = $isIncome ? $amount : 0.0;
            $running += $isIncome ? $amount : -$amount;
            $entry['running_balance'] = $running;
        }
        unset($entry);

        $globalTotals = AccountingModel::totals();
        $periodTotals = AccountingModel::totalsFor($filters);
        $accounts = AccountingModel::accounts();

        View::render('admin/accounting/index', [
            'title' => 'Comptabilité - Administration',
            'entries' => $entries,
            'totals' => $globalTotals,
            'periodTotals' => $periodTotals,
            'weekOptions' => AccountingModel::weekOptions(),
            'weeklyBalances' => AccountingModel::weeklyBalances(12, $status),
            'accounts' => $accounts,
            'paymentMethods' => $this->paymentMethods(),
            'pageStyles' => ['modules/accounting.css'],
            'filters' => $filters,
            'csrfToken' => Auth::csrfToken(),
        ], 'admin');
    }

    public function store(): void
    {
        Auth::requirePermission('accounting.manage');
        if (!$this->validateRequest()) {
            return;
        }

        $data = $this->sanitizeData();
        if (!$this->validateData($data)) {
            return;
        }

        AccountingModel::create($data);
        Flash::set('success', 'Écriture comptable ajoutée.');
        header('Location: /admin/comptabilite');
    }

    public function setStatus(int $id): void
    {
        Auth::requirePermission('accounting.manage');
        if (!$this->validateRequest()) {
            return;
        }

        $status = Request::oneOf($_POST, 'entry_status', ['draft', 'validated'], '');
        if ($status === '') {
            Flash::set('error', 'Statut comptable invalide.');
            header('Location: /admin/comptabilite');
            return;
        }

        $entryId = (int) $id;
        $entry = AccountingModel::findById($entryId);
        if (!$entry) {
            Flash::set('error', 'Écriture introuvable.');
            header('Location: /admin/comptabilite');
            return;
        }

        AccountingModel::updateStatus($entryId, $status);
        Flash::set('success', $status === 'validated' ? 'Écriture validée.' : 'Écriture repassée en brouillon.');
        header('Location: /admin/comptabilite');
    }

    public function destroy(int $id): void
    {
        Auth::requirePermission('accounting.manage');
        if (!$this->validateRequest()) {
            return;
        }

        $deleted = AccountingModel::delete((int) $id);
        if ($deleted) {
            Flash::set('success', 'Écriture supprimée.');
        } else {
            Flash::set('error', 'Suppression impossible: l\'écriture est validée.');
        }
        header('Location: /admin/comptabilite');
    }

    private function validateRequest(): bool
    {
        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            Flash::set('error', 'Session expirée. Merci de réessayer.');
            header('Location: /admin/comptabilite');
            return false;
        }

        return true;
    }

    private function sanitizeData(): array
    {
        $entryType = Request::oneOf($_POST, 'entry_type', ['income', 'expense'], 'income');

        $entryDate = Request::str($_POST, 'entry_date');
        if ($entryDate === '') {
            $entryDate = date('Y-m-d');
        }

        $paymentMethod = Request::oneOf($_POST, 'payment_method', ['transfer', 'cash', 'card', 'check', 'other'], 'transfer');

        $entryStatus = Request::oneOf($_POST, 'entry_status', ['draft', 'validated'], 'draft');

        $accountCode = Request::str($_POST, 'account_code');
        if ($accountCode === '') {
            $accountCode = $entryType === 'income' ? '706' : '606';
        }

        return [
            'entry_type' => $entryType,
            'account_code' => $accountCode,
            'payment_method' => $paymentMethod,
            'reference' => Request::str($_POST, 'reference'),
            'entry_status' => $entryStatus,
            'label' => Request::str($_POST, 'label'),
            'amount' => Request::float($_POST, 'amount', 0),
            'entry_date' => $entryDate,
            'notes' => Request::str($_POST, 'notes'),
        ];
    }

    private function validateData(array $data): bool
    {
        if ($data['label'] === '' || $data['amount'] <= 0) {
            Flash::set('error', 'Le libellé et le montant (> 0) sont obligatoires.');
            header('Location: /admin/comptabilite');
            return false;
        }

        $validAccounts = array_map(static fn(array $account): string => (string) $account['code'], AccountingModel::accounts());
        if (!in_array((string) $data['account_code'], $validAccounts, true)) {
            Flash::set('error', 'Compte comptable invalide.');
            header('Location: /admin/comptabilite');
            return false;
        }

        return true;
    }

    private function paymentMethods(): array
    {
        return [
            'transfer' => 'Virement',
            'cash' => 'Espèces',
            'card' => 'Carte',
            'check' => 'Chèque',
            'other' => 'Autre',
        ];
    }
}
