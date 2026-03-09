<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\PostModel;

final class BlogController
{
    public function index(): void
    {
        View::render('blog/index', [
            'title' => 'Actualités - Les Enfants de la Lune',
            'posts' => PostModel::allPublished(),
        ]);
    }

    public function show(string $slug): void
    {
        $post = PostModel::findPublishedBySlug($slug);
        if (!$post) {
            http_response_code(404);
            echo 'Article introuvable';
            return;
        }

        View::render('blog/show', [
            'title' => $post['title'] . ' - Les Enfants de la Lune',
            'post' => $post,
        ]);
    }
}
