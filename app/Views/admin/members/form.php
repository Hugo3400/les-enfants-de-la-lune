<?php
  $isEdit = !empty($member);
  $action = $isEdit ? '/admin/membres/' . (int) $member['id'] . '/update' : '/admin/membres';
  $heading = $isEdit
    ? htmlspecialchars(($member['last_name'] ?? '') . ' ' . ($member['first_name'] ?? ''))
    : 'Ajouter un membre';
?>

<style>
.mf-page { display:grid; grid-template-columns: minmax(0,1fr) minmax(0,380px); gap:20px; align-items:start; }
@media (max-width:960px) { .mf-page { grid-template-columns:1fr; } }
.mf-card { background:linear-gradient(160deg,rgba(41,34,81,.92),rgba(32,27,64,.94)); border:1px solid rgba(143,134,248,.18); border-radius:14px; padding:24px; }
.mf-section-title { display:flex; align-items:center; gap:9px; font-size:.78rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--lavender,#c7c2ff); margin:0 0 16px; padding-bottom:10px; border-bottom:1px solid rgba(143,134,248,.15); }
.mf-section-title i { font-size:.85rem; opacity:.8; }
.mf-field-group { display:grid; gap:12px; }
.mf-row-2 { grid-template-columns:1fr 1fr; }
.mf-row-3 { grid-template-columns:1fr 1fr 1fr; }
.mf-row-5 { grid-template-columns:repeat(5,1fr); }
@media (max-width:640px) { .mf-row-2,.mf-row-3,.mf-row-5 { grid-template-columns:1fr; } }
.mf-label { font-size:.78rem; font-weight:600; color:var(--ink-soft,#cbc3f7); display:flex; flex-direction:column; gap:5px; }
.mf-label input, .mf-label select, .mf-label textarea {
  background:rgba(255,255,255,.06); border:1px solid rgba(143,134,248,.22);
  border-radius:8px; color:var(--ink,#f2efff); padding:8px 11px; font-size:.88rem;
  transition:border-color .15s;
}
.mf-label input:focus, .mf-label select:focus, .mf-label textarea:focus {
  outline:none; border-color:rgba(143,134,248,.55); background:rgba(255,255,255,.09);
}
.mf-label input[type=number] { text-align:center; }
/* coupon +/- */
.coupon-wrap { display:flex; align-items:center; gap:0; border:1px solid rgba(143,134,248,.22); border-radius:8px; overflow:hidden; background:rgba(255,255,255,.06); }
.coupon-wrap button { background:rgba(143,134,248,.12); border:none; color:var(--lavender,#c7c2ff); font-size:1rem; width:30px; height:36px; cursor:pointer; flex-shrink:0; }
.coupon-wrap button:hover { background:rgba(143,134,248,.28); }
.coupon-wrap input { border:none; background:transparent; text-align:center; color:var(--ink,#f2efff); font-size:.88rem; width:100%; min-width:0; padding:0; }
/* badge statut */
.mf-status-badge { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:20px; font-size:.78rem; font-weight:700; }
.mf-badge-active   { background:rgba(39,174,96,.15); color:#4ee899; border:1px solid rgba(39,174,96,.3); }
.mf-badge-inactive { background:rgba(149,165,166,.12); color:#bdc3c7; border:1px solid rgba(149,165,166,.2); }
/* logement actif */
.mf-active-rental { display:flex; align-items:center; gap:10px; padding:14px 16px; border-radius:10px; background:rgba(39,174,96,.1); border:1px solid rgba(39,174,96,.25); margin-bottom:16px; }
.mf-active-rental-info { flex:1; min-width:0; font-size:.85rem; }
.mf-active-rental-info strong { display:block; font-size:.95rem; color:#4ee899; margin-bottom:2px; }
/* hr */
.mf-sep { border:none; border-top:1px solid rgba(143,134,248,.15); margin:18px 0 16px; }
</style>

<section>
  <div class="section-head" style="margin-bottom:16px;">
    <div>
      <h1 style="margin:0;"><?= $isEdit ? '<i class="fa-solid fa-user-pen" style="font-size:.85em;opacity:.7;margin-right:8px;"></i>' : '' ?><?= $heading ?></h1>
      <?php if ($isEdit): ?>
        <div style="margin-top:6px;display:flex;align-items:center;gap:8px;">
          <?php $memberStatus = (string)($member['status'] ?? 'active'); ?>
          <span class="mf-status-badge <?= $memberStatus === 'active' ? 'mf-badge-active' : 'mf-badge-inactive' ?>">
            <i class="fa-solid fa-circle" style="font-size:.45rem;"></i>
            <?= $memberStatus === 'active' ? 'Actif' : ucfirst($memberStatus) ?>
          </span>
          <?php if (!empty($member['joined_at'])): ?>
            <span style="font-size:.78rem;color:var(--ink-soft,#cbc3f7);">Membre depuis le <?= date('d/m/Y', strtotime((string)$member['joined_at'])) ?></span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    <a class="button-secondary" href="/admin/membres">← Retour à la liste</a>
  </div>

  <div class="mf-page">

    <!-- ── Colonne gauche : formulaire ─────────────────────────── -->
    <div>
      <form method="post" action="<?= htmlspecialchars($action) ?>" id="member-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

        <!-- Identité -->
        <div class="mf-card" style="margin-bottom:16px;">
          <p class="mf-section-title"><i class="fa-solid fa-id-card"></i> Identité</p>
          <div class="mf-field-group">
            <div class="mf-field-group mf-row-2">
              <label class="mf-label">Prénom *<input type="text" name="first_name" required value="<?= htmlspecialchars((string) ($member['first_name'] ?? '')) ?>"></label>
              <label class="mf-label">Nom *<input type="text" name="last_name" required value="<?= htmlspecialchars((string) ($member['last_name'] ?? '')) ?>"></label>
            </div>
            <div class="mf-field-group mf-row-2">
              <label class="mf-label">Email<input type="email" name="email" value="<?= htmlspecialchars((string) ($member['email'] ?? '')) ?>" placeholder="optionnel"></label>
              <label class="mf-label">Téléphone<input type="text" name="phone" value="<?= htmlspecialchars((string) ($member['phone'] ?? '')) ?>" placeholder="optionnel"></label>
            </div>
            <div class="mf-field-group mf-row-3">
              <label class="mf-label">Rôle
                <select name="role" required>
                  <?php foreach (($roles ?? []) as $roleKey => $roleLabel): ?>
                    <option value="<?= htmlspecialchars($roleKey) ?>" <?= ((string) ($member['role'] ?? 'membre')) === $roleKey ? 'selected' : '' ?>><?= htmlspecialchars($roleLabel) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label class="mf-label">Statut
                <select name="status" required>
                  <?php foreach (($statuses ?? []) as $sk => $sl): ?>
                    <option value="<?= htmlspecialchars($sk) ?>" <?= ((string) ($member['status'] ?? 'active')) === $sk ? 'selected' : '' ?>><?= htmlspecialchars($sl) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label class="mf-label">Membre depuis<input type="date" name="joined_at" value="<?= htmlspecialchars((string) ($member['joined_at'] ?? '')) ?>"></label>
            </div>
            <label class="mf-label">Compte utilisateur lié
              <select name="user_id">
                <option value="">Aucun (pas de connexion)</option>
                <?php foreach (($users ?? []) as $u): ?>
                  <option value="<?= (int) $u['id'] ?>" <?= ((int) ($member['user_id'] ?? 0)) === (int) $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $u['name']) ?> (<?= htmlspecialchars((string) $u['email']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </label>
            <label class="mf-label">Notes internes<textarea name="notes" rows="2" placeholder="Informations complémentaires…"><?= htmlspecialchars((string) ($member['notes'] ?? '')) ?></textarea></label>
          </div>
        </div>

        <!-- Suivi -->
        <div class="mf-card" style="margin-bottom:16px;">
          <p class="mf-section-title"><i class="fa-solid fa-chart-line"></i> Suivi</p>
          <div class="mf-field-group">
            <div class="mf-field-group mf-row-3">
              <label class="mf-label">Recensement BC
                <select name="recensement_bc">
                  <option value="">—</option>
                  <option value="oui"      <?= ($member['recensement_bc'] ?? '') === 'oui'      ? 'selected' : '' ?>>Oui</option>
                  <option value="non"      <?= ($member['recensement_bc'] ?? '') === 'non'      ? 'selected' : '' ?>>Non</option>
                  <option value="en_cours" <?= ($member['recensement_bc'] ?? '') === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                </select>
              </label>
              <label class="mf-label">Carte
                <select name="carte">
                  <option value="">—</option>
                  <option value="oui"      <?= ($member['carte'] ?? '') === 'oui'      ? 'selected' : '' ?>>Oui</option>
                  <option value="non"      <?= ($member['carte'] ?? '') === 'non'      ? 'selected' : '' ?>>Non</option>
                  <option value="en_cours" <?= ($member['carte'] ?? '') === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                </select>
              </label>
              <label class="mf-label">Validité carte<input type="date" name="carte_validite" value="<?= htmlspecialchars((string) ($member['carte_validite'] ?? '')) ?>"></label>
            </div>
            <div class="mf-field-group mf-row-2">
              <label class="mf-label">RIB<input type="text" name="rib" value="<?= htmlspecialchars((string) ($member['rib'] ?? '')) ?>" placeholder="Référence RIB (optionnel)"></label>
              <label class="mf-label">Payé
                <select name="paye">
                  <option value="">—</option>
                  <option value="oui" <?= ($member['paye'] ?? '') === 'oui' ? 'selected' : '' ?>>Oui</option>
                  <option value="non" <?= ($member['paye'] ?? '') === 'non' ? 'selected' : '' ?>>Non</option>
                </select>
              </label>
            </div>
            <label class="mf-label">Situation<input type="text" name="situation" value="<?= htmlspecialchars((string) ($member['situation'] ?? '')) ?>" placeholder="Situation actuelle…"></label>
            <label class="mf-label">RDV Point situation<input type="date" name="rdv_situation" value="<?= htmlspecialchars((string) ($member['rdv_situation'] ?? '')) ?>"></label>
          </div>
        </div>

        <!-- Coupons -->
        <div class="mf-card" style="margin-bottom:20px;">
          <p class="mf-section-title"><i class="fa-solid fa-ticket"></i> Coupons</p>
          <div class="mf-field-group mf-row-5">
            <?php
              $coupons = [
                'coupon_classic_bikes' => 'Classic Bikes',
                'coupon_seaton_sand'   => "Seaton's Sand",
                'coupon_rex_dinner'    => "Rex's Dinner",
                'coupon_yellow_jack'   => 'Yellow Jack',
                'coupon_mojito'        => 'Mojito',
              ];
            ?>
            <?php foreach ($coupons as $cName => $cLabel): ?>
              <label class="mf-label"><?= htmlspecialchars($cLabel) ?>
                <div class="coupon-wrap">
                  <button type="button" onclick="stepCoupon('<?= $cName ?>',−1)">−</button>
                  <input type="number" name="<?= $cName ?>" id="<?= $cName ?>" min="0" step="1" value="<?= (int) ($member[$cName] ?? 0) ?>">
                  <button type="button" onclick="stepCoupon('<?= $cName ?>',1)">+</button>
                </div>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <button type="submit" style="flex:1;min-width:160px;"><?= $isEdit ? '<i class="fa-solid fa-floppy-disk"></i> Enregistrer' : 'Ajouter le membre' ?></button>
          <a class="button-secondary" href="/admin/membres" style="flex:1;min-width:120px;text-align:center;">Annuler</a>
        </div>
      </form>
    </div>

    <!-- ── Colonne droite : logement ───────────────────────────── -->
    <?php if ($isEdit): ?>
    <div>

      <!-- Logement actif -->
      <div class="mf-card" style="margin-bottom:16px;">
        <p class="mf-section-title"><i class="fa-solid fa-house"></i> Logement</p>

        <?php if (!empty($activeRental)): ?>
          <div class="mf-active-rental">
            <i class="fa-solid fa-circle-check" style="color:#4ee899;font-size:1.3rem;flex-shrink:0;"></i>
            <div class="mf-active-rental-info">
              <strong><?= htmlspecialchars($activeRental['title'] ?? '') ?></strong>
              <span style="color:var(--ink-soft,#cbc3f7);font-size:.78rem;"><?= htmlspecialchars($activeRental['location_label'] ?? '') ?></span>
              <div style="margin-top:4px;font-size:.78rem;color:var(--ink-soft,#cbc3f7);">
                Depuis le <?= date('d/m/Y', strtotime($activeRental['assigned_at'])) ?>
                <?php if (!empty($activeRental['lease_duration_value'])): ?>
                  · Bail <?= (int) $activeRental['lease_duration_value'] ?>
                  <?php $au = (string)($activeRental['lease_duration_unit'] ?? 'month'); echo $au === 'year' ? 'an(s)' : ($au === 'week' ? 'semaine(s)' : 'mois'); ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <form method="post" action="/admin/membres/<?= (int) $member['id'] ?>/release-rental">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="assignment_id" value="<?= (int) $activeRental['id'] ?>">
            <button type="submit" class="link-danger" style="font-size:.82rem;width:100%;padding:8px;" onclick="return confirm('Libérer ce logement ?')">
              <i class="fa-solid fa-door-open"></i> Libérer le logement
            </button>
          </form>
          <hr class="mf-sep">
        <?php else: ?>
          <p style="font-size:.82rem;color:var(--ink-soft,#cbc3f7);margin:0 0 14px;text-align:center;"><i class="fa-solid fa-house-circle-xmark" style="opacity:.5;"></i> Aucun logement attribué</p>
        <?php endif; ?>

        <!-- Formulaire attribution -->
        <form method="post" action="/admin/membres/<?= (int) $member['id'] ?>/assign-rental" class="mf-field-group" style="gap:10px;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
          <label class="mf-label">Attribuer un logement
            <select name="rental_id" required>
              <option value="">Choisir…</option>
              <?php foreach (($rentals ?? []) as $rental): ?>
                <option value="<?= (int) $rental['id'] ?>"><?= htmlspecialchars($rental['title']) ?> — <?= htmlspecialchars($rental['location_label']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="mf-label">Date d'attribution<input type="date" name="assigned_at" value="<?= date('Y-m-d') ?>" required></label>
          <div class="mf-field-group mf-row-2">
            <label class="mf-label">Durée
              <input type="number" name="lease_duration_value" min="1" max="520" step="1" placeholder="Ex: 2" required>
            </label>
            <label class="mf-label">Unité
              <select name="lease_duration_unit" required>
                <option value="week">Semaine(s)</option>
                <option value="month" selected>Mois</option>
                <option value="year">Année(s)</option>
              </select>
            </label>
          </div>
          <label class="mf-label">Notes<input type="text" name="rental_notes" placeholder="Remarques…"></label>
          <button type="submit" style="width:100%;"><i class="fa-solid fa-house-circle-check"></i> Attribuer</button>
        </form>
      </div>

      <!-- Historique logements -->
      <?php if (!empty($rentalHistory)): ?>
      <div class="mf-card">
        <p class="mf-section-title"><i class="fa-solid fa-clock-rotate-left"></i> Historique logements</p>
        <div style="display:flex;flex-direction:column;gap:8px;">
          <?php foreach ($rentalHistory as $h): ?>
            <?php $isActive = ($h['status'] ?? '') === 'active'; ?>
            <div style="padding:10px 12px;border-radius:8px;background:rgba(255,255,255,<?= $isActive ? '.07' : '.03' ?>);border:1px solid rgba(143,134,248,<?= $isActive ? '.25' : '.1' ?>);font-size:.8rem;">
              <div style="display:flex;align-items:center;gap:7px;margin-bottom:3px;">
                <i class="fa-solid fa-circle-<?= $isActive ? 'check' : 'stop' ?>" style="color:<?= $isActive ? '#4ee899' : '#95a5a6' ?>;font-size:.6rem;"></i>
                <strong><?= htmlspecialchars($h['title'] ?? '') ?></strong>
              </div>
              <div style="color:var(--ink-soft,#cbc3f7);line-height:1.6;">
                <?= date('d/m/Y', strtotime($h['assigned_at'])) ?>
                <?php if (!empty($h['released_at'])): ?> → <?= date('d/m/Y', strtotime($h['released_at'])) ?><?php endif; ?>
                <?php if (!empty($h['lease_duration_value'])): ?>
                  · <?= (int)$h['lease_duration_value'] ?> <?php $hu = (string)($h['lease_duration_unit'] ?? 'month'); echo $hu === 'year' ? 'an(s)' : ($hu === 'week' ? 'sem.' : 'mois'); ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>
    <?php endif; ?>

  </div>
</section>

<script>
function stepCoupon(id, delta) {
  const el = document.getElementById(id);
  const v = parseInt(el.value, 10) || 0;
  el.value = Math.max(0, v + delta);
}
</script>
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
