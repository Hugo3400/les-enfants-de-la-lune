<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');

$isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
    || ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;

    $relative = substr($class, strlen($prefix)); // Core\Router
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) require $file;
});

$router = new App\Core\Router();
$db = App\Core\Database::connection();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com data:; img-src 'self' data: https:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header('Cross-Origin-Opener-Policy: same-origin');
header('Cross-Origin-Resource-Policy: same-site');
header('X-Permitted-Cross-Domain-Policies: none');

if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

set_exception_handler(static function (\Throwable $exception): void {
    App\Core\ErrorHandler::internal($exception);
});

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error === null) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (!in_array((int) ($error['type'] ?? 0), $fatalTypes, true)) {
        return;
    }

    App\Core\ErrorHandler::internal(
        sprintf('%s in %s:%d', (string) ($error['message'] ?? ''), (string) ($error['file'] ?? ''), (int) ($error['line'] ?? 0))
    );
});

$router->get('/', 'App\\Controllers\\HomeController@index');
$router->get('/a-propos', 'App\\Controllers\\PageController@about');

$router->get('/contact', 'App\\Controllers\\ContactController@index');
$router->post('/contact', 'App\\Controllers\\ContactController@submit');
$router->get('/contact/merci', 'App\\Controllers\\ContactController@success');

$router->get('/actualites', 'App\\Controllers\\BlogController@index');
$router->get('/actualites/{slug}', 'App\\Controllers\\BlogController@show');
$router->get('/locations', 'App\\Controllers\\RentalController@index');

$router->get('/admin/login', 'App\\Controllers\\AuthController@loginForm');
$router->post('/admin/login', 'App\\Controllers\\AuthController@login');
$router->post('/admin/logout', 'App\\Controllers\\AuthController@logout');

// Espace membre
$router->get('/espace-membre/connexion', 'App\\Controllers\\MemberPortalController@loginForm');
$router->post('/espace-membre/connexion', 'App\\Controllers\\MemberPortalController@login');
$router->post('/espace-membre/deconnexion', 'App\\Controllers\\MemberPortalController@logout');
$router->get('/espace-membre', 'App\\Controllers\\MemberPortalController@dashboard');
$router->get('/espace-membre/logement', 'App\\Controllers\\MemberPortalController@logement');
$router->get('/espace-membre/profil', 'App\\Controllers\\MemberPortalController@profil');
$router->get('/espace-membre/evenements', 'App\\Controllers\\MemberPortalController@evenements');
$router->post('/espace-membre/evenements/{id}/inscription', 'App\\Controllers\\MemberPortalController@registerEvent');
$router->get('/espace-membre/actualites', 'App\\Controllers\\MemberPortalController@actualites');

$router->get('/admin', 'App\\Controllers\\AdminController@dashboard');
$router->get('/admin/articles', 'App\\Controllers\\AdminController@articles');
$router->get('/admin/messages', 'App\\Controllers\\AdminController@messages');
$router->post('/admin/messages/{id}/read', 'App\\Controllers\\AdminController@markMessageRead');
$router->post('/admin/messages/read-all', 'App\\Controllers\\AdminController@markAllMessagesRead');
$router->get('/admin/locations', 'App\\Controllers\\AdminRentalController@index');
$router->get('/admin/locations/new', 'App\\Controllers\\AdminRentalController@createForm');
$router->post('/admin/locations', 'App\\Controllers\\AdminRentalController@store');
$router->get('/admin/locations/{id}/edit', 'App\\Controllers\\AdminRentalController@editForm');
$router->post('/admin/locations/{id}/update', 'App\\Controllers\\AdminRentalController@update');
$router->post('/admin/locations/{id}/delete', 'App\\Controllers\\AdminRentalController@destroy');
$router->post('/admin/locations/{id}/assign', 'App\\Controllers\\AdminRentalController@assign');
$router->post('/admin/locations/{id}/release', 'App\\Controllers\\AdminRentalController@release');

$router->get('/admin/comptabilite', 'App\\Controllers\\AdminAccountingController@index');
$router->get('/admin/comptabilite/export.csv', 'App\\Controllers\\AdminAccountingController@exportCsv');
$router->post('/admin/comptabilite', 'App\\Controllers\\AdminAccountingController@store');
$router->post('/admin/comptabilite/{id}/status', 'App\\Controllers\\AdminAccountingController@setStatus');
$router->post('/admin/comptabilite/{id}/delete', 'App\\Controllers\\AdminAccountingController@destroy');
$router->get('/admin/articles/new', 'App\\Controllers\\AdminPostController@createForm');
$router->post('/admin/articles', 'App\\Controllers\\AdminPostController@store');
$router->get('/admin/articles/{id}/edit', 'App\\Controllers\\AdminPostController@editForm');
$router->post('/admin/articles/{id}/update', 'App\\Controllers\\AdminPostController@update');
$router->post('/admin/articles/{id}/delete', 'App\\Controllers\\AdminPostController@destroy');

$router->get('/admin/evenements', 'App\\Controllers\\AdminEventController@index');
$router->get('/admin/evenements/new', 'App\\Controllers\\AdminEventController@createForm');
$router->post('/admin/evenements', 'App\\Controllers\\AdminEventController@store');
$router->get('/admin/evenements/{id}/edit', 'App\\Controllers\\AdminEventController@editForm');
$router->post('/admin/evenements/{id}/update', 'App\\Controllers\\AdminEventController@update');
$router->post('/admin/evenements/{id}/delete', 'App\\Controllers\\AdminEventController@destroy');

$router->get('/admin/membres', 'App\\Controllers\\AdminMemberController@index');
$router->get('/admin/membres/new', 'App\\Controllers\\AdminMemberController@createForm');
$router->post('/admin/membres', 'App\\Controllers\\AdminMemberController@store');
$router->get('/admin/membres/{id}/edit', 'App\\Controllers\\AdminMemberController@editForm');
$router->post('/admin/membres/{id}/update', 'App\\Controllers\\AdminMemberController@update');
$router->post('/admin/membres/{id}/delete', 'App\\Controllers\\AdminMemberController@destroy');
$router->post('/admin/membres/{id}/assign-rental', 'App\\Controllers\\AdminMemberController@assignRental');
$router->post('/admin/membres/{id}/release-rental', 'App\\Controllers\\AdminMemberController@releaseRental');

$router->get('/admin/utilisateurs', 'App\\Controllers\\AdminUserController@index');
$router->get('/admin/utilisateurs/new', 'App\\Controllers\\AdminUserController@createForm');
$router->post('/admin/utilisateurs', 'App\\Controllers\\AdminUserController@store');
$router->get('/admin/utilisateurs/{id}/edit', 'App\\Controllers\\AdminUserController@editForm');
$router->post('/admin/utilisateurs/{id}/update', 'App\\Controllers\\AdminUserController@update');
$router->post('/admin/utilisateurs/{id}/delete', 'App\\Controllers\\AdminUserController@destroy');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

$router->dispatch($method, $uri);
