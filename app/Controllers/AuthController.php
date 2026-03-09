<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Request;
use App\Core\View;

final class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            header('Location: /admin');
            return;
        }

        View::render('auth/login', [
            'title' => 'Connexion admin - Les Enfants de la Lune',
            'csrfToken' => Auth::csrfToken(),
        ], 'auth');
    }

    public function login(): void
    {
        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            Flash::set('error', 'Session expirée. Merci de réessayer.');
            header('Location: /admin/login');
            return;
        }

        $email = Request::email($_POST, 'email');
        $password = Request::str($_POST, 'password');

        if ($email === '' || $password === '') {
            Flash::set('error', 'Email et mot de passe sont obligatoires.');
            header('Location: /admin/login');
            return;
        }

        if (!Auth::attempt($email, $password)) {
            Flash::set('error', 'Identifiants invalides.');
            header('Location: /admin/login');
            return;
        }

        Flash::set('success', 'Connexion réussie.');
        header('Location: /admin');
    }

    public function logout(): void
    {
        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            Flash::set('error', 'Session expirée.');
            header('Location: /admin');
            return;
        }

        Auth::logout();
        Flash::set('success', 'Déconnexion effectuée.');
        header('Location: /');
    }
}
