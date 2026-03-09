<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Request;
use App\Core\View;
use App\Models\ContactMessageModel;

final class ContactController
{
    public function index(): void
    {
        View::render('contact/index', [
            'title' => 'Contact - Les Enfants de la Lune',
            'csrfToken' => Auth::csrfToken(),
        ]);
    }

    public function submit(): void
    {
        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            Flash::set('error', 'Session expirée. Merci de renvoyer le formulaire.');
            header('Location: /contact');
            return;
        }

        $name = Request::str($_POST, 'name');
        $email = Request::email($_POST, 'email');
        $category = Request::str($_POST, 'category', 'general');
        $subject = Request::str($_POST, 'subject');
        $message = Request::str($_POST, 'message');

        $allowedCategories = ['aide', 'benevole', 'partenariat', 'general'];
        if (!in_array($category, $allowedCategories, true)) {
            $category = 'general';
        }

        if ($name === '' || $email === '' || $subject === '' || $message === '') {
            Flash::set('error', 'Tous les champs sont obligatoires.');
            header('Location: /contact');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('error', 'Adresse email invalide.');
            header('Location: /contact');
            return;
        }

        ContactMessageModel::create([
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'category' => $category,
            'message' => $message,
        ]);

        header('Location: /contact/merci');
    }

    public function success(): void
    {
        View::render('contact/success', [
            'title' => 'Message envoyé - Les Enfants de la Lune',
        ]);
    }
}
