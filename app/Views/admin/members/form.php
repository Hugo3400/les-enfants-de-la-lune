<?php
  $isEdit = !empty($member);
  $action = $isEdit ? '/admin/membres/' . (int) $member['id'] . '/update' : '/admin/membres';
  $heading = $isEdit ? 'Modifier le membre' : 'Ajouter un membre';
?>

<section>
  <div class="section-head">
    <h1><?= htmlspecialchars($heading) ?></h1>
    <a class="button-secondary" href="/admin/membres">← Retour à la liste</a>
  </div>

  <article class="card" style="padding:24px;margin-top:12px;">
    <form method="post" action="<?= htmlspecialchars($action) ?>" class="form-grid" style="max-width:700px;">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <label>
          Prénom *
          <input type="text" name="first_name" required value="<?= htmlspecialchars((string) ($member['first_name'] ?? '')) ?>">
        </label>
        <label>
          Nom *
          <input type="text" name="last_name" required value="<?= htmlspecialchars((string) ($member['last_name'] ?? '')) ?>">
        </label>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <label>
          Email
          <input type="email" name="email" value="<?= htmlspecialchars((string) ($member['email'] ?? '')) ?>" placeholder="optionnel">
        </label>
        <label>
          Téléphone
          <input type="text" name="phone" value="<?= htmlspecialchars((string) ($member['phone'] ?? '')) ?>" placeholder="optionnel">
        </label>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
        <label>
          Rôle dans l'association
          <select name="role" required>
            <?php foreach (($roles ?? []) as $roleKey => $roleLabel): ?>
              <option value="<?= htmlspecialchars($roleKey) ?>" <?= ((string) ($member['role'] ?? 'membre')) === $roleKey ? 'selected' : '' ?>>
                <?= htmlspecialchars($roleLabel) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          Statut
          <select name="status" required>
            <?php foreach (($statuses ?? []) as $statusKey => $statusLabel): ?>
              <option value="<?= htmlspecialchars($statusKey) ?>" <?= ((string) ($member['status'] ?? 'active')) === $statusKey ? 'selected' : '' ?>>
                <?= htmlspecialchars($statusLabel) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          Membre depuis
          <input type="date" name="joined_at" value="<?= htmlspecialchars((string) ($member['joined_at'] ?? '')) ?>">
        </label>
      </div>

      <label>
        Compte utilisateur lié
        <select name="user_id">
          <option value="">Aucun (pas de connexion)</option>
          <?php foreach (($users ?? []) as $u): ?>
            <option value="<?= (int) $u['id'] ?>" <?= ((int) ($member['user_id'] ?? 0)) === (int) $u['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) $u['name']) ?> (<?= htmlspecialchars((string) $u['email']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Notes internes
        <textarea name="notes" rows="3" placeholder="Informations complémentaires…"><?= htmlspecialchars((string) ($member['notes'] ?? '')) ?></textarea>
      </label>

      <hr style="border:none;border-top:1px solid var(--line, rgba(188,170,255,.2));margin:20px 0;">
      <h3 style="margin:0 0 14px;font-size:1rem;">Suivi<br></h3>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <label>
          RIB
          <input type="text" name="rib" value="<?= htmlspecialchars((string) ($member['rib'] ?? '')) ?>" placeholder="Référence RIB (optionnel)">
        </label>
        <label>
          Payé
          <select name="paye">
            <option value="">—</option>
            <option value="oui" <?= ($member['paye'] ?? '') === 'oui' ? 'selected' : '' ?>>Oui</option>
            <option value="non" <?= ($member['paye'] ?? '') === 'non' ? 'selected' : '' ?>>Non</option>
          </select>
        </label>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
        <label>
          Recensement BC
          <select name="recensement_bc">
            <option value="">—</option>
            <option value="oui"      <?= ($member['recensement_bc'] ?? '') === 'oui'      ? 'selected' : '' ?>>Oui</option>
            <option value="non"      <?= ($member['recensement_bc'] ?? '') === 'non'      ? 'selected' : '' ?>>Non</option>
            <option value="en_cours" <?= ($member['recensement_bc'] ?? '') === 'en_cours' ? 'selected' : '' ?>>En cours</option>
          </select>
        </label>
        <label>
          Carte
          <select name="carte">
            <option value="">—</option>
            <option value="oui"      <?= ($member['carte'] ?? '') === 'oui'      ? 'selected' : '' ?>>Oui</option>
            <option value="non"      <?= ($member['carte'] ?? '') === 'non'      ? 'selected' : '' ?>>Non</option>
            <option value="en_cours" <?= ($member['carte'] ?? '') === 'en_cours' ? 'selected' : '' ?>>En cours</option>
          </select>
        </label>
        <label>
          Validité carte
          <input type="date" name="carte_validite" value="<?= htmlspecialchars((string) ($member['carte_validite'] ?? '')) ?>">
        </label>
      </div>

      <label>
        Situation
        <input type="text" name="situation" value="<?= htmlspecialchars((string) ($member['situation'] ?? '')) ?>" placeholder="Situation actuelle…">
      </label>

      <label>
        RDV Point situation
        <input type="date" name="rdv_situation" value="<?= htmlspecialchars((string) ($member['rdv_situation'] ?? '')) ?>">
      </label>

      <hr style="border:none;border-top:1px solid var(--line, rgba(188,170,255,.2));margin:20px 0;">
      <h3 style="margin:0 0 14px;font-size:1rem;">Coupons</h3>

      <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;">
        <label>
          Classic Bikes
          <input type="number" name="coupon_classic_bikes" min="0" step="1" value="<?= (int) ($member['coupon_classic_bikes'] ?? 0) ?>">
        </label>
        <label>
          Seaton's Sand
          <input type="number" name="coupon_seaton_sand" min="0" step="1" value="<?= (int) ($member['coupon_seaton_sand'] ?? 0) ?>">
        </label>
        <label>
          Rex's Dinner
          <input type="number" name="coupon_rex_dinner" min="0" step="1" value="<?= (int) ($member['coupon_rex_dinner'] ?? 0) ?>">
        </label>
        <label>
          Yellow Jack
          <input type="number" name="coupon_yellow_jack" min="0" step="1" value="<?= (int) ($member['coupon_yellow_jack'] ?? 0) ?>">
        </label>
        <label>
          Mojito
          <input type="number" name="coupon_mojito" min="0" step="1" value="<?= (int) ($member['coupon_mojito'] ?? 0) ?>">
        </label>
      </div>

      <div class="actions-row">
        <button type="submit"><?= $isEdit ? 'Enregistrer les modifications' : 'Ajouter le membre' ?></button>
        <a class="button-secondary" href="/admin/membres">Annuler</a>
      </div>
    </form>
  </article>

  <?php if ($isEdit): ?>
    <!-- Attribution de logement -->
    <article class="card" style="padding:24px;margin-top:20px;">
      <h2 style="margin:0 0 16px;color:#1b2a4a;"><i class="fa-solid fa-house"></i> Attribution de logement</h2>

      <?php if (!empty($activeRental)): ?>
        <div style="padding:16px;background:#d4edda;border-radius:8px;margin-bottom:16px;">
          <strong>Logement actif :</strong> <?= htmlspecialchars($activeRental['title'] ?? '') ?>
          — <?= htmlspecialchars($activeRental['location_label'] ?? '') ?>
          (depuis le <?= date('d/m/Y', strtotime($activeRental['assigned_at'])) ?>)
          <?php if (!empty($activeRental['lease_duration_value'])): ?>
            — bail : <?= (int) $activeRental['lease_duration_value'] ?>
            <?php
              $activeUnit = (string) ($activeRental['lease_duration_unit'] ?? 'month');
              echo $activeUnit === 'year' ? 'an(s)' : ($activeUnit === 'week' ? 'semaine(s)' : 'mois');
            ?>
          <?php endif; ?>
          <form method="post" action="/admin/membres/<?= (int) $member['id'] ?>/release-rental" style="display:inline;margin-left:12px;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="assignment_id" value="<?= (int) $activeRental['id'] ?>">
            <button type="submit" class="link-danger" onclick="return confirm('Libérer ce logement ?')">Libérer</button>
          </form>
        </div>
      <?php endif; ?>

      <form method="post" action="/admin/membres/<?= (int) $member['id'] ?>/assign-rental" class="form-grid" style="max-width:500px;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
        <label>
          Logement à attribuer
          <select name="rental_id" required>
            <option value="">Choisir un logement…</option>
            <?php foreach (($rentals ?? []) as $rental): ?>
              <option value="<?= (int) $rental['id'] ?>"><?= htmlspecialchars($rental['title']) ?> — <?= htmlspecialchars($rental['location_label']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          Date d'attribution
          <input type="date" name="assigned_at" value="<?= date('Y-m-d') ?>" required>
        </label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <label>
            Durée du bail
            <input type="number" name="lease_duration_value" min="1" max="520" step="1" placeholder="Ex: 12" required>
            <small>Max: 520 semaines, 120 mois, 10 annees.</small>
          </label>
          <label>
            Unité
            <select name="lease_duration_unit" required>
              <option value="week">Semaine(s)</option>
              <option value="month" selected>Mois</option>
              <option value="year">Année(s)</option>
            </select>
          </label>
        </div>
        <label>
          Notes (optionnel)
          <input type="text" name="rental_notes" placeholder="Remarques sur l'attribution…">
        </label>
        <button type="submit">Attribuer le logement</button>
      </form>

      <?php if (!empty($rentalHistory)): ?>
        <h3 style="margin:20px 0 10px;color:#1b2a4a;">Historique</h3>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Logement</th><th>Du</th><th>Durée</th><th>Au</th><th>Statut</th></tr>
            </thead>
            <tbody>
              <?php foreach ($rentalHistory as $h): ?>
                <tr>
                  <td><?= htmlspecialchars($h['title'] ?? '') ?></td>
                  <td><?= date('d/m/Y', strtotime($h['assigned_at'])) ?></td>
                  <td>
                    <?php if (!empty($h['lease_duration_value'])): ?>
                      <?= (int) $h['lease_duration_value'] ?>
                      <?php
                        $historyUnit = (string) ($h['lease_duration_unit'] ?? 'month');
                        echo $historyUnit === 'year' ? 'an(s)' : ($historyUnit === 'week' ? 'semaine(s)' : 'mois');
                      ?>
                    <?php else: ?>
                      —
                    <?php endif; ?>
                  </td>
                  <td><?= !empty($h['released_at']) ? date('d/m/Y', strtotime($h['released_at'])) : '—' ?></td>
                  <td><?= ($h['status'] ?? '') === 'active' ? '<i class="fa-solid fa-circle-check" style="color:#27ae60;"></i> Actif' : '<i class="fa-solid fa-stop" style="color:#95a5a6;"></i> Terminé' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </article>
  <?php endif; ?>
</section>
