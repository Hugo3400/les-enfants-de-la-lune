<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? htmlspecialchars($title) : 'Espace membre' ?></title>
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
</head>
<body class="member-body">
  <?php
    $currentMember = \App\Core\Auth::member();
    $memberName = $currentMember ? htmlspecialchars($currentMember['first_name'] . ' ' . $currentMember['last_name']) : 'Membre';
    $memberRole = $currentMember ? (\App\Models\MemberModel::ROLES[$currentMember['role'] ?? 'membre'] ?? 'Membre') : '';
  ?>
  <header class="member-header">
    <div class="wrap header-inner">
      <a href="/espace-membre" class="brand"><i class="fa-solid fa-moon"></i> Espace membre</a>
      <nav class="main-nav member-nav">
        <a href="/espace-membre">Tableau de bord</a>
        <a href="/espace-membre/logement">Mon logement</a>
        <a href="/espace-membre/evenements">Événements</a>
        <a href="/espace-membre/actualites">Actualités</a>
        <a href="/espace-membre/profil">Mon profil</a>
        <a href="/">Site public</a>
      </nav>
      <div class="member-user-badge">
        <span class="member-user-name"><?= $memberName ?></span>
        <span class="member-user-role"><?= $memberRole ?></span>
      </div>
    </div>
  </header>

  <main class="wrap member-main">
    <?php if ($success = \App\Core\Flash::get('success')): ?>
      <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error = \App\Core\Flash::get('error')): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?= $content ?>
  </main>

  <footer class="site-footer wrap member-footer">
    <small>© <?= date('Y') ?> Les Enfants de la Lune · Espace réservé aux membres</small>
  </footer>
</body>
</html>
