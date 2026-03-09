<section class="admin-dashboard">
  <div class="section-head">
    <h1>Événements</h1>
    <a class="button-link" href="/admin/evenements/new">+ Nouvel événement</a>
  </div>
  <p class="admin-lead">Ces événements apparaissent dans le bloc « À venir » sur la page d'accueil.</p>

  <?php if (empty($events)): ?>
    <div class="card">
      <div class="admin-empty">
        <p>Aucun événement pour le moment.</p>
        <a class="button-link" href="/admin/evenements/new">Créer le premier</a>
      </div>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Ordre</th>
            <th>Titre</th>
            <th>Description</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Inscription</th>
            <th>Visible</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($events as $event): ?>
            <tr>
              <td><?= (int) ($event['sort_order'] ?? 0) ?></td>
              <td><strong><?= htmlspecialchars((string) ($event['title'] ?? '')) ?></strong></td>
              <td><?= htmlspecialchars((string) ($event['description'] ?? '—')) ?></td>
              <td><?= htmlspecialchars((string) ($event['event_date'] ?? '—')) ?></td>
              <td><?= htmlspecialchars((string) ($event['event_time'] ?? '—')) ?></td>
              <td>
                <?php if (!empty($event['registration_url'])): ?>
                  <a href="<?= htmlspecialchars((string) $event['registration_url']) ?>" target="_blank" rel="noopener">Lien externe</a>
                <?php else: ?>
                  <span style="color:#8a8a8a;">Demande interne</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ((int) ($event['is_visible'] ?? 0)): ?>
                  <span class="status-pill status-available">Oui</span>
                <?php else: ?>
                  <span class="status-pill status-unavailable">Non</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="actions-cell">
                  <a class="button-secondary" href="/admin/evenements/<?= (int) $event['id'] ?>/edit">Modifier</a>
                  <form method="post" action="/admin/evenements/<?= (int) $event['id'] ?>/delete"
                        onsubmit="return confirm('Supprimer cet événement ?')">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                    <button type="submit" class="link-danger">Supprimer</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
