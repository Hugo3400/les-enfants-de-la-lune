<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Request;
use App\Core\View;
use App\Models\EventModel;
use App\Models\MemberModel;
use App\Models\PostModel;

final class MemberPortalController
{
    /* ─── Auth ─── */

    public function loginForm(): void
    {
        if (Auth::check() && Auth::member()) {
            header('Location: /espace-membre');
            return;
        }

        View::render('member/login', [
            'title' => 'Connexion — Espace membre',
            'csrfToken' => Auth::csrfToken(),
        ], 'member-auth');
    }

    public function login(): void
    {
        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            Flash::set('error', 'Session expirée. Merci de réessayer.');
            header('Location: /espace-membre/connexion');
            return;
        }

        $email = Request::email($_POST, 'email');
        $password = Request::str($_POST, 'password');

        if ($email === '' || $password === '') {
            Flash::set('error', 'Email et mot de passe sont obligatoires.');
            header('Location: /espace-membre/connexion');
            return;
        }

        if (!Auth::attempt($email, $password)) {
            Flash::set('error', 'Identifiants invalides.');
            header('Location: /espace-membre/connexion');
            return;
        }

        // Check member profile exists
        $member = Auth::member();
        if (!$member) {
            Flash::set('error', 'Votre compte n\'est pas lié à un profil membre. Contactez l\'association.');
            header('Location: /espace-membre/connexion');
            return;
        }

        if (($member['status'] ?? '') !== 'active') {
            Flash::set('error', 'Votre adhésion n\'est plus active. Contactez l\'association.');
            header('Location: /espace-membre/connexion');
            return;
        }

        Flash::set('success', 'Bienvenue, ' . htmlspecialchars($member['first_name'] ?? '') . ' !');
        header('Location: /espace-membre');
    }

    public function logout(): void
    {
        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            header('Location: /espace-membre');
            return;
        }

        Auth::logout();
        Flash::set('success', 'Vous êtes déconnecté(e).');
        header('Location: /espace-membre/connexion');
    }

    /* ─── Dashboard ─── */

    public function dashboard(): void
    {
        $member = Auth::requireMember();

        $activeRental = MemberModel::getActiveRental((int) $member['id']);
        $events = EventModel::allVisible();
        $latestPosts = PostModel::latestPublished(3);

        View::render('member/dashboard', [
            'title' => 'Mon espace — Les Enfants de la Lune',
            'member' => $member,
            'activeRental' => $activeRental,
            'events' => $events,
            'latestPosts' => $latestPosts,
            'csrfToken' => Auth::csrfToken(),
        ], 'member');
    }

    /* ─── Mon logement ─── */

    public function logement(): void
    {
        $member = Auth::requireMember();

        $rentals = MemberModel::getRentals((int) $member['id']);
        $activeRental = null;
        $history = [];

        foreach ($rentals as $r) {
            if (($r['status'] ?? '') === 'active' && !$activeRental) {
                $activeRental = $r;
            } else {
                $history[] = $r;
            }
        }

        View::render('member/logement', [
            'title' => 'Mon logement — Les Enfants de la Lune',
            'member' => $member,
            'activeRental' => $activeRental,
            'history' => $history,
            'csrfToken' => Auth::csrfToken(),
        ], 'member');
    }

    /* ─── Mon profil ─── */

    public function profil(): void
    {
        $member = Auth::requireMember();
        $user = Auth::user();

        View::render('member/profil', [
            'title' => 'Mon profil — Les Enfants de la Lune',
            'member' => $member,
            'user' => $user,
            'roles' => MemberModel::ROLES,
            'csrfToken' => Auth::csrfToken(),
        ], 'member');
    }

    /* ─── Événements ─── */

    public function evenements(): void
    {
        $member = Auth::requireMember();
        $events = EventModel::allVisible();

        View::render('member/evenements', [
            'title' => 'Événements — Les Enfants de la Lune',
            'member' => $member,
            'events' => $events,
            'csrfToken' => Auth::csrfToken(),
        ], 'member');
    }

    /* ─── Actualités ─── */

    public function actualites(): void
    {
        $member = Auth::requireMember();
        $posts = PostModel::allPublished();

        View::render('member/actualites', [
            'title' => 'Actualités — Les Enfants de la Lune',
            'member' => $member,
            'posts' => $posts,
            'csrfToken' => Auth::csrfToken(),
        ], 'member');
    }
}
