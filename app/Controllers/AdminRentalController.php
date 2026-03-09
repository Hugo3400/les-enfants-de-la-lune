<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\View;
use App\Models\RentalModel;
use App\Models\MemberModel;

final class AdminRentalController
{
    public function index(): void
    {
        Auth::requirePermission('rentals');

        View::render('admin/rentals/index', [
            'title' => 'Locations - Administration',
            'rentals' => RentalModel::allWithAssignees(),
            'csrfToken' => Auth::csrfToken(),
            'pageStyles' => ['modules/rentals.css'],
        ], 'admin');
    }

    public function createForm(): void
    {
        Auth::requirePermission('rentals.create');

        View::render('admin/rentals/form', [
            'title' => 'Nouvelle location - Administration',
            'csrfToken' => Auth::csrfToken(),
            'pageStyles' => ['modules/rentals.css'],
            'rental' => [
                'id' => null,
                'title' => '',
                'location_label' => '',
                'price' => '0',
                'status' => 'available',
                'description' => '',
            ],
            'formAction' => '/admin/locations',
        ], 'admin');
    }

    public function store(): void
    {
        Auth::requirePermission('rentals.create');
        if (!$this->validateRequest()) {
            return;
        }

        $data = $this->sanitizeData();
        if (!$this->validateData($data)) {
            return;
        }

        RentalModel::create($data);
        Flash::set('success', 'Location ajoutée.');
        header('Location: /admin/locations');
    }

    public function editForm(int $id): void
    {
        Auth::requirePermission('rentals.edit');

        $rental = RentalModel::findById((int) $id);
        if (!$rental) {
            http_response_code(404);
            echo 'Location introuvable';
            return;
        }

        $assignee = RentalModel::currentAssignee($id);
        $history = RentalModel::assignmentHistory($id);

        View::render('admin/rentals/form', [
            'title' => 'Modifier location - Administration',
            'csrfToken' => Auth::csrfToken(),
            'pageStyles' => ['modules/rentals.css'],
            'rental' => $rental,
            'formAction' => '/admin/locations/' . $id . '/update',
            'members' => MemberModel::allActive(),
            'assignee' => $assignee,
            'history' => $history,
        ], 'admin');
    }

    public function update(int $id): void
    {
        Auth::requirePermission('rentals.edit');
        if (!$this->validateRequest()) {
            return;
        }

        $rentalId = (int) $id;
        if (!RentalModel::findById($rentalId)) {
            http_response_code(404);
            echo 'Location introuvable';
            return;
        }

        $data = $this->sanitizeData();
        if (!$this->validateData($data)) {
            return;
        }

        RentalModel::update($rentalId, $data);
        Flash::set('success', 'Location mise à jour.');
        header('Location: /admin/locations');
    }

    public function destroy(int $id): void
    {
        Auth::requirePermission('rentals.delete');
        if (!$this->validateRequest()) {
            return;
        }

        RentalModel::delete((int) $id);
        Flash::set('success', 'Location supprimée.');
        header('Location: /admin/locations');
    }

    public function assign(int $id): void
    {
        Auth::requirePermission('rentals.edit');
        if (!$this->validateRequest()) {
            return;
        }

        $rental = RentalModel::findById($id);
        if (!$rental) {
            http_response_code(404);
            echo 'Location introuvable';
            return;
        }

        $memberId = (int) ($_POST['member_id'] ?? 0);
        if ($memberId <= 0) {
            Flash::set('error', 'Veuillez sélectionner un membre.');
            header('Location: /admin/locations/' . $id . '/edit');
            return;
        }

        $member = MemberModel::findById($memberId);
        if (!$member) {
            Flash::set('error', 'Membre introuvable.');
            header('Location: /admin/locations/' . $id . '/edit');
            return;
        }

        $notes = trim((string) ($_POST['assignment_notes'] ?? ''));
        RentalModel::assign($id, $memberId, date('Y-m-d'), $notes ?: null);

        Flash::set('success', 'Location attribuée à ' . htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . '.');
        header('Location: /admin/locations/' . $id . '/edit');
    }

    public function release(int $id): void
    {
        Auth::requirePermission('rentals.edit');
        if (!$this->validateRequest()) {
            return;
        }

        $rental = RentalModel::findById($id);
        if (!$rental) {
            http_response_code(404);
            echo 'Location introuvable';
            return;
        }

        RentalModel::release($id, date('Y-m-d'));

        Flash::set('success', 'Location libérée.');
        header('Location: /admin/locations/' . $id . '/edit');
    }

    private function validateRequest(): bool
    {
        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            Flash::set('error', 'Session expirée. Merci de réessayer.');
            header('Location: /admin/locations');
            return false;
        }

        return true;
    }

    private function sanitizeData(): array
    {
        $status = trim((string) ($_POST['status'] ?? 'available'));
        if (!in_array($status, ['available', 'unavailable'], true)) {
            $status = 'available';
        }

        return [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'location_label' => trim((string) ($_POST['location_label'] ?? '')),
            'price' => (float) ($_POST['price'] ?? 0),
            'status' => $status,
            'description' => trim((string) ($_POST['description'] ?? '')),
        ];
    }

    private function validateData(array $data): bool
    {
        if ($data['title'] === '' || $data['location_label'] === '') {
            Flash::set('error', 'Le titre et le lieu sont obligatoires.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/admin/locations'));
            return false;
        }

        return true;
    }
}
