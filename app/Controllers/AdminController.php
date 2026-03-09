<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\View;
use App\Models\AccountingModel;
use App\Models\ContactMessageModel;
use App\Models\MemberModel;
use App\Models\PostModel;
use App\Models\RentalModel;

final class AdminController
{
    public function dashboard(): void
    {
        Auth::requirePermission('dashboard');

        $pdo = Database::connection();
        $postsCount = (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
        $publishedCount = (int) $pdo->query('SELECT COUNT(*) FROM posts WHERE is_published = 1')->fetchColumn();
        $draftCount = (int) $pdo->query('SELECT COUNT(*) FROM posts WHERE is_published = 0')->fetchColumn();
        $messagesCount = ContactMessageModel::countAll();
        $rentalsAvailable = RentalModel::countByStatus('available');
        $rentalsUnavailable = RentalModel::countByStatus('unavailable');
        $totals = AccountingModel::totals();
        $membersCount = MemberModel::count();
        $membersActive = MemberModel::countActive();

        View::render('admin/dashboard', [
            'title' => 'Administration - Les Enfants de la Lune',
            'postsCount' => $postsCount,
            'publishedCount' => $publishedCount,
            'draftCount' => $draftCount,
            'messagesCount' => $messagesCount,
            'rentalsAvailable' => $rentalsAvailable,
            'rentalsUnavailable' => $rentalsUnavailable,
            'accountingBalance' => (float) ($totals['balance'] ?? 0),
            'membersCount' => $membersCount,
            'membersActive' => $membersActive,
            'latestPosts' => PostModel::allAdmin(),
            'latestMessages' => ContactMessageModel::latest(5),
            'csrfToken' => Auth::csrfToken(),
        ], 'admin');
    }

    public function articles(): void
    {
        Auth::requirePermission('articles');

        View::render('admin/articles', [
            'title' => 'Articles - Administration',
            'posts' => PostModel::allAdmin(),
            'csrfToken' => Auth::csrfToken(),
            'pageStyles' => ['modules/articles.css'],
        ], 'admin');
    }

    public function messages(): void
    {
        Auth::requirePermission('messages');

        View::render('admin/messages', [
            'title' => 'Messages - Administration',
            'messages' => ContactMessageModel::latest(50),
            'csrfToken' => Auth::csrfToken(),
        ], 'admin');
    }

    public function markMessageRead(int $id): void
    {
        Auth::requirePermission('messages.read');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            header('Location: /admin/messages');
            return;
        }

        ContactMessageModel::markAsRead($id);
        header('Location: /admin/messages');
    }

    public function markAllMessagesRead(): void
    {
        Auth::requirePermission('messages.manage');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            header('Location: /admin/messages');
            return;
        }

        ContactMessageModel::markAllAsRead();
        Flash::set('success', 'Tous les messages ont été marqués comme lus.');
        header('Location: /admin/messages');
    }
}
