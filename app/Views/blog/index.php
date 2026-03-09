<section>
  <h1>Actualités</h1>
  <p>Retrouve ici les actions, annonces et temps forts de l'association sur Blaine County.</p>

  <?php if (empty($posts)): ?>
    <p>Aucune publication pour le moment. Les prochaines nouvelles arrivent bientôt.</p>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($posts as $post): ?>
        <article class="card">
          <h2>
            <a href="/actualites/<?= urlencode((string) $post['slug']) ?>">
              <?= htmlspecialchars((string) $post['title']) ?>
            </a>
          </h2>
          <?php if (!empty($post['excerpt'])): ?>
            <p><?= nl2br(htmlspecialchars((string) $post['excerpt'])) ?></p>
          <?php endif; ?>
          <small>Publié le <?= htmlspecialchars((string) $post['created_at']) ?></small>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
