<?php
// Helper badge couleur pour les champs oui/non/en_cours
function memberBadge(?string $val): string {
    if ($val === null || $val === '') return '<span style="color:#8a8a8a;">—</span>';
    if ($val === 'oui')      return '<span class="status-pill status-available">Oui</span>';
    if ($val === 'non')      return '<span class="status-pill status-unavailable">Non</span>';
    if ($val === 'en_cours') return '<span class="admin-badge" style="background:rgba(230,150,30,.18);color:#e6961e;">En cours</span>';
    return htmlspecialchars($val);
}
?>
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
              <td style="white-space:nowrap;"><strong><?= htmlspecialchars((string) ($member['last_name'] ?? '')) ?></strong> <?= htmlspecialchars((string) ($member['first_name'] ?? '')) ?></td>
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
