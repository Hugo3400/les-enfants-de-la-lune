<section>
  <h1>Actualités</h1>
  <p>Retrouve ici les actions, annonces et temps forts de l'association sur Blaine County.</p>

  <?php
    $currentTheme = (string) ($selectedTheme ?? '');
    $counts = $themeCounts ?? [];
    $totalCount = 0;
    foreach ($counts as $count) {
      $totalCount += (int) $count;
    }
  ?>

  <div class="section-head" style="margin: 8px 0 14px; align-items: center; flex-wrap: wrap; gap: 8px;">
    <a href="/actualites" class="button-secondary" style="padding:7px 12px; font-size:.82rem; <?= $currentTheme === '' ? 'font-weight:700;' : '' ?>">Tous (<?= $totalCount ?>)</a>
    <?php foreach (($themes ?? []) as $themeKey => $themeLabel): ?>
      <a href="/actualites?theme=<?= urlencode((string) $themeKey) ?>" class="button-secondary" style="padding:7px 12px; font-size:.82rem; <?= $currentTheme === (string) $themeKey ? 'font-weight:700;' : '' ?>">
        <?= htmlspecialchars((string) $themeLabel) ?> (<?= (int) ($counts[$themeKey] ?? 0) ?>)
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($posts)): ?>
    <p>Aucune publication pour le moment. Les prochaines nouvelles arrivent bientôt.</p>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($posts as $post): ?>
        <article class="card">
          <?php
            $createdAtRaw = (string) ($post['created_at'] ?? '');
            $createdAtTs = strtotime($createdAtRaw);
            $createdAtLabel = $createdAtTs ? date('d/m/Y', $createdAtTs) : $createdAtRaw;
          ?>
          <h2>
            <a href="/actualites/<?= urlencode((string) $post['slug']) ?>">
              <?= htmlspecialchars((string) $post['title']) ?>
            </a>
          </h2>
          <?php $themeKey = (string) ($post['theme'] ?? 'general'); ?>
          <small><?= htmlspecialchars((string) (($themes[$themeKey] ?? null) ?: 'Général')) ?></small>
          <?php if (!empty($post['excerpt'])): ?>
            <p><?= nl2br(htmlspecialchars((string) $post['excerpt'])) ?></p>
          <?php endif; ?>
          <small>Publié le <?= htmlspecialchars($createdAtLabel) ?></small>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
