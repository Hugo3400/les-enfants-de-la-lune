<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Request;
use App\Core\View;
use App\Models\MemberModel;
use App\Models\RentalModel;
use App\Models\UserModel;

final class AdminMemberController
{
    public function index(): void
    {
        Auth::requirePermission('members');

        $filters = [
            'q' => Request::str($_GET, 'q'),
            'member_role' => Request::oneOf($_GET, 'member_role', array_merge(['all'], array_keys(MemberModel::ROLES)), 'all'),
            'account_role' => Request::oneOf($_GET, 'account_role', ['all', 'privileged', 'none', 'webmaster', 'admin', 'moderator', 'treasurer', 'editor', 'member'], 'all'),
            'housing' => Request::oneOf($_GET, 'housing', ['all', 'assigned', 'unassigned'], 'all'),
            'paye' => Request::oneOf($_GET, 'paye', ['all', 'oui', 'non', 'en_cours'], 'all'),
        ];

        $sort = Request::oneOf(
            $_GET,
            'sort',
            ['name_asc', 'name_desc', 'joined_desc', 'joined_asc', 'member_role_asc', 'member_role_desc', 'account_role_asc', 'account_role_desc', 'housing_asc', 'housing_desc'],
            'name_asc'
        );

        View::render('admin/members/index', [
            'title' => 'Membres - Administration',
            'members' => MemberModel::allWithActiveRentals($filters, $sort),
            'roles' => MemberModel::ROLES,
            'statuses' => MemberModel::STATUSES,
            'userRoles' => UserModel::ROLES,
            'filters' => $filters,
            'sort' => $sort,
            'csrfToken' => Auth::csrfToken(),
        ], 'admin');
    }

    public function createForm(): void
    {
        Auth::requirePermission('members');

        View::render('admin/members/form', [
            'title' => 'Nouveau membre - Administration',
            'member' => null,
            'roles' => MemberModel::ROLES,
            'statuses' => MemberModel::STATUSES,
            'users' => UserModel::all(),
            'csrfToken' => Auth::csrfToken(),
        ], 'admin');
    }

    public function store(): void
    {
        Auth::requirePermission('members');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            Flash::set('error', 'Session expirée.');
            header('Location: /admin/membres');
            return;
        }

        $data = $this->sanitize();

        if ($data['first_name'] === '' || $data['last_name'] === '') {
            Flash::set('error', 'Le prénom et le nom sont obligatoires.');
            header('Location: /admin/membres/new');
            return;
        }

        MemberModel::create($data);
        Flash::set('success', 'Membre ajouté avec succès.');
        header('Location: /admin/membres');
    }

    public function editForm(int $id): void
    {
        Auth::requirePermission('members');

        $member = MemberModel::findById($id);
        if (!$member) {
            Flash::set('error', 'Membre introuvable.');
            header('Location: /admin/membres');
            return;
        }

        View::render('admin/members/form', [
            'title' => 'Modifier membre - Administration',
            'member' => $member,
            'roles' => MemberModel::ROLES,
            'statuses' => MemberModel::STATUSES,
            'users' => UserModel::all(),
            'rentals' => RentalModel::allAvailable(),
            'activeRental' => MemberModel::getActiveRental($id),
            'rentalHistory' => MemberModel::getRentals($id),
            'csrfToken' => Auth::csrfToken(),
        ], 'admin');
    }

    public function update(int $id): void
    {
        Auth::requirePermission('members');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            Flash::set('error', 'Session expirée.');
            header('Location: /admin/membres');
            return;
        }

        $member = MemberModel::findById($id);
        if (!$member) {
            Flash::set('error', 'Membre introuvable.');
            header('Location: /admin/membres');
            return;
        }

        $data = $this->sanitize();

        if ($data['first_name'] === '' || $data['last_name'] === '') {
            Flash::set('error', 'Le prénom et le nom sont obligatoires.');
            header('Location: /admin/membres/' . $id . '/edit');
            return;
        }

        MemberModel::update($id, $data);
        Flash::set('success', 'Membre mis à jour.');
        header('Location: /admin/membres');
    }

    public function destroy(int $id): void
    {
        Auth::requirePermission('members');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            header('Location: /admin/membres');
            return;
        }

        MemberModel::delete($id);
        Flash::set('success', 'Membre supprimé.');
        header('Location: /admin/membres');
    }

    public function assignRental(int $id): void
    {
        Auth::requirePermission('members');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            header('Location: /admin/membres/' . $id . '/edit');
            return;
        }

        $rentalId = (int) ($_POST['rental_id'] ?? 0);
        $assignedAt = trim((string) ($_POST['assigned_at'] ?? date('Y-m-d')));
        $leaseDurationValue = (int) ($_POST['lease_duration_value'] ?? 0);
        $leaseDurationUnit = trim((string) ($_POST['lease_duration_unit'] ?? 'month'));
        $notes = trim((string) ($_POST['rental_notes'] ?? ''));

        if ($rentalId <= 0) {
            Flash::set('error', 'Veuillez sélectionner un logement.');
            header('Location: /admin/membres/' . $id . '/edit');
            return;
        }

        if ($leaseDurationValue <= 0) {
            Flash::set('error', 'Veuillez saisir une durée de bail valide.');
            header('Location: /admin/membres/' . $id . '/edit');
            return;
        }

        if (!in_array($leaseDurationUnit, ['week', 'month', 'year'], true)) {
            $leaseDurationUnit = 'month';
        }

        if ($leaseDurationUnit === 'week' && $leaseDurationValue > 520) {
            Flash::set('error', 'La duree en semaines ne peut pas depasser 520.');
            header('Location: /admin/membres/' . $id . '/edit');
            return;
        }

        if ($leaseDurationUnit === 'month' && $leaseDurationValue > 120) {
            Flash::set('error', 'La duree en mois ne peut pas depasser 120.');
            header('Location: /admin/membres/' . $id . '/edit');
            return;
        }

        if ($leaseDurationUnit === 'year' && $leaseDurationValue > 10) {
            Flash::set('error', 'La duree en annees ne peut pas depasser 10.');
            header('Location: /admin/membres/' . $id . '/edit');
            return;
        }

        MemberModel::assignRental(
            $id,
            $rentalId,
            $assignedAt,
            $leaseDurationValue,
            $leaseDurationUnit,
            $notes ?: null
        );
        Flash::set('success', 'Logement attribué avec succès.');
        header('Location: /admin/membres/' . $id . '/edit');
    }

    public function releaseRental(int $id): void
    {
        Auth::requirePermission('members');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            header('Location: /admin/membres/' . $id . '/edit');
            return;
        }

        $assignmentId = (int) ($_POST['assignment_id'] ?? 0);
        if ($assignmentId > 0) {
            MemberModel::releaseRental($assignmentId, date('Y-m-d'));
            Flash::set('success', 'Logement libéré.');
        }

        header('Location: /admin/membres/' . $id . '/edit');
    }

    private function sanitize(): array
    {
        return [
            'first_name'           => trim((string) ($_POST['first_name'] ?? '')),
            'last_name'            => trim((string) ($_POST['last_name'] ?? '')),
            'email'                => mb_strtolower(trim((string) ($_POST['email'] ?? ''))),
            'phone'                => trim((string) ($_POST['phone'] ?? '')),
            'role'                 => trim((string) ($_POST['role'] ?? 'membre')),
            'status'               => trim((string) ($_POST['status'] ?? 'active')),
            'user_id'              => trim((string) ($_POST['user_id'] ?? '')),
            'joined_at'            => trim((string) ($_POST['joined_at'] ?? '')),
            'notes'                => trim((string) ($_POST['notes'] ?? '')),
            'rib'                  => trim((string) ($_POST['rib'] ?? '')),
            'recensement_bc'       => trim((string) ($_POST['recensement_bc'] ?? '')),
            'carte'                => trim((string) ($_POST['carte'] ?? '')),
            'carte_validite'       => trim((string) ($_POST['carte_validite'] ?? '')),
            'situation'            => trim((string) ($_POST['situation'] ?? '')),
            'rdv_situation'        => trim((string) ($_POST['rdv_situation'] ?? '')),
            'paye'                 => trim((string) ($_POST['paye'] ?? '')),
            'coupon_classic_bikes' => max(0, (int) ($_POST['coupon_classic_bikes'] ?? 0)),
            'coupon_seaton_sand'   => max(0, (int) ($_POST['coupon_seaton_sand'] ?? 0)),
            'coupon_rex_dinner'    => max(0, (int) ($_POST['coupon_rex_dinner'] ?? 0)),
            'coupon_yellow_jack'   => max(0, (int) ($_POST['coupon_yellow_jack'] ?? 0)),
            'coupon_mojito'        => max(0, (int) ($_POST['coupon_mojito'] ?? 0)),
        ];
    }
}
