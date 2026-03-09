<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\View;
use App\Models\PostModel;

final class AdminPostController
{
    public function createForm(): void
    {
        Auth::requirePermission('articles.create');

        View::render('admin/posts/form', [
            'title' => 'Nouvel article - Administration',
            'csrfToken' => Auth::csrfToken(),
            'pageStyles' => ['modules/articles.css'],
            'post' => [
                'id' => null,
                'title' => '',
                'slug' => '',
                'excerpt' => '',
                'content' => '',
                'is_published' => 1,
            ],
            'formAction' => '/admin/articles',
        ], 'admin');
    }

    public function store(): void
    {
        Auth::requirePermission('articles.create');
        if (!$this->validateRequest()) {
            return;
        }

        $data = $this->sanitizePostData();
        if (!$this->validateData($data)) {
            return;
        }

        PostModel::create($data);
        Flash::set('success', 'Article créé.');
        header('Location: /admin');
    }

    public function editForm(string $id): void
    {
        Auth::requirePermission('articles.edit');

        $post = PostModel::findById((int) $id);
        if (!$post) {
            http_response_code(404);
            echo 'Article introuvable';
            return;
        }

        View::render('admin/posts/form', [
            'title' => 'Modifier article - Administration',
            'csrfToken' => Auth::csrfToken(),
            'pageStyles' => ['modules/articles.css'],
            'post' => $post,
            'formAction' => '/admin/articles/' . (int) $post['id'] . '/update',
        ], 'admin');
    }

    public function update(string $id): void
    {
        Auth::requirePermission('articles.edit');
        if (!$this->validateRequest()) {
            return;
        }

        $postId = (int) $id;
        if (!PostModel::findById($postId)) {
            http_response_code(404);
            echo 'Article introuvable';
            return;
        }

        $data = $this->sanitizePostData();
        if (!$this->validateData($data)) {
            return;
        }

        PostModel::update($postId, $data);
        Flash::set('success', 'Article mis à jour.');
        header('Location: /admin');
    }

    public function destroy(string $id): void
    {
        Auth::requirePermission('articles.delete');
        if (!$this->validateRequest()) {
            return;
        }

        PostModel::delete((int) $id);
        Flash::set('success', 'Article supprimé.');
        header('Location: /admin');
    }

    private function validateRequest(): bool
    {
        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            Flash::set('error', 'Session expirée. Merci de réessayer.');
            header('Location: /admin');
            return false;
        }

        return true;
    }

    private function sanitizePostData(): array
    {
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));

        if ($slug === '' && $title !== '') {
            $slug = $this->slugify($title);
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];
    }

    private function validateData(array $data): bool
    {
        if ($data['title'] === '' || $data['slug'] === '' || $data['content'] === '') {
            Flash::set('error', 'Titre, slug et contenu sont obligatoires.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/admin'));
            return false;
        }

        return true;
    }

    private function slugify(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text) ?? '';
        return trim($text, '-');
    }
}
