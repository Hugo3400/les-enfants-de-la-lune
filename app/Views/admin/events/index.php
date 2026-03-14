<section class="admin-events-page">
  <?php $eventsCount = count($events ?? []); ?>
  <?php $visibleCount = 0; ?>
  <?php foreach (($events ?? []) as $ev): ?>
    <?php if ((int) ($ev['is_visible'] ?? 0) === 1): ?>
      <?php $visibleCount++; ?>
    <?php endif; ?>
  <?php endforeach; ?>

  <div class="card events-hero">
    <div class="section-head">
      <div>
        <h1>Événements</h1>
        <p>Gestion des événements affichés sur le bloc « À venir » de la page d'accueil.</p>
      </div>
      <div class="events-hero-actions">
        <a class="button-link" href="/admin/evenements/new">+ Nouvel événement</a>
      </div>
    </div>
    <div class="events-hero-meta">
      <span class="admin-badge"><?= $eventsCount ?> événement<?= $eventsCount > 1 ? 's' : '' ?></span>
      <span class="admin-badge"><?= $visibleCount ?> visible<?= $visibleCount > 1 ? 's' : '' ?></span>
      <span class="admin-badge"><?= $eventsCount - $visibleCount ?> masqué<?= ($eventsCount - $visibleCount) > 1 ? 's' : '' ?></span>
    </div>
  </div>

  <?php if (empty($events)): ?>
    <div class="card">
      <div class="admin-empty">
        <p>Aucun événement pour le moment.</p>
        <a class="button-link" href="/admin/evenements/new">Créer le premier</a>
      </div>
    </div>
  <?php else: ?>
    <article class="card events-table-card">
      <div class="section-head">
        <h2>Liste des événements</h2>
      </div>
      <div class="table-wrap events-table-wrap">
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
                <td><span class="events-order-pill"><?= (int) ($event['sort_order'] ?? 0) ?></span></td>
                <td><strong><?= htmlspecialchars((string) ($event['title'] ?? '')) ?></strong></td>
                <td class="events-description-col"><?= htmlspecialchars((string) ($event['description'] ?? '—')) ?></td>
                <td><?= htmlspecialchars((string) ($event['event_date'] ?? '—')) ?></td>
                <td><?= htmlspecialchars((string) ($event['event_time'] ?? '—')) ?></td>
                <td>
                  <?php if (!empty($event['registration_url'])): ?>
                    <a href="<?= htmlspecialchars((string) $event['registration_url']) ?>" target="_blank" rel="noopener">Lien externe</a>
                  <?php else: ?>
                    <span class="events-muted">Demande interne</span>
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
                    <form method="post" action="/admin/evenements/<?= (int) $event['id'] ?>/delete" onsubmit="return confirm('Supprimer cet événement ?')">
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
    </article>
  <?php endif; ?>
</section>
