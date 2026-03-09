<section class="member-page">
  <div class="section-head">
    <h1><i class="fa-solid fa-newspaper"></i> Actualités</h1>
  </div>

  <?php
    $currentTheme = (string) ($selectedTheme ?? '');
    $counts = $themeCounts ?? [];
    $totalCount = 0;
    foreach ($counts as $count) {
      $totalCount += (int) $count;
    }
  ?>

  <div class="member-theme-filters">
    <a href="/espace-membre/actualites" class="member-theme-pill <?= $currentTheme === '' ? 'is-active' : '' ?>">Tous (<?= $totalCount ?>)</a>
    <?php foreach (($themes ?? []) as $themeKey => $themeLabel): ?>
      <a href="/espace-membre/actualites?theme=<?= urlencode((string) $themeKey) ?>" class="member-theme-pill <?= $currentTheme === (string) $themeKey ? 'is-active' : '' ?>">
        <?= htmlspecialchars((string) $themeLabel) ?> (<?= (int) ($counts[$themeKey] ?? 0) ?>)
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($posts)): ?>
    <div class="member-posts-grid">
      <?php foreach ($posts as $post): ?>
        <article class="card member-post-card">
          <div class="member-post-meta">
            <time><?= date('d/m/Y', strtotime($post['created_at'])) ?></time>
            <?php $themeKey = (string) ($post['theme'] ?? 'general'); ?>
            <span class="member-post-theme"><?= htmlspecialchars((string) (($themes[$themeKey] ?? null) ?: 'Général')) ?></span>
          </div>
          <h2><a href="/actualites/<?= htmlspecialchars((string) ($post['slug'] ?? '')) ?>"><?= htmlspecialchars((string) ($post['title'] ?? '')) ?></a></h2>
          <?php if (!empty($post['excerpt'])): ?>
            <p class="member-post-excerpt"><?= htmlspecialchars((string) $post['excerpt']) ?></p>
          <?php endif; ?>
          <a href="/actualites/<?= htmlspecialchars((string) ($post['slug'] ?? '')) ?>" class="member-card-link">Lire l'article →</a>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <article class="card member-empty-card">
      <div class="member-empty-icon"><i class="fa-regular fa-newspaper"></i></div>
      <h2>Aucune actualité</h2>
      <p>Aucun article publié pour le moment.</p>
    </article>
  <?php endif; ?>
</section>
