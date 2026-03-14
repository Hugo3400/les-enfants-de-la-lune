<?php
// Helper badge couleur pour les champs oui/non/en_cours
function memberBadge(?string $val): string {
    if ($val === null || $val === '') return '<span style="color:#8a8a8a;">—</span>';
    if ($val === 'oui')      return '<span class="status-pill status-available">Oui</span>';
    if ($val === 'non')      return '<span class="status-pill status-unavailable">Non</span>';
    if ($val === 'en_cours') return '<span class="admin-badge" style="background:rgba(230,150,30,.18);color:#e6961e;">En cours</span>';
    return htmlspecialchars($val);
}

function memberPermissionBadge(?string $role, ?int $isActive): string {
  if (!$role || $role === 'member' || !\App\Models\UserModel::hasPermission($role, 'dashboard')) {
    return '';
  }

  $label = match ($role) {
    'webmaster' => 'Webmaster',
    'admin' => 'Président',
    'moderator' => 'Vice président',
    'treasurer' => 'Trésorier',
    'editor' => 'Secrétaire',
    default => 'Permissions',
  };

  $style = $isActive === 0
    ? 'background:rgba(138,138,138,.16);color:#a0a0a0;margin-left:8px;'
    : 'background:rgba(92,224,163,.16);color:#5ce0a3;margin-left:8px;';

  return '<span class="admin-badge" style="' . $style . '">' . htmlspecialchars($label) . '</span>';
}

$filters = $filters ?? [];
$sort = $sort ?? 'name_asc';
?>
<section>
  <div class="section-head">
    <h1>Membres de l'association</h1>
    <div class="accounting-head-actions">
      <span class="admin-badge"><?= count($members ?? []) ?> membre<?= count($members ?? []) > 1 ? 's' : '' ?></span>
      <a class="button-link" href="/admin/membres/new">+ Ajouter un membre</a>
    </div>
  </div>

  <form method="get" action="/admin/membres" class="card" style="padding:16px 18px;margin-bottom:18px;display:grid;grid-template-columns:2fr repeat(5,minmax(0,1fr));gap:12px;align-items:end;">
    <label style="display:flex;flex-direction:column;gap:6px;">
      <span style="font-size:.76rem;letter-spacing:.08em;text-transform:uppercase;color:#8a8a8a;">Recherche</span>
      <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>" placeholder="Nom, email, téléphone" style="width:100%;">
    </label>

    <label style="display:flex;flex-direction:column;gap:6px;">
      <span style="font-size:.76rem;letter-spacing:.08em;text-transform:uppercase;color:#8a8a8a;">Rôle membre</span>
      <select name="member_role">
        <option value="all">Tous</option>
        <?php foreach (($roles ?? []) as $roleKey => $roleLabel): ?>
          <option value="<?= htmlspecialchars($roleKey) ?>" <?= (($filters['member_role'] ?? 'all') === $roleKey) ? 'selected' : '' ?>><?= htmlspecialchars($roleLabel) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label style="display:flex;flex-direction:column;gap:6px;">
      <span style="font-size:.76rem;letter-spacing:.08em;text-transform:uppercase;color:#8a8a8a;">Compte</span>
      <select name="account_role">
        <option value="all">Tous</option>
        <option value="privileged" <?= (($filters['account_role'] ?? 'all') === 'privileged') ? 'selected' : '' ?>>Avec permissions</option>
        <option value="webmaster" <?= (($filters['account_role'] ?? 'all') === 'webmaster') ? 'selected' : '' ?>>Webmaster</option>
        <option value="admin" <?= (($filters['account_role'] ?? 'all') === 'admin') ? 'selected' : '' ?>>Président</option>
        <option value="moderator" <?= (($filters['account_role'] ?? 'all') === 'moderator') ? 'selected' : '' ?>>Vice président</option>
        <option value="treasurer" <?= (($filters['account_role'] ?? 'all') === 'treasurer') ? 'selected' : '' ?>>Trésorier</option>
        <option value="editor" <?= (($filters['account_role'] ?? 'all') === 'editor') ? 'selected' : '' ?>>Secrétaire</option>
        <option value="member" <?= (($filters['account_role'] ?? 'all') === 'member') ? 'selected' : '' ?>>Membre</option>
        <option value="none" <?= (($filters['account_role'] ?? 'all') === 'none') ? 'selected' : '' ?>>Sans compte lié</option>
      </select>
    </label>

    <label style="display:flex;flex-direction:column;gap:6px;">
      <span style="font-size:.76rem;letter-spacing:.08em;text-transform:uppercase;color:#8a8a8a;">Logement</span>
      <select name="housing">
        <option value="all">Tous</option>
        <option value="assigned" <?= (($filters['housing'] ?? 'all') === 'assigned') ? 'selected' : '' ?>>Attribué</option>
        <option value="unassigned" <?= (($filters['housing'] ?? 'all') === 'unassigned') ? 'selected' : '' ?>>Sans logement</option>
      </select>
    </label>

    <label style="display:flex;flex-direction:column;gap:6px;">
      <span style="font-size:.76rem;letter-spacing:.08em;text-transform:uppercase;color:#8a8a8a;">Paiement</span>
      <select name="paye">
        <option value="all">Tous</option>
        <option value="oui" <?= (($filters['paye'] ?? 'all') === 'oui') ? 'selected' : '' ?>>Oui</option>
        <option value="en_cours" <?= (($filters['paye'] ?? 'all') === 'en_cours') ? 'selected' : '' ?>>En cours</option>
        <option value="non" <?= (($filters['paye'] ?? 'all') === 'non') ? 'selected' : '' ?>>Non</option>
      </select>
    </label>

    <label style="display:flex;flex-direction:column;gap:6px;">
      <span style="font-size:.76rem;letter-spacing:.08em;text-transform:uppercase;color:#8a8a8a;">Tri</span>
      <select name="sort">
        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Nom A → Z</option>
        <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Nom Z → A</option>
        <option value="joined_desc" <?= $sort === 'joined_desc' ? 'selected' : '' ?>>Plus récents</option>
        <option value="joined_asc" <?= $sort === 'joined_asc' ? 'selected' : '' ?>>Plus anciens</option>
        <option value="member_role_asc" <?= $sort === 'member_role_asc' ? 'selected' : '' ?>>Rôle membre A → Z</option>
        <option value="member_role_desc" <?= $sort === 'member_role_desc' ? 'selected' : '' ?>>Rôle membre Z → A</option>
        <option value="account_role_asc" <?= $sort === 'account_role_asc' ? 'selected' : '' ?>>Rôle compte A → Z</option>
        <option value="account_role_desc" <?= $sort === 'account_role_desc' ? 'selected' : '' ?>>Rôle compte Z → A</option>
        <option value="housing_asc" <?= $sort === 'housing_asc' ? 'selected' : '' ?>>Logement A → Z</option>
        <option value="housing_desc" <?= $sort === 'housing_desc' ? 'selected' : '' ?>>Logement Z → A</option>
      </select>
    </label>

    <div style="display:flex;gap:10px;align-items:center;grid-column:1 / -1;justify-content:flex-end;">
      <a class="button-secondary" href="/admin/membres">Réinitialiser</a>
      <button type="submit" class="button-link">Appliquer</button>
    </div>
  </form>

  <?php if (empty($members)): ?>
    <div class="card" style="padding:32px;text-align:center;">
      <p>Aucun membre enregistré pour le moment.</p>
      <a class="button-link" href="/admin/membres/new" style="margin-top:12px;display:inline-block;">Ajouter le premier membre</a>
    </div>
  <?php else: ?>
    <div class="table-wrap" style="overflow-x:auto;">
      <table style="min-width:1400px;font-size:.82rem;">
        <thead>
          <tr>
            <th style="white-space:nowrap;">Nom</th>
            <th>Tél</th>
            <th style="white-space:nowrap;">Rec. BC</th>
            <th style="white-space:nowrap;">Carte</th>
            <th style="white-space:nowrap;">Validité</th>
            <th>Situation</th>
            <th style="white-space:nowrap;">RDV</th>
            <th style="white-space:nowrap;">Logement</th>
            <th style="white-space:nowrap;">Payé</th>
            <th style="white-space:nowrap;">Coupons</th>
            <th style="white-space:nowrap;">Depuis</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($members as $member): ?>
            <?php
              // Coupons
              $couponParts = [];
              if (!empty($member['coupon_classic_bikes']))  $couponParts[] = 'Classic×' . (int)$member['coupon_classic_bikes'];
              if (!empty($member['coupon_seaton_sand']))    $couponParts[] = "Seaton's×" . (int)$member['coupon_seaton_sand'];
              if (!empty($member['coupon_rex_dinner']))     $couponParts[] = 'Rex×' . (int)$member['coupon_rex_dinner'];
              if (!empty($member['coupon_yellow_jack']))    $couponParts[] = 'YJ×' . (int)$member['coupon_yellow_jack'];
              if (!empty($member['coupon_mojito']))         $couponParts[] = 'Mojito×' . (int)$member['coupon_mojito'];
              // Logement
              $rentalTitle = $member['rental_title'] ?? null;
              if ($rentalTitle) {
                  // extraire la partie après le dernier " - "
                  $rentalShort = preg_replace('/^.*\s-\s/', '', $rentalTitle);
              }
            ?>
            <tr>
              <td style="white-space:nowrap;"><strong><?= htmlspecialchars((string) ($member['last_name'] ?? '')) ?></strong> <?= htmlspecialchars((string) ($member['first_name'] ?? '')) ?><?= memberPermissionBadge($member['linked_user_role'] ?? null, isset($member['linked_user_active']) ? (int) $member['linked_user_active'] : null) ?></td>
              <td style="white-space:nowrap;"><?= !empty($member['phone']) ? htmlspecialchars((string) $member['phone']) : '<span style="color:#8a8a8a;">—</span>' ?></td>
              <td><?= memberBadge($member['recensement_bc'] ?? null) ?></td>
              <td><?= memberBadge($member['carte'] ?? null) ?></td>
              <td style="white-space:nowrap;"><?= !empty($member['carte_validite']) ? date('d/m/Y', strtotime((string)$member['carte_validite'])) : '<span style="color:#8a8a8a;">—</span>' ?></td>
              <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars((string)($member['situation'] ?? '')) ?>">
                <?= !empty($member['situation']) ? htmlspecialchars((string) $member['situation']) : '<span style="color:#8a8a8a;">—</span>' ?>
              </td>
              <td style="white-space:nowrap;"><?= !empty($member['rdv_situation']) ? date('d/m/Y', strtotime((string)$member['rdv_situation'])) : '<span style="color:#8a8a8a;">—</span>' ?></td>
              <td style="white-space:nowrap;">
                <?php if ($rentalTitle): ?>
                  <span class="status-pill status-available" title="<?= htmlspecialchars($rentalTitle) ?>"><?= htmlspecialchars($rentalShort ?? $rentalTitle) ?></span>
                <?php else: ?>
                  <span style="color:#8a8a8a;">—</span>
                <?php endif; ?>
              </td>
              <td><?= memberBadge($member['paye'] ?? null) ?></td>
              <td style="white-space:nowrap;font-size:.76rem;">
                <?= !empty($couponParts) ? implode(' ', array_map(fn($c) => '<span class="admin-badge">' . htmlspecialchars($c) . '</span>', $couponParts)) : '<span style="color:#8a8a8a;">—</span>' ?>
              </td>
              <td style="white-space:nowrap;"><?= !empty($member['joined_at']) ? date('d/m/Y', strtotime((string)$member['joined_at'])) : '<span style="color:#8a8a8a;">—</span>' ?></td>
              <td class="actions-cell" style="white-space:nowrap;">
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
