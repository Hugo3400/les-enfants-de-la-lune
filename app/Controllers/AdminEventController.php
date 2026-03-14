<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\View;
use App\Models\EventModel;

final class AdminEventController
{
    public function index(): void
    {
        Auth::requirePermission('events');

        View::render('admin/events/index', [
            'title' => 'Événements - Administration',
            'events' => EventModel::all(),
            'csrfToken' => Auth::csrfToken(),
            'pageStyles' => ['modules/events.css'],
        ], 'admin');
    }

    public function createForm(): void
    {
        Auth::requirePermission('events.create');

        View::render('admin/events/form', [
            'title' => 'Nouvel événement - Administration',
            'csrfToken' => Auth::csrfToken(),
            'pageStyles' => ['modules/events.css'],
            'event' => [
                'id' => null,
                'title' => '',
                'description' => '',
                'event_date' => '',
                'event_time' => '',
                'is_visible' => 1,
                'sort_order' => 0,
                'registration_url' => '',
            ],
            'formAction' => '/admin/evenements',
        ], 'admin');
    }

    public function store(): void
    {
        Auth::requirePermission('events.create');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            Flash::set('error', 'Jeton CSRF invalide.');
            header('Location: /admin/evenements/new');
            return;
        }

        $data = $this->sanitizeData();

        if ($data['title'] === '') {
            Flash::set('error', 'Le titre est obligatoire.');
            header('Location: /admin/evenements/new');
            return;
        }

        EventModel::create($data);
        Flash::set('success', 'Événement créé.');
        header('Location: /admin/evenements');
    }

    public function editForm(int $id): void
    {
        Auth::requirePermission('events.edit');

        $event = EventModel::findById((int) $id);
        if (!$event) {
            http_response_code(404);
            echo 'Événement introuvable';
            return;
        }

        View::render('admin/events/form', [
            'title' => 'Modifier événement - Administration',
            'csrfToken' => Auth::csrfToken(),
            'pageStyles' => ['modules/events.css'],
            'event' => $event,
            'formAction' => '/admin/evenements/' . (int) $event['id'] . '/update',
        ], 'admin');
    }

    public function update(int $id): void
    {
        Auth::requirePermission('events.edit');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            Flash::set('error', 'Jeton CSRF invalide.');
            header('Location: /admin/evenements/' . $id . '/edit');
            return;
        }

        $event = EventModel::findById((int) $id);
        if (!$event) {
            http_response_code(404);
            echo 'Événement introuvable';
            return;
        }

        $data = $this->sanitizeData();

        if ($data['title'] === '') {
            Flash::set('error', 'Le titre est obligatoire.');
            header('Location: /admin/evenements/' . (int) $id . '/edit');
            return;
        }

        EventModel::update((int) $id, $data);
        Flash::set('success', 'Événement mis à jour.');
        header('Location: /admin/evenements');
    }

    public function destroy(int $id): void
    {
        Auth::requirePermission('events.delete');

        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            Flash::set('error', 'Jeton CSRF invalide.');
            header('Location: /admin/evenements');
            return;
        }

        EventModel::delete((int) $id);
        Flash::set('success', 'Événement supprimé.');
        header('Location: /admin/evenements');
    }

    private function sanitizeData(): array
    {
        $registrationUrl = trim((string) ($_POST['registration_url'] ?? ''));
        if ($registrationUrl !== '' && !filter_var($registrationUrl, FILTER_VALIDATE_URL)) {
            $registrationUrl = '';
        }

        return [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'event_date' => trim((string) ($_POST['event_date'] ?? '')),
            'event_time' => trim((string) ($_POST['event_time'] ?? '')),
            'is_visible' => isset($_POST['is_visible']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'registration_url' => $registrationUrl,
        ];
    }
}
