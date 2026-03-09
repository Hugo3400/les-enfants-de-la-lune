<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;
use App\Models\PostModel;

final class BlogController
{
    public function index(): void
    {
        $selectedTheme = Request::str($_GET, 'theme');
        if ($selectedTheme !== '' && !array_key_exists($selectedTheme, PostModel::THEMES)) {
            $selectedTheme = '';
        }

        View::render('blog/index', [
            'title' => 'Actualités - Les Enfants de la Lune',
            'posts' => PostModel::allPublished($selectedTheme !== '' ? $selectedTheme : null),
            'themes' => PostModel::THEMES,
            'selectedTheme' => $selectedTheme,
            'themeCounts' => PostModel::publishedThemeCounts(),
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
