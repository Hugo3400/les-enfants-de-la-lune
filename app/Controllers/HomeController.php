<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\PostModel;
use App\Models\RentalModel;
use App\Models\EventModel;

final class HomeController
{
    public function index(): void
    {
        View::render('home/index', [
            'title' => "Accueil - Les Enfants de la Lune",
            'heroTitle' => "Les Enfants de la Lune",
            'heroText' => "Association d'entraide à Blaine County : accompagnement humain, actions concrètes et soutien local.",
            'posts' => PostModel::latestPublished(3),
            'availableRentals' => RentalModel::allAvailable(),
            'upcomingEvents' => EventModel::allVisible(),
        ]);
    }
}
