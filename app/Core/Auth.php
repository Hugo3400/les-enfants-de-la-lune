<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\UserModel;

final class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user']);
    }

    public static function user(): ?array
    {
        return self::check() ? $_SESSION['auth_user'] : null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = UserModel::findByEmail($email);
        if (!$user) {
            return false;
        }

        if (((int) ($user['is_active'] ?? 1)) === 0) {
            return false;
        }

        $hash = (string) ($user['password_hash'] ?? '');
        if (!password_verify($password, $hash)) {
            return false;
        }

        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'role' => (string) ($user['role'] ?? 'admin'),
        ];

        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['auth_user']);
    }

    /* ─── Member portal helpers ─── */

    /** Get the member profile linked to the currently authenticated user */
    public static function member(): ?array
    {
        $user = self::user();
        if (!$user) {
            return null;
        }
        return \App\Models\MemberModel::findByUserId((int) $user['id']);
    }

    /** Require a linked member profile — redirect to login or error otherwise */
    public static function requireMember(): array
    {
        if (!self::check()) {
            Flash::set('error', 'Veuillez vous connecter pour accéder à votre espace membre.');
            header('Location: /espace-membre/connexion');
            exit;
        }

        $member = self::member();
        if (!$member) {
            Flash::set('error', 'Votre compte n\'est pas lié à un profil membre. Contactez l\'association.');
            header('Location: /espace-membre/connexion');
            exit;
        }

        if (($member['status'] ?? '') !== 'active') {
            Flash::set('error', 'Votre adhésion n\'est plus active. Contactez l\'association.');
            header('Location: /espace-membre/connexion');
            exit;
        }

        return $member;
    }

    public static function requireAuth(): void
    {
        if (self::check()) {
            return;
        }

        Flash::set('error', 'Veuillez vous connecter pour accéder à l\'administration.');
        header('Location: /admin/login');
        exit;
    }

    public static function role(): string
    {
        $user = self::user();
        if ($user && !empty($user['role'])) {
            return (string) $user['role'];
        }

        // Session created before role system — fetch from DB and cache
        if ($user && !empty($user['id'])) {
            $dbUser = UserModel::findById((int) $user['id']);
            $role = (string) ($dbUser['role'] ?? 'admin');
            $_SESSION['auth_user']['role'] = $role;
            return $role;
        }

        return 'member';
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function can(string $permission): bool
    {
        if (!self::check()) {
            return false;
        }
        return UserModel::hasPermission(self::role(), $permission);
    }

    public static function requirePermission(string $permission): void
    {
        self::requireAuth();
        if (self::can($permission)) {
            return;
        }
        Flash::set('error', 'Vous n\'avez pas les droits nécessaires pour cette action.');
        header('Location: /admin');
        exit;
    }

    public static function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['csrf_token'];
    }

    public static function validateCsrf(?string $token): bool
    {
        if (!isset($_SESSION['csrf_token']) || !is_string($token)) {
            return false;
        }

        return hash_equals((string) $_SESSION['csrf_token'], $token);
    }
}
