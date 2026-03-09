<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\View;
use App\Models\UserModel;

final class AdminUserController
{
    public function index(): void
    {
        Auth::requirePermission('users');

        View::render('admin/users/index', [
            'title' => 'Comptes utilisateurs - Administration',
            'users' => UserModel::all(),
            'roles' => UserModel::ROLES,
            'csrfToken' => Auth::csrfToken(),
        ], 'admin');
    }

    public function createForm(): void
    {
        Auth::requirePermission('users');

        View::render('admin/users/form', [
            'title' => 'Nouveau compte - Administration',
            'editUser' => null,
            'roles' => UserModel::ROLES,
            'csrfToken' => Auth::csrfToken(),
        ], 'admin');
    }

    public function store(): void
    {
        Auth::requirePermission('users');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            Flash::set('error', 'Session expirée.');
            header('Location: /admin/utilisateurs');
            return;
        }

        $data = $this->sanitize();

        if ($data['name'] === '' || $data['email'] === '' || ($data['password'] ?? '') === '') {
            Flash::set('error', 'Le nom, l\'email et le mot de passe sont obligatoires.');
            header('Location: /admin/utilisateurs/new');
            return;
        }

        $existing = UserModel::findByEmail($data['email']);
        if ($existing) {
            Flash::set('error', 'Un compte existe déjà avec cette adresse email.');
            header('Location: /admin/utilisateurs/new');
            return;
        }

        UserModel::create($data);
        Flash::set('success', 'Compte créé avec succès.');
        header('Location: /admin/utilisateurs');
    }

    public function editForm(int $id): void
    {
        Auth::requirePermission('users');

        $user = UserModel::findById($id);
        if (!$user) {
            Flash::set('error', 'Compte introuvable.');
            header('Location: /admin/utilisateurs');
            return;
        }

        View::render('admin/users/form', [
            'title' => 'Modifier compte - Administration',
            'editUser' => $user,
            'roles' => UserModel::ROLES,
            'csrfToken' => Auth::csrfToken(),
        ], 'admin');
    }

    public function update(int $id): void
    {
        Auth::requirePermission('users');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            Flash::set('error', 'Session expirée.');
            header('Location: /admin/utilisateurs');
            return;
        }

        $user = UserModel::findById($id);
        if (!$user) {
            Flash::set('error', 'Compte introuvable.');
            header('Location: /admin/utilisateurs');
            return;
        }

        // On ne peut pas se retirer les droits admin soi-même
        $currentUser = Auth::user();
        if ($currentUser && (int) $currentUser['id'] === $id) {
            $_POST['role'] = $user['role'];
            $_POST['is_active'] = '1';
        }

        $data = $this->sanitize();

        if ($data['name'] === '' || $data['email'] === '') {
            Flash::set('error', 'Le nom et l\'email sont obligatoires.');
            header('Location: /admin/utilisateurs/' . $id . '/edit');
            return;
        }

        UserModel::update($id, $data);
        Flash::set('success', 'Compte mis à jour.');
        header('Location: /admin/utilisateurs');
    }

    public function destroy(int $id): void
    {
        Auth::requirePermission('users');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            header('Location: /admin/utilisateurs');
            return;
        }

        // On ne peut pas supprimer son propre compte
        $currentUser = Auth::user();
        if ($currentUser && (int) $currentUser['id'] === $id) {
            Flash::set('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            header('Location: /admin/utilisateurs');
            return;
        }

        UserModel::delete($id);
        Flash::set('success', 'Compte supprimé.');
        header('Location: /admin/utilisateurs');
    }

    private function sanitize(): array
    {
        $data = [
            'name'      => trim((string) ($_POST['name'] ?? '')),
            'email'     => mb_strtolower(trim((string) ($_POST['email'] ?? ''))),
            'role'      => trim((string) ($_POST['role'] ?? 'member')),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        $password = trim((string) ($_POST['password'] ?? ''));
        if ($password !== '') {
            $data['password'] = $password;
        }

        return $data;
    }
}
