<?php $isEdit = !empty($post['id']); ?>
<section class="card admin-article-form-page">
  <div class="section-head">
    <h1><?= $isEdit ? 'Modifier un article' : 'Nouvel article' ?></h1>
    <a class="button-secondary" href="/admin/articles">Retour aux articles</a>
  </div>
  <p class="article-form-intro">Renseigne le contenu principal puis ajuste la publication. Le slug sert à l'URL publique.</p>

  <form method="post" action="<?= htmlspecialchars((string) ($formAction ?? '/admin/articles')) ?>" class="form-grid article-form-grid" id="articleForm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

    <div class="article-form-main">
      <label>
        Titre
        <input type="text" name="title" id="postTitle" value="<?= htmlspecialchars((string) ($post['title'] ?? '')) ?>" required>
      </label>

      <label>
        Slug
        <input type="text" name="slug" id="postSlug" value="<?= htmlspecialchars((string) ($post['slug'] ?? '')) ?>" placeholder="mon-article" required>
      </label>
      <p class="form-help">URL: <span id="slugPreview">/actualites/<?= htmlspecialchars((string) (($post['slug'] ?? '') !== '' ? $post['slug'] : 'mon-article')) ?></span></p>

      <label>
        Extrait
        <textarea name="excerpt" rows="3" id="postExcerpt"><?= htmlspecialchars((string) ($post['excerpt'] ?? '')) ?></textarea>
      </label>

      <label>
        Contenu
        <textarea name="content" rows="12" id="postContent" required><?= htmlspecialchars((string) ($post['content'] ?? '')) ?></textarea>
      </label>
    </div>

    <aside class="article-form-side">
      <article class="article-side-card">
        <h3>Publication</h3>
        <label class="inline-check article-publish-check">
          <input type="checkbox" name="is_published" <?= (int) ($post['is_published'] ?? 0) === 1 ? 'checked' : '' ?>>
          Publier l'article
        </label>
      </article>

      <article class="article-side-card">
        <h3>Conseils</h3>
        <ul>
          <li>Titre court et clair (idéalement 50-80 caractères).</li>
          <li>Extrait: 1 à 2 phrases de synthèse.</li>
          <li>Un paragraphe = une idée.</li>
        </ul>
      </article>

      <article class="article-side-card">
        <h3>Statistiques</h3>
        <p class="article-stat"><span>Longueur extrait:</span> <strong id="excerptCount"><?= mb_strlen((string) ($post['excerpt'] ?? '')) ?></strong></p>
        <p class="article-stat"><span>Longueur contenu:</span> <strong id="contentCount"><?= mb_strlen((string) ($post['content'] ?? '')) ?></strong></p>
      </article>
    </aside>

    <div class="actions-row article-form-actions">
      <button type="submit"><?= $isEdit ? 'Mettre à jour' : 'Créer l\'article' ?></button>
      <a class="button-secondary" href="/admin/articles">Annuler</a>
    </div>
  </form>
</section>

<script>
  (function () {
    var titleInput = document.getElementById('postTitle');
    var slugInput = document.getElementById('postSlug');
    var slugPreview = document.getElementById('slugPreview');
    var excerptInput = document.getElementById('postExcerpt');
    var contentInput = document.getElementById('postContent');
    var excerptCount = document.getElementById('excerptCount');
    var contentCount = document.getElementById('contentCount');
    var slugTouched = false;

    function slugify(value) {
      return value
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    }

    function updateSlugPreview() {
      if (!slugPreview || !slugInput) return;
      var current = slugInput.value.trim() || 'mon-article';
      slugPreview.textContent = '/actualites/' + current;
    }

    function updateCount(input, output) {
      if (!input || !output) return;
      output.textContent = String(input.value.length);
    }

    if (slugInput) {
      slugInput.addEventListener('input', function () {
        slugTouched = true;
        slugInput.value = slugify(slugInput.value);
        updateSlugPreview();
      });
    }

    if (titleInput && slugInput) {
      titleInput.addEventListener('input', function () {
        if (!slugTouched || slugInput.value.trim() === '') {
          slugInput.value = slugify(titleInput.value);
          updateSlugPreview();
        }
      });
    }

    if (excerptInput) {
      excerptInput.addEventListener('input', function () {
        updateCount(excerptInput, excerptCount);
      });
    }

    if (contentInput) {
      contentInput.addEventListener('input', function () {
        updateCount(contentInput, contentCount);
      });
    }

    updateSlugPreview();
    updateCount(excerptInput, excerptCount);
    updateCount(contentInput, contentCount);
  })();
</script>
