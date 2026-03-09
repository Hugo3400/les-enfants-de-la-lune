<section class="admin-dashboard">
  <div class="section-head">
    <h1>Tableau de bord</h1>
    <span class="admin-badge">Administration</span>
  </div>
  <p class="admin-lead">Vue rapide de l'activité et accès direct aux actions importantes.</p>

  <div class="stats-grid admin-stats-grid">
    <article class="card admin-stat-card">
      <p>Articles totaux</p>
      <strong><?= (int) ($postsCount ?? 0) ?></strong>
    </article>
    <article class="card admin-stat-card">
      <p>Articles publiés</p>
      <strong><?= (int) ($publishedCount ?? 0) ?></strong>
    </article>
    <article class="card admin-stat-card">
      <p>Brouillons</p>
      <strong><?= (int) ($draftCount ?? 0) ?></strong>
    </article>
    <article class="card admin-stat-card">
      <p>Messages reçus</p>
      <strong><?= (int) ($messagesCount ?? 0) ?></strong>
    </article>
    <article class="card admin-stat-card">
      <p>Locations disponibles</p>
      <strong><?= (int) ($rentalsAvailable ?? 0) ?></strong>
    </article>
    <article class="card admin-stat-card">
      <p>Locations non disponibles</p>
      <strong><?= (int) ($rentalsUnavailable ?? 0) ?></strong>
    </article>
    <article class="card admin-stat-card">
      <p>Solde comptable</p>
      <strong><?= number_format((float) ($accountingBalance ?? 0), 2, ',', ' ') ?> €</strong>
    </article>
    <article class="card admin-stat-card">
      <p>Membres inscrits</p>
      <strong><?= (int) ($membersCount ?? 0) ?></strong>
    </article>
    <article class="card admin-stat-card">
      <p>Membres actifs</p>
      <strong><?= (int) ($membersActive ?? 0) ?></strong>
    </article>
  </div>

  <div class="actions-row admin-actions-row">
    <a class="button-link" href="/admin/articles/new">Créer un article</a>
    <a class="button-secondary" href="/admin/articles">Gérer les articles</a>
    <a class="button-secondary" href="/admin/messages">Voir les messages</a>
    <form method="post" action="/admin/logout">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
      <button type="submit" class="button-secondary">Se déconnecter</button>
    </form>
  </div>

  <div class="admin-grid-2">
    <article class="card">
      <div class="section-head">
        <h2>Derniers articles</h2>
        <a href="/admin/articles">Voir tout</a>
      </div>

      <?php if (empty($latestPosts)): ?>
        <div class="admin-empty">
          <p>Aucun article pour le moment.</p>
          <a class="button-link" href="/admin/articles/new">Publier un premier article</a>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Titre</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($latestPosts, 0, 6) as $post): ?>
                <tr>
                  <td><?= htmlspecialchars((string) $post['title']) ?></td>
                  <td><?= (int) $post['is_published'] === 1 ? 'Publié' : 'Brouillon' ?></td>
                  <td class="actions-cell">
                    <a href="/admin/articles/<?= (int) $post['id'] ?>/edit">Modifier</a>
                    <form method="post" action="/admin/articles/<?= (int) $post['id'] ?>/delete" onsubmit="return confirm('Supprimer cet article ?');">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                      <button type="submit" class="link-danger">Supprimer</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </article>

    <article class="card">
      <div class="section-head">
        <h2>Derniers messages</h2>
        <a href="/admin/messages">Voir tout</a>
      </div>

      <?php if (empty($latestMessages)): ?>
        <div class="admin-empty">
          <p>Aucun message reçu pour le moment.</p>
          <a class="button-secondary" href="/">Voir le site public</a>
        </div>
      <?php else: ?>
        <div class="admin-message-list">
          <?php foreach ($latestMessages as $message): ?>
            <article class="admin-message-item">
              <h3><?= htmlspecialchars((string) $message['subject']) ?></h3>
              <p><strong><?= htmlspecialchars((string) $message['name']) ?></strong> · <?= htmlspecialchars((string) $message['email']) ?></p>
              <small><?= htmlspecialchars((string) $message['created_at']) ?></small>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </article>
  </div>
</section>
