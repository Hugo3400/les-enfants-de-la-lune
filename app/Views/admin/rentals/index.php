<section class="admin-rentals-page">
  <div class="section-head">
    <h1><i class="fa-solid fa-house"></i> Gestion des locations</h1>
    <a class="button-link" href="/admin/locations/new"><i class="fa-solid fa-plus"></i> Ajouter une location</a>
  </div>

  <p>Gère la disponibilité des biens, les attributions aux membres et leurs informations.</p>

  <?php if (empty($rentals)): ?>
    <p>Aucune location enregistrée.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Bien</th>
            <th>Lieu</th>
            <th>Prix</th>
            <th>Occupant</th>
            <th>Disponibilité</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rentals as $rental): ?>
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
