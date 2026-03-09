<?php
  $formatInline = static function (string $text): string {
      $safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
      $safe = preg_replace('~\[(.+?)\]\((https?://[^\s)]+)\)~u', '<a href="$2" target="_blank" rel="noopener">$1</a>', $safe) ?? $safe;
      $safe = preg_replace('~\*\*(.+?)\*\*~u', '<strong>$1</strong>', $safe) ?? $safe;
      $safe = preg_replace('~\*(.+?)\*~u', '<em>$1</em>', $safe) ?? $safe;
      $safe = preg_replace('~`(.+?)`~u', '<code>$1</code>', $safe) ?? $safe;
      return $safe;
  };

  $renderContent = static function (string $content) use ($formatInline): string {
      $lines = preg_split('/\R/u', $content) ?: [];
      $html = [];
      $inUl = false;

      $closeLists = static function () use (&$html, &$inUl): void {
          if ($inUl) {
              $html[] = '</ul>';
              $inUl = false;
          }
      };

      foreach ($lines as $rawLine) {
          $line = trim((string) $rawLine);

          if ($line === '') {
              $closeLists();
              continue;
          }

          if (str_starts_with($line, '### ')) {
              $closeLists();
              $html[] = '<h3>' . $formatInline(substr($line, 4)) . '</h3>';
              continue;
          }

          if (str_starts_with($line, '## ')) {
              $closeLists();
              $html[] = '<h2>' . $formatInline(substr($line, 3)) . '</h2>';
              continue;
          }

          if (str_starts_with($line, '- ')) {
              if (!$inUl) {
                  $html[] = '<ul>';
                  $inUl = true;
              }
              $html[] = '<li>' . $formatInline(substr($line, 2)) . '</li>';
              continue;
          }

          if (str_starts_with($line, '> ')) {
              $closeLists();
              $html[] = '<blockquote>' . $formatInline(substr($line, 2)) . '</blockquote>';
              continue;
          }

          $closeLists();
          $html[] = '<p>' . $formatInline($line) . '</p>';
      }

      $closeLists();
      return implode('', $html);
  };

  $formattedContent = $renderContent((string) ($post['content'] ?? ''));
?>

<article class="card blog-article-card">
  <h1><?= htmlspecialchars((string) ($post['title'] ?? '')) ?></h1>
  <p class="blog-article-meta"><small>Publié le <?= htmlspecialchars((string) ($post['created_at'] ?? '')) ?></small></p>

  <?php if (!empty($post['excerpt'])): ?>
    <p class="blog-article-excerpt"><strong><?= nl2br(htmlspecialchars((string) $post['excerpt'])) ?></strong></p>
  <?php endif; ?>

  <div class="blog-article-content">
    <?= $formattedContent ?>
  </div>
</article>
<p><a href="/actualites">← Retour aux actualités</a></p>
