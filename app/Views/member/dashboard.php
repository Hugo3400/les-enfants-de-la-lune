<?php
  $firstName = htmlspecialchars((string) ($member['first_name'] ?? ''));
  $lastName  = htmlspecialchars((string) ($member['last_name'] ?? ''));
  $roleName  = \App\Models\MemberModel::ROLES[$member['role'] ?? 'membre'] ?? 'Membre';
  $joinedAt  = $member['joined_at'] ?? null;
?>

<section class="member-dashboard">
  <div class="member-welcome-card card">
    <div class="member-welcome-text">
      <h1>Bonjour, <?= $firstName ?> <i class="fa-regular fa-hand" style="font-size:.85em;"></i></h1>
      <p class="member-welcome-sub"><?= $roleName ?> <?php if ($joinedAt): ?>· Membre depuis le <?= date('d/m/Y', strtotime($joinedAt)) ?><?php endif; ?></p>
    </div>
    <form method="post" action="/espace-membre/deconnexion" style="margin:0;">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
      <button type="submit" class="button-secondary" style="font-size:.85rem;">Se déconnecter</button>
    </form>
  </div>

  <div class="member-grid">
    <!-- Logement -->
    <article class="card member-card">
      <div class="member-card-icon icon-home"><i class="fa-solid fa-house"></i></div>
      <h2>Mon logement</h2>
      <?php if ($activeRental): ?>
        <div class="member-rental-info">
          <strong><?= htmlspecialchars((string) ($activeRental['title'] ?? '')) ?></strong>
          <span class="member-tag active">Attribué</span>
        </div>
        <p class="member-rental-detail"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars((string) ($activeRental['location_label'] ?? '')) ?></p>
        <p class="member-rental-detail"><i class="fa-regular fa-calendar"></i> Depuis le <?= date('d/m/Y', strtotime($activeRental['assigned_at'])) ?></p>
      <?php else: ?>
        <p class="member-empty-state">Aucun logement attribué pour le moment.</p>
      <?php endif; ?>
      <a href="/espace-membre/logement" class="member-card-link">Voir les détails →</a>
    </article>

    <!-- Événements à venir -->
    <article class="card member-card">
      <div class="member-card-icon icon-calendar"><i class="fa-regular fa-calendar-check"></i></div>
      <h2>Prochains événements</h2>
      <?php
        $upcoming = array_filter($events ?? [], function ($e) {
            return !empty($e['event_date']) && $e['event_date'] >= date('Y-m-d');
        });
        $upcoming = array_slice($upcoming, 0, 3);
      ?>
      <?php if (!empty($upcoming)): ?>
        <ul class="member-event-list">
          <?php foreach ($upcoming as $event): ?>
            <li>
              <span class="member-event-date"><?= date('d/m', strtotime($event['event_date'])) ?></span>
              <span><?= htmlspecialchars((string) ($event['title'] ?? '')) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="member-empty-state">Aucun événement à venir.</p>
      <?php endif; ?>
      <a href="/espace-membre/evenements" class="member-card-link">Tous les événements →</a>
    </article>

    <!-- Dernières actualités -->
    <article class="card member-card">
      <div class="member-card-icon icon-news"><i class="fa-regular fa-newspaper"></i></div>
      <h2>Dernières actualités</h2>
      <?php if (!empty($latestPosts)): ?>
        <ul class="member-posts-list">
          <?php foreach ($latestPosts as $post): ?>
            <li>
              <a href="/actualites/<?= htmlspecialchars((string) ($post['slug'] ?? '')) ?>"><?= htmlspecialchars((string) ($post['title'] ?? '')) ?></a>
              <small><?= date('d/m/Y', strtotime($post['created_at'])) ?></small>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="member-empty-state">Aucune actualité pour le moment.</p>
      <?php endif; ?>
      <a href="/espace-membre/actualites" class="member-card-link">Toutes les actualités →</a>
    </article>

    <!-- Mon profil -->
    <article class="card member-card">
      <div class="member-card-icon icon-user"><i class="fa-regular fa-user"></i></div>
      <h2>Mon profil</h2>
      <div class="member-profil-summary">
        <p><strong><?= $firstName ?> <?= $lastName ?></strong></p>
        <p><?= htmlspecialchars((string) ($member['email'] ?? 'Non renseigné')) ?></p>
        <p><?= $roleName ?></p>
      </div>
      <a href="/espace-membre/profil" class="member-card-link">Voir mon profil →</a>
    </article>
  </div>
</section>
