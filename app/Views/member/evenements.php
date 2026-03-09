<section class="member-page">
  <div class="section-head">
    <h1><i class="fa-solid fa-calendar-days"></i> Événements</h1>
  </div>

  <?php
    $upcoming = [];
    $past = [];
    foreach ($events ?? [] as $event) {
        if (!empty($event['event_date']) && $event['event_date'] >= date('Y-m-d')) {
            $upcoming[] = $event;
        } else {
            $past[] = $event;
        }
    }
  ?>

  <?php if (!empty($upcoming)): ?>
    <h2 class="member-section-title">À venir</h2>
    <div class="member-events-grid">
      <?php foreach ($upcoming as $event): ?>
        <article class="card member-event-card">
          <div class="member-event-top">
            <div class="member-event-date-badge">
              <span class="event-day"><?= date('d', strtotime($event['event_date'])) ?></span>
              <span class="event-month"><?= strftime('%b', strtotime($event['event_date'])) ?: date('M', strtotime($event['event_date'])) ?></span>
            </div>
            <div>
              <h3><?= htmlspecialchars((string) ($event['title'] ?? '')) ?></h3>
              <?php if (!empty($event['event_time'])): ?>
                <span class="member-event-time"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($event['event_time']) ?></span>
              <?php endif; ?>
            </div>
          </div>
          <?php if (!empty($event['description'])): ?>
            <p class="member-event-desc"><?= htmlspecialchars((string) $event['description']) ?></p>
          <?php endif; ?>

          <div class="member-event-actions">
            <?php if (!empty($event['registration_url'])): ?>
              <a href="<?= htmlspecialchars((string) $event['registration_url']) ?>" target="_blank" rel="noopener" class="button-link">S'inscrire</a>
            <?php else: ?>
              <form method="post" action="/espace-membre/evenements/<?= (int) ($event['id'] ?? 0) ?>/inscription" style="margin:0;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? '')) ?>">
                <button type="submit" class="button-secondary">Demander une inscription</button>
              </form>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <article class="card member-empty-card">
      <div class="member-empty-icon"><i class="fa-regular fa-calendar-check"></i></div>
      <h2>Pas d'événement à venir</h2>
      <p>Aucun événement prévu pour le moment. Restez connecté(e) !</p>
    </article>
  <?php endif; ?>

  <?php if (!empty($past)): ?>
    <h2 class="member-section-title" style="margin-top:32px;">Passés</h2>
    <div class="member-events-grid past-events">
      <?php foreach (array_slice($past, 0, 6) as $event): ?>
        <article class="card member-event-card past">
          <div class="member-event-top">
            <div class="member-event-date-badge past">
              <span class="event-day"><?= date('d', strtotime($event['event_date'])) ?></span>
              <span class="event-month"><?= date('M', strtotime($event['event_date'])) ?></span>
            </div>
            <div>
              <h3><?= htmlspecialchars((string) ($event['title'] ?? '')) ?></h3>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
