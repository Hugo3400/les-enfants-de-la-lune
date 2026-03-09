<section>
  <div class="section-head">
    <h1>Membres de l'association</h1>
    <div class="accounting-head-actions">
      <span class="admin-badge"><?= count($members ?? []) ?> membre<?= count($members ?? []) > 1 ? 's' : '' ?></span>
      <a class="button-link" href="/admin/membres/new">+ Ajouter un membre</a>
    </div>
  </div>

  <?php if (empty($members)): ?>
    <div class="card" style="padding:32px;text-align:center;">
      <p>Aucun membre enregistré pour le moment.</p>
      <a class="button-link" href="/admin/membres/new" style="margin-top:12px;display:inline-block;">Ajouter le premier membre</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Rôle</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Statut</th>
            <th>Compte lié</th>
            <th>Membre depuis</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($members as $member): ?>
            <?php
              $memberRole = (string) ($member['role'] ?? 'membre');
              $memberStatus = (string) ($member['status'] ?? 'active');
              $isActive = $memberStatus === 'active';
            ?>
            <tr>
              <td><strong><?= htmlspecialchars((string) ($member['last_name'] ?? '')) ?></strong> <?= htmlspecialchars((string) ($member['first_name'] ?? '')) ?></td>
              <td><span class="admin-badge"><?= htmlspecialchars($roles[$memberRole] ?? ucfirst($memberRole)) ?></span></td>
              <td>
                <?php if (!empty($member['email'])): ?>
                  <a href="mailto:<?= rawurlencode((string) $member['email']) ?>"><?= htmlspecialchars((string) $member['email']) ?></a>
                <?php else: ?>
                  <span style="color:#8a8a8a;">—</span>
                <?php endif; ?>
              </td>
              <td><?= !empty($member['phone']) ? htmlspecialchars((string) $member['phone']) : '<span style="color:#8a8a8a;">—</span>' ?></td>
              <td>
                <span class="status-pill <?= $isActive ? 'status-available' : 'status-unavailable' ?>">
                  <?= htmlspecialchars($statuses[$memberStatus] ?? ucfirst($memberStatus)) ?>
                </span>
              </td>
              <td>
                <?php if (!empty($member['user_id'])): ?>
                  <span class="status-pill status-available">Oui</span>
                <?php else: ?>
                  <span style="color:#8a8a8a;">Non</span>
                <?php endif; ?>
              </td>
              <td><?= !empty($member['joined_at']) ? htmlspecialchars((string) $member['joined_at']) : '<span style="color:#8a8a8a;">—</span>' ?></td>
              <td class="actions-cell">
                <a href="/admin/membres/<?= (int) $member['id'] ?>/edit" class="button-secondary" style="padding:5px 10px;font-size:.78rem;">Modifier</a>
                <form method="post" action="/admin/membres/<?= (int) $member['id'] ?>/delete" onsubmit="return confirm('Supprimer ce membre ?');" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                  <button type="submit" class="link-danger" style="font-size:.82rem;">Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
