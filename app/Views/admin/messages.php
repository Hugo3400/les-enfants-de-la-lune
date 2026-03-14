<section class="admin-messages-page">
  <?php
    $unreadCount = 0;
    foreach (($messages ?? []) as $m) {
      if (((int) ($m['is_read'] ?? 0)) === 0) {
        $unreadCount++;
      }
    }
    $totalCount = count($messages ?? []);

    $categoryLabels = [
      'aide' => ['icon' => 'fa-solid fa-life-ring', 'label' => 'Demande d\'aide'],
      'benevole' => ['icon' => 'fa-solid fa-handshake', 'label' => 'Benevolat'],
      'partenariat' => ['icon' => 'fa-solid fa-building', 'label' => 'Partenariat'],
      'evenement' => ['icon' => 'fa-regular fa-calendar-check', 'label' => 'Inscription evenement'],
      'general' => ['icon' => 'fa-regular fa-comment', 'label' => 'General'],
    ];
  ?>

  <div class="card messages-hero">
    <div class="section-head">
      <div>
        <h1>Messages de contact</h1>
        <p>Messages envoyes depuis le formulaire de contact du site.</p>
      </div>
      <div class="messages-hero-actions">
        <?php if ($unreadCount > 0): ?>
          <form method="post" action="/admin/messages/read-all">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <button type="submit" class="button-secondary">Tout marquer comme lu</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
    <div class="messages-hero-meta">
      <span class="admin-badge"><?= $totalCount ?> message<?= $totalCount > 1 ? 's' : '' ?></span>
      <span class="admin-badge"><?= $unreadCount ?> non lu<?= $unreadCount > 1 ? 's' : '' ?></span>
      <span class="admin-badge"><?= $totalCount - $unreadCount ?> lu<?= ($totalCount - $unreadCount) > 1 ? 's' : '' ?></span>
    </div>
  </div>

  <?php if (empty($messages)): ?>
    <article class="card">
      <div class="admin-empty">
        <p>Aucun message recu pour le moment.</p>
      </div>
    </article>
  <?php else: ?>
    <div class="messages-grid">
      <?php foreach ($messages as $message): ?>
        <?php $isRead = ((int) ($message['is_read'] ?? 0)) === 1; ?>
        <?php $cat = (string) ($message['category'] ?? 'general'); ?>
        <?php $catMeta = $categoryLabels[$cat] ?? ['icon' => 'fa-regular fa-comment', 'label' => ucfirst($cat)]; ?>

        <article class="card admin-message-item <?= $isRead ? 'is-read' : 'is-unread' ?>">
          <div class="section-head message-card-head">
            <h3><?= htmlspecialchars((string) ($message['subject'] ?? '(Sans objet)')) ?></h3>
            <span class="admin-badge">
              <i class="<?= htmlspecialchars((string) $catMeta['icon']) ?>"></i>
              <?= htmlspecialchars((string) $catMeta['label']) ?>
            </span>
          </div>

          <p class="message-author">
            <strong><?= htmlspecialchars((string) ($message['name'] ?? 'Inconnu')) ?></strong>
            <span>·</span>
            <a href="mailto:<?= rawurlencode((string) ($message['email'] ?? '')) ?>"><?= htmlspecialchars((string) ($message['email'] ?? '')) ?></a>
          </p>

          <div class="message-body"><?= nl2br(htmlspecialchars((string) ($message['message'] ?? ''))) ?></div>

          <div class="message-footer">
            <small>Recu le <?= htmlspecialchars((string) ($message['created_at'] ?? '')) ?></small>
            <?php if (!$isRead): ?>
              <form method="post" action="/admin/messages/<?= (int) $message['id'] ?>/read">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <button type="submit" class="button-secondary">Marquer lu</button>
              </form>
            <?php else: ?>
              <small class="message-read-mark"><i class="fa-solid fa-check"></i> Lu</small>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
