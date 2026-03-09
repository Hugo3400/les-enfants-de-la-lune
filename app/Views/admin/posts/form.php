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
      <div class="article-form-row">
        <label>
          Titre
          <input type="text" name="title" id="postTitle" value="<?= htmlspecialchars((string) ($post['title'] ?? '')) ?>" required>
        </label>

        <label>
          Slug
          <input type="text" name="slug" id="postSlug" value="<?= htmlspecialchars((string) ($post['slug'] ?? '')) ?>" placeholder="mon-article" required>
        </label>
      </div>
      <p class="form-help">URL: <span id="slugPreview">/actualites/<?= htmlspecialchars((string) (($post['slug'] ?? '') !== '' ? $post['slug'] : 'mon-article')) ?></span></p>

      <label>
        Extrait
        <textarea name="excerpt" rows="3" id="postExcerpt" placeholder="Résumé court affiché dans la liste des actualités."><?= htmlspecialchars((string) ($post['excerpt'] ?? '')) ?></textarea>
      </label>

      <label>
        Thème
        <select name="theme">
          <?php $currentTheme = (string) ($post['theme'] ?? 'general'); ?>
          <?php foreach (($themes ?? []) as $themeKey => $themeLabel): ?>
            <option value="<?= htmlspecialchars((string) $themeKey) ?>" <?= $currentTheme === (string) $themeKey ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) $themeLabel) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <div class="article-editor-wrap">
        <div class="article-editor-head">
          <h3>Contenu</h3>
          <div class="article-editor-tools" role="toolbar" aria-label="Outils de mise en forme">
            <button type="button" class="article-tool" data-wrap-prefix="**" data-wrap-suffix="**">Gras</button>
            <button type="button" class="article-tool" data-wrap-prefix="*" data-wrap-suffix="*">Italique</button>
            <button type="button" class="article-tool" data-line-prefix="## ">H2</button>
            <button type="button" class="article-tool" data-line-prefix="### ">H3</button>
            <button type="button" class="article-tool" data-line-prefix="- ">Liste</button>
            <button type="button" class="article-tool" data-line-prefix="> ">Citation</button>
            <button type="button" class="article-tool" data-wrap-prefix="[" data-wrap-suffix="](https://)">Lien</button>
          </div>
        </div>
        <textarea name="content" rows="14" id="postContent" required placeholder="Écris ici. Tu peux utiliser: ## titre, - liste, > citation, **gras**, *italique*, [lien](https://...)\n\nExemple:\n## Notre action du mois\nUn premier paragraphe clair...\n- Point 1\n- Point 2"><?= htmlspecialchars((string) ($post['content'] ?? '')) ?></textarea>
        <p class="form-help">Mise en forme prise en charge sur le site: titres (##), listes (-), citations (>), gras, italique et liens.</p>
      </div>
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
        <h3>Modèles rapides</h3>
        <div class="article-template-list">
          <button type="button" class="button-secondary article-template-btn" data-template="annonce">Annonce association</button>
          <button type="button" class="button-secondary article-template-btn" data-template="temoignage">Témoignage</button>
          <button type="button" class="button-secondary article-template-btn" data-template="bilan">Bilan d'action</button>
        </div>
      </article>

      <article class="article-side-card">
        <h3>Aperçu</h3>
        <div id="articleLivePreview" class="article-live-preview"></div>
      </article>

      <article class="article-side-card">
        <h3>Indicateurs</h3>
        <p class="article-stat"><span>Longueur extrait:</span> <strong id="excerptCount"><?= mb_strlen((string) ($post['excerpt'] ?? '')) ?></strong></p>
        <p class="article-stat"><span>Longueur contenu:</span> <strong id="contentCount"><?= mb_strlen((string) ($post['content'] ?? '')) ?></strong></p>
        <p class="article-stat"><span>Mots estimés:</span> <strong id="wordCount">0</strong></p>
        <p class="article-stat"><span>Temps de lecture:</span> <strong id="readingTime">0 min</strong></p>
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
    var wordCount = document.getElementById('wordCount');
    var readingTime = document.getElementById('readingTime');
    var livePreview = document.getElementById('articleLivePreview');
    var autoExcerptBtn = document.getElementById('autoExcerptBtn');
    var slugTouched = false;
    var tools = document.querySelectorAll('.article-tool');
    var templateButtons = document.querySelectorAll('.article-template-btn');

    var templates = {
      annonce: "## Ce qu'il faut retenir\n\nExplique ici l'information principale en 2-3 phrases.\n\n## Informations pratiques\n- Date :\n- Lieu :\n- Contact :\n\n## Comment participer\nAjoute ici le lien ou la démarche à suivre.",
      temoignage: "## Une histoire à partager\n\nPrésente la personne ou la situation en quelques lignes.\n\n## Ce qui a changé\n- Avant :\n- Action menée :\n- Résultat :\n\n## Message de la personne\n> Insère ici une citation marquante.",
      bilan: "## Bilan du mois\n\nRésumé global de l'action.\n\n## Chiffres clés\n- Bénéficiaires :\n- Bénévoles mobilisés :\n- Actions réalisées :\n\n## Prochaines étapes\nDécris la suite du projet en cours."
    };

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

    function computeWords(value) {
      var normalized = (value || '').trim().replace(/\s+/g, ' ');
      if (!normalized) return 0;
      return normalized.split(' ').length;
    }

    function escapeHtml(value) {
      return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function formatInline(text) {
      var result = escapeHtml(text || '');
      result = result.replace(/\[(.+?)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
      result = result.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
      result = result.replace(/\*(.+?)\*/g, '<em>$1</em>');
      result = result.replace(/`(.+?)`/g, '<code>$1</code>');
      return result;
    }

    function renderPreview(value) {
      if (!livePreview) return;

      var lines = String(value || '').replace(/\r\n/g, '\n').split('\n');
      var html = [];
      var inUl = false;

      function closeLists() {
        if (inUl) {
          html.push('</ul>');
          inUl = false;
        }
      }

      for (var i = 0; i < lines.length; i++) {
        var line = lines[i].trim();

        if (!line) {
          closeLists();
          continue;
        }

        if (line.indexOf('## ') === 0) {
          closeLists();
          html.push('<h2>' + formatInline(line.substring(3)) + '</h2>');
          continue;
        }

        if (line.indexOf('### ') === 0) {
          closeLists();
          html.push('<h3>' + formatInline(line.substring(4)) + '</h3>');
          continue;
        }

        if (line.indexOf('- ') === 0) {
          if (!inUl) {
            html.push('<ul>');
            inUl = true;
          }
          html.push('<li>' + formatInline(line.substring(2)) + '</li>');
          continue;
        }

        if (line.indexOf('> ') === 0) {
          closeLists();
          html.push('<blockquote>' + formatInline(line.substring(2)) + '</blockquote>');
          continue;
        }

        closeLists();
        html.push('<p>' + formatInline(line) + '</p>');
      }

      closeLists();
      livePreview.innerHTML = html.join('') || '<p class="article-preview-empty">Commence à écrire pour voir l\'aperçu.</p>';
    }

    function updateDerivedStats() {
      var words = computeWords(contentInput ? contentInput.value : '');
      if (wordCount) wordCount.textContent = String(words);
      if (readingTime) {
        var minutes = Math.max(1, Math.ceil(words / 220));
        readingTime.textContent = words > 0 ? (minutes + ' min') : '0 min';
      }
    }

    function applyWrap(prefix, suffix) {
      if (!contentInput) return;
      var start = contentInput.selectionStart || 0;
      var end = contentInput.selectionEnd || 0;
      var selected = contentInput.value.substring(start, end);
      var replacement = prefix + selected + (suffix || '');
      contentInput.setRangeText(replacement, start, end, 'end');
      contentInput.focus();
      refreshEditorState();
    }

    function applyLinePrefix(prefix) {
      if (!contentInput) return;
      var start = contentInput.selectionStart || 0;
      var value = contentInput.value;
      var lineStart = value.lastIndexOf('\n', start - 1) + 1;
      contentInput.setRangeText(prefix, lineStart, lineStart, 'end');
      contentInput.focus();
      refreshEditorState();
    }

    function insertTemplate(key) {
      if (!contentInput || !templates[key]) return;
      if (contentInput.value.trim() !== '' && !confirm('Remplacer le contenu actuel par ce modèle ?')) {
        return;
      }
      contentInput.value = templates[key];
      refreshEditorState();
    }

    function generateExcerpt() {
      if (!contentInput || !excerptInput) return;
      var raw = contentInput.value
        .replace(/\[(.+?)\]\((https?:\/\/[^\s)]+)\)/g, '$1')
        .replace(/[\*`#>-]/g, '')
        .replace(/\s+/g, ' ')
        .trim();

      if (!raw) return;

      var result = raw.length > 180 ? raw.substring(0, 177).trim() + '...' : raw;
      excerptInput.value = result;
      refreshEditorState();
    }

    function refreshEditorState() {
      updateCount(excerptInput, excerptCount);
      updateCount(contentInput, contentCount);
      updateDerivedStats();
      renderPreview(contentInput ? contentInput.value : '');
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
        refreshEditorState();
      });
    }

    if (contentInput) {
      contentInput.addEventListener('input', function () {
        refreshEditorState();
      });
    }

    tools.forEach(function (tool) {
      tool.addEventListener('click', function () {
        var wrapPrefix = tool.getAttribute('data-wrap-prefix');
        var wrapSuffix = tool.getAttribute('data-wrap-suffix');
        var linePrefix = tool.getAttribute('data-line-prefix');

        if (wrapPrefix !== null) {
          applyWrap(wrapPrefix, wrapSuffix || '');
          return;
        }

        if (linePrefix !== null) {
          applyLinePrefix(linePrefix);
        }
      });
    });

    templateButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        insertTemplate(button.getAttribute('data-template') || '');
      });
    });

    if (autoExcerptBtn) {
      autoExcerptBtn.addEventListener('click', generateExcerpt);
    }

    updateSlugPreview();
    refreshEditorState();
  })();
</script>
