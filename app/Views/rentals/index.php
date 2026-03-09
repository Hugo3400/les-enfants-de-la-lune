<section>
  <div class="section-head">
    <h1>Locations immobilières</h1>
  </div>
  <p>Consulte les biens actuellement proposés par l'association.</p>

  <?php if (empty($rentals)): ?>
    <p>Aucune location enregistrée pour le moment.</p>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($rentals as $rental): ?>
        <?php $isAvailable = ((string) $rental['status']) === 'available'; ?>
        <article class="card rental-card">
          <h2><?= htmlspecialchars((string) $rental['title']) ?></h2>
          <p><strong>Lieu :</strong> <?= htmlspecialchars((string) $rental['location_label']) ?></p>
          <p><strong>Tarif :</strong> <?= number_format((float) $rental['price'], 2, ',', ' ') ?> €</p>
          <p>
            <strong>Disponibilité :</strong>
            <span class="status-pill <?= $isAvailable ? 'status-available' : 'status-unavailable' ?>">
              <?= $isAvailable ? 'Disponible' : 'Non disponible' ?>
            </span>
          </p>
          <?php if (!empty($rental['description'])): ?>
            <p><?= nl2br(htmlspecialchars((string) $rental['description'])) ?></p>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
