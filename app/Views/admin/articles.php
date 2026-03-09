<section class="admin-articles-page">
  <div class="section-head">
    <h1>Gestion des actualités</h1>
    <a class="button-link" href="/admin/articles/new">Nouvel article</a>
  </div>

  <p>Publie, modifie ou retire les actualités visibles sur le site public.</p>

  <?php
    $currentTheme = (string) ($selectedTheme ?? '');
    $counts = $themeCounts ?? [];
  ?>

  <form method="get" action="/admin/articles" class="form-grid" style="max-width:340px; margin-bottom:14px;">
    <label>
      Filtrer par thème
      <select name="theme" onchange="this.form.submit()">
        <option value="">Tous (<?= array_sum(array_map('intval', $counts)) ?>)</option>
        <?php foreach (($themes ?? []) as $key => $label): ?>
          <option value="<?= htmlspecialchars((string) $key) ?>" <?= $currentTheme === (string) $key ? 'selected' : '' ?>>
            <?= htmlspecialchars((string) $label) ?> (<?= (int) ($counts[$key] ?? 0) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </label>
  </form>

  <?php if (empty($posts)): ?>
    <p>Aucun article pour le moment.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Titre</th>
            <th>Slug</th>
            <th>Thème</th>
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
              <?php $themeKey = (string) ($post['theme'] ?? 'general'); ?>
              <td><?= htmlspecialchars((string) (($themes[$themeKey] ?? null) ?: 'Général')) ?></td>
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
