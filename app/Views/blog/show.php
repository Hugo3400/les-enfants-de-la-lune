<article class="card">
  <h1><?= htmlspecialchars((string) ($post['title'] ?? '')) ?></h1>
  <p><small>Publié le <?= htmlspecialchars((string) ($post['created_at'] ?? '')) ?></small></p>

  <?php if (!empty($post['excerpt'])): ?>
    <p><strong><?= nl2br(htmlspecialchars((string) $post['excerpt'])) ?></strong></p>
  <?php endif; ?>

  <div>
    <?= nl2br(htmlspecialchars((string) ($post['content'] ?? ''))) ?>
  </div>
</article>
<p><a href="/actualites">← Retour aux actualités</a></p>
