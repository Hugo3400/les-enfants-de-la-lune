<section class="member-page">
  <div class="section-head">
    <h1><i class="fa-solid fa-newspaper"></i> Actualités</h1>
  </div>

  <?php if (!empty($posts)): ?>
    <div class="member-posts-grid">
      <?php foreach ($posts as $post): ?>
        <article class="card member-post-card">
          <div class="member-post-meta">
            <time><?= date('d/m/Y', strtotime($post['created_at'])) ?></time>
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
