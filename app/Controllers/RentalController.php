<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\RentalModel;

final class RentalController
{
    public function index(): void
    {
        View::render('rentals/index', [
            'title' => 'Locations - Les Enfants de la Lune',
            'rentals' => RentalModel::all(),
        ]);
    }
}
