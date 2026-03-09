<section class="admin-articles-page">
  <div class="section-head">
    <h1>Gestion des actualités</h1>
    <a class="button-link" href="/admin/articles/new">Nouvel article</a>
  </div>

  <p>Publie, modifie ou retire les actualités visibles sur le site public.</p>

  <?php if (empty($posts)): ?>
    <p>Aucun article pour le moment.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Titre</th>
            <th>Slug</th>
            <th>Statut</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $post): ?>
            <tr>
              <td><?= htmlspecialchars((string) $post['title']) ?></td>
              <td><?= htmlspecialchars((string) $post['slug']) ?></td>
              <td><?= (int) $post['is_published'] === 1 ? 'Publié' : 'Brouillon' ?></td>
              <td><?= htmlspecialchars((string) $post['created_at']) ?></td>
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
</section>
