<section>
  <div class="section-head">
    <h1>Messages de contact</h1>
    <div class="accounting-head-actions">
      <?php
        $unreadCount = 0;
        foreach (($messages ?? []) as $m) {
          if (((int) ($m['is_read'] ?? 0)) === 0) $unreadCount++;
        }
      ?>
      <?php if ($unreadCount > 0): ?>
        <span class="admin-badge"><?= $unreadCount ?> non lu<?= $unreadCount > 1 ? 's' : '' ?></span>
        <form method="post" action="/admin/messages/read-all" style="display:inline;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
          <button type="submit" class="button-secondary" style="font-size:.82rem;padding:6px 12px;">Tout marquer comme lu</button>
        </form>
      <?php else: ?>
        <span class="admin-badge">Tous lus <i class="fa-solid fa-check"></i></span>
      <?php endif; ?>
    </div>
  </div>
  <p>Messages envoyés depuis le formulaire de contact du site.</p>

  <?php
    $categoryLabels = [
      'aide' => '<i class="fa-solid fa-life-ring"></i> Demande d\'aide',
      'benevole' => '<i class="fa-solid fa-handshake"></i> Bénévolat',
      'partenariat' => '<i class="fa-solid fa-building"></i> Partenariat',
      'evenement' => '<i class="fa-regular fa-calendar-check"></i> Inscription événement',
      'general' => '<i class="fa-regular fa-comment"></i> Général',
    ];
  ?>

  <?php if (empty($messages)): ?>
    <p>Aucun message reçu pour le moment.</p>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($messages as $message): ?>
        <?php $isRead = ((int) ($message['is_read'] ?? 0)) === 1; ?>
        <?php $cat = (string) ($message['category'] ?? 'general'); ?>
        <article class="card admin-message-item <?= $isRead ? '' : 'message-unread' ?>">
          <div class="section-head" style="margin-bottom:8px;">
            <h3><?= htmlspecialchars((string) $message['subject']) ?></h3>
            <span class="admin-badge"><?= htmlspecialchars($categoryLabels[$cat] ?? ucfirst($cat)) ?></span>
          </div>
          <p><strong><?= htmlspecialchars((string) $message['name']) ?></strong> · <a href="mailto:<?= rawurlencode((string) $message['email']) ?>"><?= htmlspecialchars((string) $message['email']) ?></a></p>
          <p><?= nl2br(htmlspecialchars((string) $message['message'])) ?></p>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
            <small>Reçu le <?= htmlspecialchars((string) $message['created_at']) ?></small>
            <?php if (!$isRead): ?>
              <form method="post" action="/admin/messages/<?= (int) $message['id'] ?>/read">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <button type="submit" class="button-secondary" style="font-size:.78rem;padding:5px 10px;">Marquer lu</button>
              </form>
            <?php else: ?>
              <small style="color:#1a8a4a;"><i class="fa-solid fa-check"></i> Lu</small>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
