<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewFile   = BASE_PATH . '/app/Views/' . trim($view, '/') . '.php';
        $layoutFile = BASE_PATH . '/app/Views/layouts/' . $layout . '.php';

        if (!is_file($viewFile)) {
            ErrorHandler::internal('View introuvable: ' . $viewFile);
            return;
        }
        if (!is_file($layoutFile)) {
            ErrorHandler::internal('Layout introuvable: ' . $layoutFile);
            return;
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }
}
