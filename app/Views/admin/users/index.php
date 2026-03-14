<section>
  <div class="section-head">
    <h1>Comptes utilisateurs</h1>
    <div class="accounting-head-actions">
      <span class="admin-badge"><?= count($users ?? []) ?> compte<?= count($users ?? []) > 1 ? 's' : '' ?></span>
      <a class="button-link" href="/admin/utilisateurs/new">+ Créer un compte</a>
    </div>
  </div>
  <p style="margin-bottom:14px;font-size:.9rem;">Gérez les accès au panel d'administration et les niveaux de permission.</p>

  <?php
    $currentUser = \App\Core\Auth::user();
    $currentUserId = $currentUser ? (int) $currentUser['id'] : 0;
    $roleLabels = $roles ?? \App\Models\UserModel::ROLES;
  ?>

  <?php if (empty($users)): ?>
    <p>Aucun compte utilisateur.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Statut</th>
            <th>Créé le</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <?php
              $uid = (int) $u['id'];
              $uRole = (string) ($u['role'] ?? 'member');
              $uActive = ((int) ($u['is_active'] ?? 1)) === 1;
              $isSelf = $uid === $currentUserId;
            ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars((string) $u['name']) ?></strong>
                <?php if ($isSelf): ?><small style="color:#6957d8;">(vous)</small><?php endif; ?>
              </td>
              <td><?= htmlspecialchars((string) $u['email']) ?></td>
              <td><span class="admin-badge"><?= htmlspecialchars($roleLabels[$uRole] ?? ucfirst($uRole)) ?></span></td>
              <td>
                <span class="status-pill <?= $uActive ? 'status-available' : 'status-unavailable' ?>">
                  <?= $uActive ? 'Actif' : 'Désactivé' ?>
                </span>
              </td>
              <td><?= htmlspecialchars((string) ($u['created_at'] ?? '')) ?></td>
              <td class="actions-cell">
                <a href="/admin/utilisateurs/<?= $uid ?>/edit" class="button-secondary" style="padding:5px 10px;font-size:.78rem;">Modifier</a>
                <?php if (!$isSelf): ?>
                  <form method="post" action="/admin/utilisateurs/<?= $uid ?>/delete" onsubmit="return confirm('Supprimer ce compte ?');" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                    <button type="submit" class="link-danger" style="font-size:.82rem;">Supprimer</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <article class="card" style="padding:20px;margin-top:20px;">
      <h3 style="margin:0 0 12px;">Niveaux de permission</h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
        <div style="padding:14px;background:#faf8f5;border-radius:10px;border:1px solid rgba(0,0,0,0.06);">
          <strong style="color:#6957d8;">Webmaster</strong>
          <p style="font-size:.82rem;margin:6px 0 0;">Accès complet à toutes les fonctionnalités, gestion des comptes et des membres.</p>
        </div>
        <div style="padding:14px;background:#faf8f5;border-radius:10px;border:1px solid rgba(0,0,0,0.06);">
          <strong style="color:#1e2a3a;">Président</strong>
          <p style="font-size:.82rem;margin:6px 0 0;">Accès complet à toutes les fonctionnalités, gestion des comptes et des membres.</p>
        </div>
        <div style="padding:14px;background:#faf8f5;border-radius:10px;border:1px solid rgba(0,0,0,0.06);">
          <strong style="color:#1e2a3a;">Vice président</strong>
          <p style="font-size:.82rem;margin:6px 0 0;">Articles, événements, locations, messages et consultation comptabilité.</p>
        </div>
        <div style="padding:14px;background:#faf8f5;border-radius:10px;border:1px solid rgba(0,0,0,0.06);">
          <strong style="color:#1e2a3a;">Trésorier</strong>
          <p style="font-size:.82rem;margin:6px 0 0;">Accès au tableau de bord, aux membres et à la comptabilité.</p>
        </div>
        <div style="padding:14px;background:#faf8f5;border-radius:10px;border:1px solid rgba(0,0,0,0.06);">
          <strong style="color:#1e2a3a;">Secrétaire</strong>
          <p style="font-size:.82rem;margin:6px 0 0;">Articles et événements (création / modification) + consultation messages.</p>
        </div>
        <div style="padding:14px;background:#faf8f5;border-radius:10px;border:1px solid rgba(0,0,0,0.06);">
          <strong style="color:#1e2a3a;">Membre</strong>
          <p style="font-size:.82rem;margin:6px 0 0;">Accès au tableau de bord uniquement (espace associatif).</p>
        </div>
      </div>
    </article>
  <?php endif; ?>
</section>
