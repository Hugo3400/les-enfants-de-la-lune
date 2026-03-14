<?php $isEdit = !empty($rental['id']); ?>

<section class="admin-rental-form-page">
  <div class="section-head">
    <h1><i class="fa-solid fa-house"></i> <?= $isEdit ? 'Modifier une location' : 'Nouvelle location' ?></h1>
    <a class="button-secondary" href="/admin/locations"><i class="fa-solid fa-arrow-left"></i> Retour</a>
  </div>

  <div class="admin-rental-form-layout">
    <article class="card admin-rental-form-card">
      <form method="post" action="<?= htmlspecialchars((string) ($formAction ?? '/admin/locations')) ?>" class="form-grid admin-rental-main-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

        <div class="admin-rental-form-block">
          <h2>Informations du bien</h2>
          <div class="admin-rental-two-cols">
            <label>
              <i class="fa-solid fa-tag"></i> Nom du bien
              <input type="text" name="title" value="<?= htmlspecialchars((string) ($rental['title'] ?? '')) ?>" required>
            </label>

            <label>
              <i class="fa-solid fa-location-dot"></i> Lieu
              <input type="text" name="location_label" value="<?= htmlspecialchars((string) ($rental['location_label'] ?? '')) ?>" required>
            </label>
          </div>
        </div>

        <div class="admin-rental-form-block">
          <h2>Tarification et statut</h2>
          <div class="admin-rental-two-cols">
            <label>
              <i class="fa-solid fa-dollar-sign"></i> Prix ($)
              <input type="number" step="0.01" min="0" name="price" value="<?= htmlspecialchars((string) ($rental['price'] ?? '0')) ?>" required>
            </label>

            <label>
              <i class="fa-solid fa-circle-check"></i> Disponibilite
              <select name="status" required>
                <option value="available" <?= ((string) ($rental['status'] ?? '')) === 'available' ? 'selected' : '' ?>>Disponible</option>
                <option value="unavailable" <?= ((string) ($rental['status'] ?? '')) === 'unavailable' ? 'selected' : '' ?>>Non disponible</option>
              </select>
            </label>
          </div>
        </div>

        <div class="admin-rental-form-block">
          <h2>Description</h2>
          <label>
            <i class="fa-solid fa-align-left"></i> Details (optionnel)
            <textarea name="description" rows="6" placeholder="Ex: Acces, equipements, regles, points de vigilance..."><?= htmlspecialchars((string) ($rental['description'] ?? '')) ?></textarea>
          </label>
        </div>

        <div class="actions-row">
          <button type="submit"><i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Mettre a jour' : 'Creer la location' ?></button>
          <a class="button-secondary" href="/admin/locations">Annuler</a>
        </div>
      </form>
    </article>

    <aside class="card admin-rental-help-card">
      <h2><i class="fa-solid fa-lightbulb"></i> Conseils</h2>
      <ul>
        <li>Nommez le bien avec un format stable: "Motel Sandy Shores - 1".</li>
        <li>Utilisez un lieu standard pour les filtres: "Paleto Bay" ou "Sandy Shores (Route 68)".</li>
        <li>Gardez le statut "Disponible" tant qu'aucune attribution active n'existe.</li>
      </ul>

      <div class="admin-rental-help-chip-wrap">
        <span class="admin-badge">Gestion location</span>
        <span class="admin-badge">Saisie rapide</span>
      </div>
    </aside>
  </div>
</section>

<?php if ($isEdit): ?>
<section class="card admin-rental-assign-section">
  <h2><i class="fa-solid fa-user-tag"></i> Attribution du logement</h2>

  <?php if (!empty($assignee)): ?>
    <div class="assignment-current">
      <div>
        <i class="fa-solid fa-user"></i>
        <strong><?= htmlspecialchars($assignee['first_name'] . ' ' . $assignee['last_name']) ?></strong>
        <span>depuis le <?= date('d/m/Y', strtotime($assignee['assigned_at'])) ?></span>
        <?php if (!empty($assignee['lease_duration_value'])): ?>
          <span>bail : <?= (int) $assignee['lease_duration_value'] ?>
            <?php
              $assigneeUnit = (string) ($assignee['lease_duration_unit'] ?? 'month');
              echo $assigneeUnit === 'year' ? 'an(s)' : ($assigneeUnit === 'week' ? 'semaine(s)' : 'mois');
            ?>
          </span>
        <?php endif; ?>
        <?php if (!empty($assignee['assignment_notes'])): ?>
          <small><i class="fa-solid fa-note-sticky"></i> <?= htmlspecialchars($assignee['assignment_notes']) ?></small>
        <?php endif; ?>
      </div>
      <form method="post" action="/admin/locations/<?= (int) $rental['id'] ?>/release" onsubmit="return confirm('Liberer cette location ?');">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
        <button type="submit" class="btn-danger">
          <i class="fa-solid fa-right-from-bracket"></i> Liberer
        </button>
      </form>
    </div>
  <?php else: ?>
    <p class="admin-rental-hint"><i class="fa-solid fa-circle-info"></i> Aucun membre n'occupe actuellement cette location.</p>
  <?php endif; ?>

  <form method="post" action="/admin/locations/<?= (int) $rental['id'] ?>/assign" class="form-grid admin-rental-assign-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

    <div class="admin-rental-two-cols">
      <label>
        <i class="fa-regular fa-calendar"></i> Date d'attribution
        <input type="date" name="assigned_at" value="<?= date('Y-m-d') ?>" required>
      </label>

      <label>
        <i class="fa-solid fa-users"></i> Attribuer a un membre
        <select name="member_id" required>
          <option value="">- Selectionner un membre -</option>
          <?php foreach (($members ?? []) as $member): ?>
            <option value="<?= (int) $member['id'] ?>"
              <?= (!empty($assignee) && (int) $assignee['member_id'] === (int) $member['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
              <?php if (!empty($member['email'])): ?>(<?= htmlspecialchars($member['email']) ?>)<?php endif; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>

    <div class="admin-rental-two-cols">
      <label>
        <i class="fa-solid fa-hourglass-half"></i> Duree du bail
        <input type="number" name="lease_duration_value" min="1" max="520" step="1" placeholder="Ex: 12" required>
        <small>Max: 520 semaines, 120 mois, 10 annees.</small>
      </label>

      <label>
        <i class="fa-solid fa-calendar-days"></i> Unite
        <select name="lease_duration_unit" required>
          <option value="week">Semaine(s)</option>
          <option value="month" selected>Mois</option>
          <option value="year">Annee(s)</option>
        </select>
      </label>
    </div>

    <label>
      <i class="fa-solid fa-note-sticky"></i> Notes (optionnel)
      <input type="text" name="assignment_notes" placeholder="Remarques sur l'attribution...">
    </label>

    <div class="actions-row">
      <button type="submit"><i class="fa-solid fa-user-plus"></i> Attribuer</button>
    </div>
  </form>
</section>

<?php if (!empty($history)): ?>
<section class="card admin-rental-history-section">
  <h2><i class="fa-solid fa-clock-rotate-left"></i> Historique des attributions</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Membre</th>
          <th>Attribue le</th>
          <th>Duree</th>
          <th>Libere le</th>
          <th>Statut</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($history as $h): ?>
          <tr>
            <td><?= htmlspecialchars($h['first_name'] . ' ' . $h['last_name']) ?></td>
            <td><?= date('d/m/Y', strtotime($h['assigned_at'])) ?></td>
            <td>
              <?php if (!empty($h['lease_duration_value'])): ?>
                <?= (int) $h['lease_duration_value'] ?>
                <?php
                  $historyUnit = (string) ($h['lease_duration_unit'] ?? 'month');
                  echo $historyUnit === 'year' ? 'an(s)' : ($historyUnit === 'week' ? 'semaine(s)' : 'mois');
                ?>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td><?= $h['released_at'] ? date('d/m/Y', strtotime($h['released_at'])) : '-' ?></td>
            <td>
              <span class="status-pill <?= $h['status'] === 'active' ? 'status-available' : 'status-unavailable' ?>">
                <?= $h['status'] === 'active' ? 'En cours' : 'Termine' ?>
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
