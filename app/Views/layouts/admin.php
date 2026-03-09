<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? htmlspecialchars($title) : 'Administration' ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
  <?php $cssVersion = (string) (filemtime(BASE_PATH . '/public/assets/css/app.css') ?: time()); ?>
  <link rel="stylesheet" href="/public/assets/css/app.css?v=<?= urlencode($cssVersion) ?>">
  <?php
    $adminThemePath = BASE_PATH . '/public/assets/css/modules/admin-theme.css';
    $adminThemeVersion = is_file($adminThemePath) ? (string) (filemtime($adminThemePath) ?: time()) : null;
  ?>
  <?php if ($adminThemeVersion !== null): ?>
    <link rel="stylesheet" href="/public/assets/css/modules/admin-theme.css?v=<?= urlencode($adminThemeVersion) ?>">
  <?php endif; ?>
  <?php if (!empty($pageStyles) && is_array($pageStyles)): ?>
    <?php foreach ($pageStyles as $styleFile): ?>
      <?php
        $stylePath = BASE_PATH . '/public/assets/css/' . ltrim((string) $styleFile, '/');
        if (!is_file($stylePath)) {
            continue;
        }
        $styleVersion = (string) (filemtime($stylePath) ?: time());
      ?>
      <link rel="stylesheet" href="/public/assets/css/<?= htmlspecialchars(ltrim((string) $styleFile, '/')) ?>?v=<?= urlencode($styleVersion) ?>">
    <?php endforeach; ?>
  <?php endif; ?>
</head>
<body class="admin-body">
  <?php $__unreadMessages = \App\Models\ContactMessageModel::countUnread(); ?>
  <header class="site-header">
    <div class="wrap header-inner">
      <a href="/admin" class="brand">Administration · Les enfants de la lune</a>
      <nav class="main-nav">
        <a href="/admin">Dashboard</a>
        <a href="/admin/articles">Articles</a>
        <a href="/admin/locations">Locations</a>
        <a href="/admin/comptabilite">Comptabilité</a>
        <a href="/admin/evenements">Événements</a>
        <a href="/admin/membres">Membres</a>
        <a href="/admin/messages" class="nav-link-with-badge">Messages<?php if ($__unreadMessages > 0): ?><span class="nav-badge"><?= $__unreadMessages ?></span><?php endif; ?></a>
        <?php if (\App\Core\Auth::can('users')): ?>
          <a href="/admin/utilisateurs">Comptes</a>
        <?php endif; ?>
        <a href="/">Site public</a>
        <a href="/admin/articles/new">Nouvel article</a>
      </nav>
    </div>
  </header>

  <main class="wrap admin-main">
    <?php if ($success = \App\Core\Flash::get('success')): ?>
      <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error = \App\Core\Flash::get('error')): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?= $content ?>
  </main>

  <footer class="site-footer wrap admin-footer">
    <small>© <?= date('Y') ?> Les Enfants de la Lune · Panel administration</small>
  </footer>
</body>
</html>
