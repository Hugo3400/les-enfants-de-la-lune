<section class="admin-rentals-page">
  <?php
    $formatDate = static function (?string $date): string {
      if (!$date) {
        return '<span style="opacity:.5;">—</span>';
      }

      $timestamp = strtotime($date);
      if ($timestamp === false) {
        return '<span style="opacity:.5;">—</span>';
      }

      return date('d/m/Y', $timestamp);
    };

    $leaseEndDate = static function (array $rental): ?string {
      $assignedAt = (string) ($rental['assigned_at'] ?? '');
      $durationValue = (int) ($rental['lease_duration_value'] ?? 0);
      $durationUnit = (string) ($rental['lease_duration_unit'] ?? '');

      if ($assignedAt === '' || $durationValue <= 0 || $durationUnit === '') {
        return null;
      }

      $modifier = match ($durationUnit) {
        'week' => '+' . $durationValue . ' week',
        'month' => '+' . $durationValue . ' month',
        'year' => '+' . $durationValue . ' year',
        default => null,
      };

      if ($modifier === null) {
        return null;
      }

      $timestamp = strtotime($assignedAt . ' ' . $modifier);
      return $timestamp === false ? null : date('Y-m-d', $timestamp);
    };
  ?>
  <div class="section-head">
    <h1><i class="fa-solid fa-house"></i> Gestion des locations</h1>
    <a class="button-link" href="/admin/locations/new"><i class="fa-solid fa-plus"></i> Ajouter une location</a>
  </div>

  <p>Gère la disponibilité des biens, les attributions aux membres et leurs informations.</p>

  <?php $currentZone = (string) ($zoneFilter ?? 'all'); ?>
  <div class="admin-rentals-filters" role="group" aria-label="Filtres zones locations">
    <a href="/admin/locations" class="button-secondary <?= $currentZone === 'all' ? 'is-active' : '' ?>">Toutes les zones</a>
    <a href="/admin/locations?zone=paleto" class="button-secondary <?= $currentZone === 'paleto' ? 'is-active' : '' ?>">Paleto Bay</a>
    <a href="/admin/locations?zone=route68" class="button-secondary <?= $currentZone === 'route68' ? 'is-active' : '' ?>">Sandy Shores (Route 68)</a>
  </div>

  <?php if (empty($rentals)): ?>
    <p>Aucune location trouvée pour ce filtre.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Bien</th>
            <th>Lieu</th>
            <th>Prix</th>
            <th>Occupant</th>
            <th>Début</th>
            <th>Fin</th>
            <th>Payé</th>
            <th>Disponibilité</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rentals as $rental): ?>
            <?php $leaseEnd = $leaseEndDate($rental); ?>
            <tr>
              <td><?= htmlspecialchars((string) $rental['title']) ?></td>
              <td><?= htmlspecialchars((string) $rental['location_label']) ?></td>
              <td><?= number_format((float) $rental['price'], 2, ',', ' ') ?> $</td>
              <td>
                <?php if (!empty($rental['member_id'])): ?>
                  <span style="display:inline-flex; align-items:center; gap:.35rem;">
                    <i class="fa-solid fa-user" style="color: var(--violet-500, #8f86f8);"></i>
                    <?= htmlspecialchars($rental['first_name'] . ' ' . $rental['last_name']) ?>
                  </span>
                <?php else: ?>
                  <span style="opacity:.5;">—</span>
                <?php endif; ?>
              </td>
              <td><?= $formatDate($rental['assigned_at'] ?? null) ?></td>
              <td><?= $formatDate($leaseEnd) ?></td>
              <td>
                <?php $paymentStatus = (string) ($rental['paye'] ?? ''); ?>
                <?php if ($paymentStatus === 'oui'): ?>
                  <span class="status-pill status-available">Oui</span>
                <?php elseif ($paymentStatus === 'en_cours'): ?>
                  <span class="admin-badge" style="background:rgba(230,150,30,.18);color:#e6961e;">En cours</span>
                <?php elseif ($paymentStatus === 'non'): ?>
                  <span class="status-pill status-unavailable">Non</span>
                <?php else: ?>
                  <span style="opacity:.5;">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php $isAvailable = ((string) $rental['status']) === 'available'; ?>
                <span class="status-pill <?= $isAvailable ? 'status-available' : 'status-unavailable' ?>">
                  <?= $isAvailable ? 'Disponible' : 'Occupé' ?>
                </span>
              </td>
              <td class="actions-cell">
                <a href="/admin/locations/<?= (int) $rental['id'] ?>/edit"><i class="fa-solid fa-pen"></i> Modifier</a>
                <form method="post" action="/admin/locations/<?= (int) $rental['id'] ?>/delete" onsubmit="return confirm('Supprimer cette location ?');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                  <button type="submit" class="link-danger"><i class="fa-solid fa-trash"></i> Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
