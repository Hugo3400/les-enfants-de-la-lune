<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? htmlspecialchars($title) : 'Connexion membre' ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
  <?php $cssVersion = (string) (filemtime(BASE_PATH . '/public/assets/css/app.css') ?: time()); ?>
  <link rel="stylesheet" href="/public/assets/css/app.css?v=<?= urlencode($cssVersion) ?>">
  <?php
    $memberCssPath = BASE_PATH . '/public/assets/css/modules/member-portal.css';
    $memberCssVersion = is_file($memberCssPath) ? (string) (filemtime($memberCssPath) ?: time()) : null;
  ?>
  <?php if ($memberCssVersion !== null): ?>
    <link rel="stylesheet" href="/public/assets/css/modules/member-portal.css?v=<?= urlencode($memberCssVersion) ?>">
  <?php endif; ?>
  <?php
    $authThemePath = BASE_PATH . '/public/assets/css/modules/public-event.css';
    $authThemeVersion = is_file($authThemePath) ? (string) (filemtime($authThemePath) ?: time()) : null;
  ?>
  <?php if ($authThemeVersion !== null): ?>
    <link rel="stylesheet" href="/public/assets/css/modules/public-event.css?v=<?= urlencode($authThemeVersion) ?>">
  <?php endif; ?>
</head>
<body class="auth-body member-auth-body">
  <main class="auth-wrap">
    <?php if ($success = \App\Core\Flash::get('success')): ?>
      <div class="alert success member-auth-alert"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error = \App\Core\Flash::get('error')): ?>
      <div class="alert error member-auth-alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?= $content ?>
  </main>
  <footer class="site-footer wrap member-footer">
    <small>© <?= date('Y') ?> Les Enfants de la Lune · Espace membre · Développé par <a href="https://nexadev.fr/" target="_blank" rel="noopener">NexaDev</a></small>
  </footer>
</body>
</html>
