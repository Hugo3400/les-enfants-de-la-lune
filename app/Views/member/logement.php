<?php
  $firstName = htmlspecialchars((string) ($member['first_name'] ?? ''));

  $formatLeaseDuration = static function (array $rental): string {
    $value = (int) ($rental['lease_duration_value'] ?? 0);
    if ($value <= 0) {
      return 'Non définie';
    }

    $unit = (string) ($rental['lease_duration_unit'] ?? 'month');
    if ($unit === 'year') {
      return $value . ' an(s)';
    }

    if ($unit === 'week') {
      return $value . ' semaine(s)';
    }

    return $value . ' mois';
  };

  $leaseStatus = static function (array $rental): string {
    if (($rental['status'] ?? '') !== 'active') {
      return 'Terminé';
    }

    $value = (int) ($rental['lease_duration_value'] ?? 0);
    if ($value <= 0 || empty($rental['assigned_at'])) {
      return 'En cours';
    }

    $assignedAt = new DateTimeImmutable((string) $rental['assigned_at']);
    $unit = (string) ($rental['lease_duration_unit'] ?? 'month');
    if ($unit === 'year') {
      $endDate = $assignedAt->modify('+' . $value . ' year');
    } elseif ($unit === 'week') {
      $endDate = $assignedAt->modify('+' . $value . ' week');
    } else {
      $endDate = $assignedAt->modify('+' . $value . ' month');
    }

    $today = new DateTimeImmutable(date('Y-m-d'));
    return $today > $endDate ? 'Expiré' : 'En cours';
  };
?>

<section class="member-page">
  <div class="section-head">
    <h1><i class="fa-solid fa-house"></i> Mon logement</h1>
  </div>

  <?php if ($activeRental): ?>
    <article class="card member-rental-active-card">
      <div class="member-rental-status">
        <span class="member-tag active">Logement actif</span>
      </div>
      <h2><?= htmlspecialchars((string) ($activeRental['title'] ?? '')) ?></h2>
      <div class="member-rental-details-grid">
        <div class="member-rental-detail-item">
          <span class="detail-label"><i class="fa-solid fa-location-dot"></i> Adresse</span>
          <span class="detail-value"><?= htmlspecialchars((string) ($activeRental['location_label'] ?? '')) ?></span>
        </div>
        <div class="member-rental-detail-item">
          <span class="detail-label"><i class="fa-solid fa-money-bill-wave"></i> Loyer mensuel</span>
          <span class="detail-value"><?= number_format((float) ($activeRental['price'] ?? 0), 2, ',', ' ') ?> €</span>
        </div>
        <div class="member-rental-detail-item">
          <span class="detail-label"><i class="fa-regular fa-calendar"></i> Attribué le</span>
          <span class="detail-value"><?= date('d/m/Y', strtotime($activeRental['assigned_at'])) ?></span>
        </div>
        <div class="member-rental-detail-item">
          <span class="detail-label"><i class="fa-solid fa-hourglass-half"></i> Durée du bail</span>
          <span class="detail-value"><?= htmlspecialchars($formatLeaseDuration($activeRental)) ?></span>
        </div>
        <div class="member-rental-detail-item">
          <span class="detail-label"><i class="fa-solid fa-clipboard-list"></i> Statut</span>
          <span class="detail-value"><?= htmlspecialchars($leaseStatus($activeRental)) ?></span>
        </div>
      </div>
      <?php if (!empty($activeRental['rental_description'])): ?>
        <div class="member-rental-description">
          <h3>Description du logement</h3>
          <p><?= nl2br(htmlspecialchars((string) $activeRental['rental_description'])) ?></p>
        </div>
      <?php endif; ?>
      <?php if (!empty($activeRental['notes'])): ?>
        <div class="member-rental-notes">
          <h3>Notes</h3>
          <p><?= nl2br(htmlspecialchars((string) $activeRental['notes'])) ?></p>
        </div>
      <?php endif; ?>
    </article>
  <?php else: ?>
    <article class="card member-empty-card">
      <div class="member-empty-icon"><i class="fa-solid fa-house"></i></div>
      <h2>Pas de logement attribué</h2>
      <p>Vous n'avez actuellement aucun logement attribué par l'association.</p>
      <p>Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez faire une demande, contactez l'association.</p>
      <a href="/contact" class="button-link" style="margin-top:16px;">Contacter l'association</a>
    </article>
  <?php endif; ?>

  <?php if (!empty($history)): ?>
    <div class="member-rental-history">
      <h2>Historique</h2>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Logement</th>
              <th>Adresse</th>
              <th>Du</th>
              <th>Durée</th>
              <th>Au</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($history as $h): ?>
              <tr>
                <td><?= htmlspecialchars((string) ($h['title'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($h['location_label'] ?? '')) ?></td>
                <td><?= date('d/m/Y', strtotime($h['assigned_at'])) ?></td>
                <td><?= htmlspecialchars($formatLeaseDuration($h)) ?></td>
                <td><?= !empty($h['released_at']) ? date('d/m/Y', strtotime($h['released_at'])) : '—' ?></td>
                <td>
                  <span class="member-tag <?= ($h['status'] ?? '') === 'active' ? 'active' : 'released' ?>">
                    <?= htmlspecialchars($leaseStatus($h)) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</section>
