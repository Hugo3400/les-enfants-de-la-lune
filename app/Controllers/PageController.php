<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

final class PageController
{
    public function about(): void
    {
        View::render('pages/about', [
            'title' => "L'association - Les Enfants de la Lune",
        ]);
    }
}
