<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? htmlspecialchars($title) : 'Connexion' ?></title>
  <?php $cssVersion = (string) (filemtime(BASE_PATH . '/public/assets/css/app.css') ?: time()); ?>
  <link rel="stylesheet" href="/public/assets/css/app.css?v=<?= urlencode($cssVersion) ?>">
  <?php
    $authThemePath = BASE_PATH . '/public/assets/css/modules/public-event.css';
    $authThemeVersion = is_file($authThemePath) ? (string) (filemtime($authThemePath) ?: time()) : null;
  ?>
  <?php if ($authThemeVersion !== null): ?>
    <link rel="stylesheet" href="/public/assets/css/modules/public-event.css?v=<?= urlencode($authThemeVersion) ?>">
  <?php endif; ?>
</head>
<body class="auth-body">
  <main class="auth-wrap">
    <?= $content ?>
  </main>
</body>
</html>
