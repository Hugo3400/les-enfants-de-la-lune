<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? htmlspecialchars($title) : "Les Enfants de la Lune" ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
  <?php $cssVersion = (string) (filemtime(BASE_PATH . '/public/assets/css/app.css') ?: time()); ?>
  <link rel="stylesheet" href="/public/assets/css/app.css?v=<?= urlencode($cssVersion) ?>">
  <?php
    $publicThemePath = BASE_PATH . '/public/assets/css/modules/public-event.css';
    $publicThemeVersion = is_file($publicThemePath) ? (string) (filemtime($publicThemePath) ?: time()) : null;
  ?>
  <?php if ($publicThemeVersion !== null): ?>
    <link rel="stylesheet" href="/public/assets/css/modules/public-event.css?v=<?= urlencode($publicThemeVersion) ?>">
  <?php endif; ?>
  <?php
    $pagesCssPath = BASE_PATH . '/public/assets/css/modules/pages.css';
    $pagesCssVersion = is_file($pagesCssPath) ? (string) (filemtime($pagesCssPath) ?: time()) : null;
  ?>
  <?php if ($pagesCssVersion !== null): ?>
    <link rel="stylesheet" href="/public/assets/css/modules/pages.css?v=<?= urlencode($pagesCssVersion) ?>">
  <?php endif; ?>
  <?php
    $mainJsPath = BASE_PATH . '/public/assets/js/main-nav.js';
    $mainJsVersion = is_file($mainJsPath) ? (string) (filemtime($mainJsPath) ?: time()) : null;
  ?>
</head>
<body class="public-body" data-theme="dark">
  <header class="site-header">
    <div class="wrap header-inner">
      <a href="/" class="brand">
        <i class="fa-solid fa-moon brand-icon"></i>
        <span class="brand-text">Les Enfants <span class="brand-accent">de la Lune</span></span>
      </a>
      <button class="nav-toggle" id="navToggle" aria-label="Menu">
        <span class="nav-toggle-bar"></span>
        <span class="nav-toggle-bar"></span>
        <span class="nav-toggle-bar"></span>
      </button>
      <nav class="main-nav" id="mainNav">
        <a href="/" class="nav-link"><i class="fa-solid fa-house"></i> Accueil</a>
        <a href="/a-propos" class="nav-link"><i class="fa-solid fa-users"></i> L'association</a>
        <a href="/locations" class="nav-link"><i class="fa-solid fa-key"></i> Locations</a>
        <a href="/actualites" class="nav-link"><i class="fa-solid fa-newspaper"></i> Actualités</a>
        <?php if (\App\Core\Auth::check()): ?>
          <?php if (\App\Core\Auth::member()): ?>
            <a href="/espace-membre" class="nav-link"><i class="fa-solid fa-id-badge"></i> Mon espace</a>
          <?php endif; ?>
          <a href="/admin" class="nav-link"><i class="fa-solid fa-gear"></i> Administration</a>
        <?php else: ?>
          <a href="/espace-membre/connexion" class="nav-link"><i class="fa-solid fa-right-to-bracket"></i> Espace membre</a>
        <?php endif; ?>
        <a href="/contact" class="nav-cta"><i class="fa-solid fa-hands-holding-heart"></i> Demander de l'aide</a>
      </nav>
    </div>
  </header>
  <main class="wrap">
    <?php if ($success = \App\Core\Flash::get('success')): ?>
      <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error = \App\Core\Flash::get('error')): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?= $content ?>
  </main>

  <footer class="site-footer wrap">
    <small>© <?= date('Y') ?> Les Enfants de la Lune · Association solidaire à Blaine County</small>
  </footer>
  <?php if ($mainJsVersion !== null): ?>
    <script src="/public/assets/js/main-nav.js?v=<?= urlencode($mainJsVersion) ?>" defer></script>
  <?php endif; ?>
</body>
</html>
