<?php $isEdit = !empty($rental['id']); ?>
<section class="card admin-rental-form-page">
  <h1><i class="fa-solid fa-house"></i> <?= $isEdit ? 'Modifier une location' : 'Nouvelle location' ?></h1>

  <form method="post" action="<?= htmlspecialchars((string) ($formAction ?? '/admin/locations')) ?>" class="form-grid">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

    <label>
      <i class="fa-solid fa-tag"></i> Nom du bien
      <input type="text" name="title" value="<?= htmlspecialchars((string) ($rental['title'] ?? '')) ?>" required>
    </label>

    <label>
      <i class="fa-solid fa-location-dot"></i> Lieu
      <input type="text" name="location_label" value="<?= htmlspecialchars((string) ($rental['location_label'] ?? '')) ?>" required>
    </label>

    <label>
      <i class="fa-solid fa-dollar-sign"></i> Prix ($)
      <input type="number" step="0.01" min="0" name="price" value="<?= htmlspecialchars((string) ($rental['price'] ?? '0')) ?>" required>
    </label>

    <label>
      <i class="fa-solid fa-circle-check"></i> Disponibilité
      <select name="status" required>
        <option value="available" <?= ((string) ($rental['status'] ?? '')) === 'available' ? 'selected' : '' ?>>Disponible</option>
        <option value="unavailable" <?= ((string) ($rental['status'] ?? '')) === 'unavailable' ? 'selected' : '' ?>>Non disponible</option>
      </select>
    </label>

    <label>
      <i class="fa-solid fa-align-left"></i> Description
      <textarea name="description" rows="5"><?= htmlspecialchars((string) ($rental['description'] ?? '')) ?></textarea>
    </label>

    <div class="actions-row">
      <button type="submit"><i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Mettre à jour' : 'Créer la location' ?></button>
      <a class="button-secondary" href="/admin/locations"><i class="fa-solid fa-arrow-left"></i> Retour</a>
    </div>
  </form>
</section>

<?php if ($isEdit): ?>
<!-- Attribution à un membre -->
<section class="card admin-rental-assign-section" style="margin-top: 2rem;">
  <h2><i class="fa-solid fa-user-tag"></i> Attribution du logement</h2>

  <?php if (!empty($assignee)): ?>
    <div class="assignment-current" style="background: var(--violet-900, #1e1743); color: #fff; padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
      <div>
        <i class="fa-solid fa-user"></i>
        <strong><?= htmlspecialchars($assignee['first_name'] . ' ' . $assignee['last_name']) ?></strong>
        <span style="opacity:.7; margin-left:.5rem;">depuis le <?= date('d/m/Y', strtotime($assignee['assigned_at'])) ?></span>
        <?php if (!empty($assignee['assignment_notes'])): ?>
          <br><small style="opacity:.6;"><i class="fa-solid fa-note-sticky"></i> <?= htmlspecialchars($assignee['assignment_notes']) ?></small>
        <?php endif; ?>
      </div>
      <form method="post" action="/admin/locations/<?= (int) $rental['id'] ?>/release" onsubmit="return confirm('Libérer cette location ?');" style="margin:0;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
        <button type="submit" class="btn-danger" style="background:#ff5d8f; color:#fff; border:none; padding:.5rem 1rem; border-radius:6px; cursor:pointer;">
          <i class="fa-solid fa-right-from-bracket"></i> Libérer
        </button>
      </form>
    </div>
  <?php else: ?>
    <p style="color: var(--lavender, #c7c2ff); margin-bottom: 1rem;">
      <i class="fa-solid fa-circle-info"></i> Aucun membre n'occupe actuellement cette location.
    </p>
  <?php endif; ?>

  <form method="post" action="/admin/locations/<?= (int) $rental['id'] ?>/assign" class="form-grid" style="gap: 1rem;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

    <label>
      <i class="fa-solid fa-users"></i> Attribuer à un membre
      <select name="member_id" required>
        <option value="">— Sélectionner un membre —</option>
        <?php foreach (($members ?? []) as $member): ?>
          <option value="<?= (int) $member['id'] ?>"
            <?= (!empty($assignee) && (int) $assignee['member_id'] === (int) $member['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
            <?php if (!empty($member['email'])): ?>(<?= htmlspecialchars($member['email']) ?>)<?php endif; ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>
      <i class="fa-solid fa-note-sticky"></i> Notes (optionnel)
      <input type="text" name="assignment_notes" placeholder="Remarques sur l'attribution…">
    </label>

    <div class="actions-row">
      <button type="submit"><i class="fa-solid fa-user-plus"></i> Attribuer</button>
    </div>
  </form>
</section>

<?php if (!empty($history)): ?>
<section class="card" style="margin-top: 2rem;">
  <h2><i class="fa-solid fa-clock-rotate-left"></i> Historique des attributions</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Membre</th>
          <th>Attribué le</th>
          <th>Libéré le</th>
          <th>Statut</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($history as $h): ?>
          <tr>
            <td><?= htmlspecialchars($h['first_name'] . ' ' . $h['last_name']) ?></td>
            <td><?= date('d/m/Y', strtotime($h['assigned_at'])) ?></td>
            <td><?= $h['released_at'] ? date('d/m/Y', strtotime($h['released_at'])) : '—' ?></td>
            <td>
              <span class="status-pill <?= $h['status'] === 'active' ? 'status-available' : 'status-unavailable' ?>">
                <?= $h['status'] === 'active' ? 'En cours' : 'Terminé' ?>
              </span>
            </td>
            <td><?= htmlspecialchars((string) ($h['notes'] ?? '')) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php endif; ?>
<?php endif; ?>
