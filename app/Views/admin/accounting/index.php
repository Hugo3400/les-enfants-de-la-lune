<section class="admin-accounting-page">
  <?php $accountsByCode = []; ?>
  <?php foreach (($accounts ?? []) as $account): ?>
    <?php $accountsByCode[(string) ($account['code'] ?? '')] = (string) ($account['label'] ?? ''); ?>
  <?php endforeach; ?>

  <?php $income = (float) ($totals['income'] ?? 0); ?>
  <?php $expense = (float) ($totals['expense'] ?? 0); ?>
  <?php $balance = (float) ($totals['balance'] ?? 0); ?>
  <?php $periodIncome = (float) ($periodTotals['income'] ?? 0); ?>
  <?php $periodExpense = (float) ($periodTotals['expense'] ?? 0); ?>
  <?php $periodBalance = (float) ($periodTotals['balance'] ?? 0); ?>
  <?php $activeWeek = (string) (($filters['week'] ?? '')); ?>
  <?php $activeType = (string) (($filters['type'] ?? 'all')); ?>
  <?php $activeStatus = (string) (($filters['status'] ?? 'all')); ?>
  <?php $activePaymentMethod = (string) (($filters['payment_method'] ?? 'all')); ?>
  <?php $activeAccountCode = (string) (($filters['account_code'] ?? 'all')); ?>
  <?php $activePartnerTag = (string) (($filters['partner_tag'] ?? 'all')); ?>
  <?php $entriesCount = count($entries); ?>
  <?php $validatedCount = 0; ?>
  <?php $draftCount = 0; ?>
  <?php $partnerTotals = ['classic' => 0.0, 'yellow' => 0.0, 'rex' => 0.0, 'mojito' => 0.0, 'seaton' => 0.0]; ?>
  <?php foreach ($entries as $entry): ?>
    <?php $isValidated = ((string) ($entry['entry_status'] ?? 'draft')) === 'validated'; ?>
    <?php if ($isValidated): ?>
      <?php $validatedCount++; ?>
    <?php else: ?>
      <?php $draftCount++; ?>
    <?php endif; ?>
    <?php
        $partnerTag = (string) ($entry['partner_tag'] ?? 'none');
      $signedAmount = ((string) ($entry['entry_type'] ?? '') === 'income')
        ? (float) ($entry['amount'] ?? 0)
        : -1 * (float) ($entry['amount'] ?? 0);
        if (isset($partnerTotals[$partnerTag])) {
          $partnerTotals[$partnerTag] += $signedAmount;
      }
    ?>
  <?php endforeach; ?>
  <?php $averageTicket = $entriesCount > 0 ? ($periodIncome + $periodExpense) / $entriesCount : 0.0; ?>

  <?php $periodLabel = 'Toutes les semaines'; ?>
  <?php foreach (($weekOptions ?? []) as $weekOption): ?>
    <?php if (((string) ($weekOption['key'] ?? '')) === $activeWeek): ?>
      <?php $periodLabel = (string) ($weekOption['label'] ?? $periodLabel); ?>
    <?php endif; ?>
  <?php endforeach; ?>

  <?php
    $activeFilterBadges = [];
    if ($activeWeek !== '') {
        $activeFilterBadges[] = 'Periode: ' . $periodLabel;
    }
    if ($activeType !== 'all') {
        $activeFilterBadges[] = 'Type: ' . ($activeType === 'income' ? 'Recettes' : 'Depenses');
    }
    if ($activeStatus !== 'all') {
        $activeFilterBadges[] = 'Statut: ' . ($activeStatus === 'validated' ? 'Validee' : 'Brouillon');
    }
    if ($activePaymentMethod !== 'all') {
        $activeFilterBadges[] = 'Paiement: ' . ((string) ($paymentMethods[$activePaymentMethod] ?? $activePaymentMethod));
    }
    if ($activeAccountCode !== 'all') {
        $activeFilterBadges[] = 'Compte: ' . $activeAccountCode;
    }
    if ($activePartnerTag !== 'all') {
        $activeFilterBadges[] = 'Partenaire: ' . ((string) (($partnerOptions[$activePartnerTag] ?? $activePartnerTag)));
    }
  ?>

  <div class="card accounting-hero">
    <div class="accounting-hero-head">
      <div>
        <h1>Comptabilite</h1>
        <p>Pilotage des recettes, depenses et solde de tresorerie.</p>
      </div>
      <div class="accounting-head-actions">
        <button type="button" class="button-link accounting-open-modal" data-modal-open="entryModal">+ Nouvelle ecriture</button>
        <a class="button-secondary" href="/admin/comptabilite/export.csv?<?= htmlspecialchars(http_build_query([
          'week' => $activeWeek,
          'type' => $activeType,
          'status' => $activeStatus,
          'payment_method' => $activePaymentMethod,
          'account_code' => $activeAccountCode,
          'partner_tag' => $activePartnerTag,
        ])) ?>">Exporter CSV</a>
      </div>
    </div>

    <div class="accounting-hero-meta">
      <span class="admin-badge">Journal · Tresorerie</span>
      <span class="admin-badge"><?= $entriesCount ?> ecriture<?= $entriesCount > 1 ? 's' : '' ?></span>
      <span class="admin-badge">Periode: <?= htmlspecialchars($periodLabel) ?></span>
    </div>
  </div>

  <div class="stats-grid admin-stats-grid">
    <article class="card admin-stat-card">
      <p>Produits (global)</p>
      <strong><?= number_format($income, 2, ',', ' ') ?> €</strong>
    </article>
    <article class="card admin-stat-card">
      <p>Charges (global)</p>
      <strong><?= number_format($expense, 2, ',', ' ') ?> €</strong>
    </article>
    <article class="card admin-stat-card">
      <p>Résultat cumulé</p>
      <strong><?= number_format($balance, 2, ',', ' ') ?> €</strong>
    </article>
  </div>

  <article class="card accounting-toolbar">
    <div class="accounting-toolbar-head">
      <h2>Filtres du journal</h2>
      <?php if (!empty($activeFilterBadges)): ?>
        <div class="accounting-active-filters">
          <?php foreach ($activeFilterBadges as $badge): ?>
            <span class="accounting-filter-badge"><?= htmlspecialchars($badge) ?></span>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <span class="accounting-no-filter">Aucun filtre actif</span>
      <?php endif; ?>
    </div>
    <form method="get" action="/admin/comptabilite" class="accounting-filters">
      <label>
        Semaine
        <select name="week">
          <option value="">Toutes les semaines</option>
          <?php foreach (($weekOptions ?? []) as $weekOption): ?>
            <?php $weekKey = (string) ($weekOption['key'] ?? ''); ?>
            <option value="<?= htmlspecialchars($weekKey) ?>" <?= $activeWeek === $weekKey ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) ($weekOption['label'] ?? $weekKey)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Type
        <select name="type">
          <option value="all" <?= $activeType === 'all' ? 'selected' : '' ?>>Tous</option>
          <option value="income" <?= $activeType === 'income' ? 'selected' : '' ?>>Recettes</option>
          <option value="expense" <?= $activeType === 'expense' ? 'selected' : '' ?>>Dépenses</option>
        </select>
      </label>

      <label>
        Statut
        <select name="status">
          <option value="all" <?= $activeStatus === 'all' ? 'selected' : '' ?>>Tous</option>
          <option value="draft" <?= $activeStatus === 'draft' ? 'selected' : '' ?>>Brouillon</option>
          <option value="validated" <?= $activeStatus === 'validated' ? 'selected' : '' ?>>Validée</option>
        </select>
      </label>

      <label>
        Paiement
        <select name="payment_method">
          <option value="all" <?= $activePaymentMethod === 'all' ? 'selected' : '' ?>>Tous</option>
          <?php foreach (($paymentMethods ?? []) as $methodCode => $methodLabel): ?>
            <option value="<?= htmlspecialchars((string) $methodCode) ?>" <?= $activePaymentMethod === (string) $methodCode ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) $methodLabel) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Partenaire
        <select name="partner_tag">
          <option value="all" <?= $activePartnerTag === 'all' ? 'selected' : '' ?>>Tous</option>
          <?php foreach (($partnerOptions ?? []) as $partnerCode => $partnerLabel): ?>
            <option value="<?= htmlspecialchars((string) $partnerCode) ?>" <?= $activePartnerTag === (string) $partnerCode ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) $partnerLabel) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Compte
        <select name="account_code">
          <option value="all" <?= $activeAccountCode === 'all' ? 'selected' : '' ?>>Tous les comptes</option>
          <?php foreach (($accounts ?? []) as $account): ?>
            <?php $accCode = (string) ($account['code'] ?? ''); ?>
            <option value="<?= htmlspecialchars($accCode) ?>" <?= $activeAccountCode === $accCode ? 'selected' : '' ?>>
              <?= htmlspecialchars($accCode . ' - ' . (string) ($account['label'] ?? '')) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <div class="actions-row accounting-filter-actions">
        <button type="submit">Appliquer</button>
        <a class="button-secondary" href="/admin/comptabilite">Réinitialiser</a>
      </div>
    </form>
  </article>

  <div class="stats-grid accounting-summary-grid">
    <article class="card accounting-kpi kpi-credit">
      <p>Crédits période (produits)</p>
      <strong><?= number_format($periodIncome, 2, ',', ' ') ?> €</strong>
      <small><?= htmlspecialchars($periodLabel) ?></small>
    </article>
    <article class="card accounting-kpi kpi-debit">
      <p>Débits période (charges)</p>
      <strong><?= number_format($periodExpense, 2, ',', ' ') ?> €</strong>
      <small><?= htmlspecialchars($periodLabel) ?></small>
    </article>
    <article class="card accounting-kpi <?= $periodBalance >= 0 ? 'kpi-positive' : 'kpi-negative' ?>">
      <p>Résultat de période</p>
      <strong><?= number_format($periodBalance, 2, ',', ' ') ?> €</strong>
      <small><?= $entriesCount ?> écriture<?= $entriesCount > 1 ? 's' : '' ?> sélectionnée<?= $entriesCount > 1 ? 's' : '' ?></small>
    </article>
  </div>

  <div class="stats-grid accounting-summary-grid accounting-summary-grid-extended">
    <article class="card accounting-kpi">
      <p>Écritures validées</p>
      <strong><?= $validatedCount ?></strong>
      <small><?= $draftCount ?> brouillon<?= $draftCount > 1 ? 's' : '' ?></small>
    </article>
    <article class="card accounting-kpi">
      <p>Ticket moyen</p>
      <strong><?= number_format($averageTicket, 2, ',', ' ') ?> €</strong>
      <small>Recettes + dépenses / écritures</small>
    </article>
    <article class="card accounting-kpi">
      <p>Compte actif</p>
      <strong><?= htmlspecialchars($activeAccountCode === 'all' ? 'Tous' : $activeAccountCode) ?></strong>
      <small>
        <?= htmlspecialchars($activePaymentMethod === 'all' ? 'Tous paiements' : ((string) ($paymentMethods[$activePaymentMethod] ?? $activePaymentMethod))) ?>
        ·
        <?= htmlspecialchars($activePartnerTag === 'all' ? 'Tous partenaires' : ((string) (($partnerOptions[$activePartnerTag] ?? $activePartnerTag)))) ?>
      </small>
    </article>
  </div>

  <article class="card">
    <div class="section-head">
      <h2>Sous-totaux partenaires (période filtrée)</h2>
      <span class="admin-badge"><?= htmlspecialchars($periodLabel) ?></span>
    </div>
    <div class="accounting-partner-grid">
      <?php foreach ($partnerTotals as $partnerCode => $partnerTotal): ?>
        <div class="accounting-partner-item <?= $partnerTotal >= 0 ? 'is-positive' : 'is-negative' ?>">
          <span><?= htmlspecialchars((string) ($partnerOptions[$partnerCode] ?? $partnerCode)) ?></span>
          <strong><?= number_format($partnerTotal, 2, ',', ' ') ?> €</strong>
        </div>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="card">
    <div class="section-head">
      <h2>Balance hebdomadaire (12 dernières semaines)</h2>
    </div>
    <div class="table-wrap accounting-table-wrap">
      <table class="accounting-weekly-table">
        <thead>
          <tr>
            <th>Semaine</th>
            <th>Période</th>
            <th>Débits</th>
            <th>Crédits</th>
            <th>Résultat</th>
            <th>Écritures</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($weeklyBalances ?? [])): ?>
            <tr>
              <td colspan="6" class="accounting-empty-row">Aucune activité hebdomadaire à afficher.</td>
            </tr>
          <?php else: ?>
            <?php foreach (($weeklyBalances ?? []) as $weekLine): ?>
              <?php $weekBalance = (float) ($weekLine['balance'] ?? 0); ?>
              <tr>
                <td><?= htmlspecialchars((string) ($weekLine['week_label'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($weekLine['start'] ?? '')) ?> → <?= htmlspecialchars((string) ($weekLine['end'] ?? '')) ?></td>
                <td class="amount-col amount-debit"><?= number_format((float) ($weekLine['expense'] ?? 0), 2, ',', ' ') ?> €</td>
                <td class="amount-col amount-credit"><?= number_format((float) ($weekLine['income'] ?? 0), 2, ',', ' ') ?> €</td>
                <td class="amount-col amount-balance <?= $weekBalance >= 0 ? 'is-positive' : 'is-negative' ?>"><?= number_format($weekBalance, 2, ',', ' ') ?> €</td>
                <td><?= (int) ($weekLine['count'] ?? 0) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </article>

  <article class="card">
    <div class="section-head">
      <h2>Journal de trésorerie</h2>
      <span class="admin-badge"><?= htmlspecialchars($periodLabel) ?></span>
    </div>
    <p class="accounting-note">Lecture comptable: débit = sortie de trésorerie (charge), crédit = entrée de trésorerie (produit).</p>

    <div class="table-wrap accounting-table-wrap">
      <table class="accounting-journal">
        <thead>
          <tr>
            <th>N°</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Journal</th>
            <th>Pièce</th>
            <th>Type</th>
            <th>Compte</th>
            <th>Paiement</th>
            <th>Partenaire</th>
            <th>Libellé</th>
            <th>Débit</th>
            <th>Crédit</th>
            <th>Solde cumulé</th>
            <th>Notes</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($entries)): ?>
            <tr>
              <td colspan="15" class="accounting-empty-row">Aucune écriture sur cette période.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($entries as $entry): ?>
              <?php $isIncome = ((string) $entry['entry_type']) === 'income'; ?>
              <?php $runningBalance = (float) ($entry['running_balance'] ?? 0); ?>
              <?php $isValidated = ((string) ($entry['entry_status'] ?? 'draft')) === 'validated'; ?>
              <?php $entryAccountCode = (string) ($entry['account_code'] ?? ''); ?>
              <?php $entryPaymentMethod = (string) ($entry['payment_method'] ?? ''); ?>
              <?php $entryPartnerTag = (string) ($entry['partner_tag'] ?? 'none'); ?>
              <tr class="<?= $isIncome ? 'entry-income' : 'entry-expense' ?>">
                <td><?= (int) $entry['id'] ?></td>
                <td><?= htmlspecialchars((string) $entry['entry_date']) ?></td>
                <td>
                  <span class="status-pill <?= $isValidated ? 'status-available' : 'status-unavailable' ?> accounting-pill">
                    <?= $isValidated ? 'Validée' : 'Brouillon' ?>
                  </span>
                </td>
                <td class="journal-code">BANQ</td>
                <td><?= htmlspecialchars((string) ($entry['reference'] ?? ('#' . (int) $entry['id']))) ?></td>
                <td>
                  <span class="status-pill <?= $isIncome ? 'status-available' : 'status-unavailable' ?> accounting-pill">
                    <?= $isIncome ? 'Produit' : 'Charge' ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($entryAccountCode . ' - ' . ($accountsByCode[$entryAccountCode] ?? 'Compte')) ?></td>
                <td><?= htmlspecialchars((string) (($paymentMethods[$entryPaymentMethod] ?? ucfirst($entryPaymentMethod)))) ?></td>
                <td><?= htmlspecialchars((string) (($partnerOptions[$entryPartnerTag] ?? ucfirst($entryPartnerTag)))) ?></td>
                <td><?= htmlspecialchars((string) $entry['label']) ?></td>
                <td class="amount-col amount-debit"><?= number_format((float) ($entry['debit'] ?? 0), 2, ',', ' ') ?> €</td>
                <td class="amount-col amount-credit"><?= number_format((float) ($entry['credit'] ?? 0), 2, ',', ' ') ?> €</td>
                <td class="amount-col amount-balance <?= $runningBalance >= 0 ? 'is-positive' : 'is-negative' ?>"><?= number_format($runningBalance, 2, ',', ' ') ?> €</td>
                <td><?= htmlspecialchars((string) ($entry['notes'] ?? '')) ?></td>
                <td>
                  <form method="post" action="/admin/comptabilite/<?= (int) $entry['id'] ?>/status" class="accounting-inline-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                    <input type="hidden" name="entry_status" value="<?= $isValidated ? 'draft' : 'validated' ?>">
                    <button type="submit" class="button-secondary"><?= $isValidated ? 'Rouvrir' : 'Valider' ?></button>
                  </form>
                  <form method="post" action="/admin/comptabilite/<?= (int) $entry['id'] ?>/delete" onsubmit="return confirm('Supprimer cette écriture ?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                    <button type="submit" class="link-danger" <?= $isValidated ? 'disabled' : '' ?>>Supprimer</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="10">Total période</th>
            <th class="amount-col amount-debit"><?= number_format($periodExpense, 2, ',', ' ') ?> €</th>
            <th class="amount-col amount-credit"><?= number_format($periodIncome, 2, ',', ' ') ?> €</th>
            <th class="amount-col amount-balance <?= $periodBalance >= 0 ? 'is-positive' : 'is-negative' ?>"><?= number_format($periodBalance, 2, ',', ' ') ?> €</th>
            <th colspan="2"></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </article>

  <div class="accounting-modal" id="entryModal" hidden aria-hidden="true">
    <button type="button" class="accounting-modal-backdrop" data-modal-close="entryModal" aria-label="Fermer"></button>
    <article class="card accounting-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="entryModalTitle">
      <div class="section-head">
        <h2 id="entryModalTitle">Ajouter une écriture</h2>
        <button type="button" class="button-secondary" data-modal-close="entryModal">Fermer</button>
      </div>

      <form method="post" action="/admin/comptabilite" class="form-grid accounting-entry-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

        <label>
          Type
          <select name="entry_type" required>
            <option value="income">Recette</option>
            <option value="expense">Dépense</option>
          </select>
        </label>

        <label>
          Compte comptable
          <select name="account_code" required>
            <?php foreach (($accounts ?? []) as $account): ?>
              <?php $accCode = (string) ($account['code'] ?? ''); ?>
              <option value="<?= htmlspecialchars($accCode) ?>">
                <?= htmlspecialchars($accCode . ' - ' . (string) ($account['label'] ?? '')) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Mode de paiement
          <select name="payment_method" id="entry_payment_method" required>
            <?php foreach (($paymentMethods ?? []) as $methodCode => $methodLabel): ?>
              <option value="<?= htmlspecialchars((string) $methodCode) ?>">
                <?= htmlspecialchars((string) $methodLabel) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Partenaire
          <select name="partner_tag" id="entry_partner_tag" required>
            <?php foreach (($partnerOptions ?? []) as $partnerCode => $partnerLabel): ?>
              <option value="<?= htmlspecialchars((string) $partnerCode) ?>">
                <?= htmlspecialchars((string) $partnerLabel) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Référence pièce
          <input type="text" name="reference" placeholder="VIR-2026-001 / FACT-52">
        </label>

        <label>
          Libellé
          <input type="text" name="label" id="entry_label" required>
        </label>

        <label>
          Montant (€)
          <input type="number" name="amount" id="entry_amount" min="0.01" step="0.01" required>
        </label>

        <label>
          Date
          <input type="date" name="entry_date" value="<?= date('Y-m-d') ?>" required>
        </label>

        <label>
          Notes
          <textarea name="notes" rows="4"></textarea>
        </label>

        <label>
          Statut
          <select name="entry_status" required>
            <option value="draft">Brouillon</option>
            <option value="validated">Valider immédiatement</option>
          </select>
        </label>

        <div class="actions-row">
          <button type="submit">Enregistrer l'écriture</button>
          <button type="button" class="button-secondary" data-modal-close="entryModal">Annuler</button>
        </div>
      </form>
    </article>
  </div>
</section>

<script>
  (function () {
    var body = document.body;

    function getModal(id) {
      return document.getElementById(id);
    }

    function openModal(id) {
      var modal = getModal(id);
      if (!modal) {
        return;
      }
      modal.hidden = false;
      modal.setAttribute('aria-hidden', 'false');
      body.classList.add('accounting-modal-open');
      var firstInput = modal.querySelector('input[name="label"]');
      if (firstInput) {
        firstInput.focus();
      }

      // Defaults utiles à l'ouverture
      var partnerSelect = modal.querySelector('#entry_partner_tag');
      if (partnerSelect && !partnerSelect.value) {
        partnerSelect.value = 'none';
      }
    }

    function autoCategorizeFromLabel() {
      var labelInput = document.getElementById('entry_label');
      var typeSelect = document.querySelector('select[name="entry_type"]');
      var accountSelect = document.querySelector('select[name="account_code"]');
      var partnerSelect = document.getElementById('entry_partner_tag');
      if (!labelInput || !typeSelect || !accountSelect || !partnerSelect) {
        return;
      }

      var text = (labelInput.value || '').toLowerCase();
      var rules = [
        { re: /loyer|location|motel/, type: 'income', account: '706', partner: 'none' },
        { re: /don|subvention/, type: 'income', account: '758', partner: 'none' },
        { re: /nexadev|honoraire|prestation site/, type: 'expense', account: '622', partner: 'none' },
        { re: /affiche|communication|pub/, type: 'expense', account: '623', partner: 'none' },
        { re: /classic/, type: 'expense', account: '606', partner: 'classic' },
        { re: /yellow/, type: 'expense', account: '606', partner: 'yellow' },
        { re: /rex/, type: 'expense', account: '606', partner: 'rex' },
        { re: /mojito/, type: 'expense', account: '606', partner: 'mojito' },
        { re: /seaton/, type: 'expense', account: '606', partner: 'seaton' }
      ];

      for (var i = 0; i < rules.length; i++) {
        if (rules[i].re.test(text)) {
          typeSelect.value = rules[i].type;
          accountSelect.value = rules[i].account;
          partnerSelect.value = rules[i].partner;
          return;
        }
      }
    }

    function closeModal(id) {
      var modal = getModal(id);
      if (!modal) {
        return;
      }
      modal.hidden = true;
      modal.setAttribute('aria-hidden', 'true');
      body.classList.remove('accounting-modal-open');
    }

    document.addEventListener('click', function (event) {
      var openTrigger = event.target instanceof Element ? event.target.closest('[data-modal-open]') : null;
      if (openTrigger) {
        event.preventDefault();
        openModal(openTrigger.getAttribute('data-modal-open'));
        return;
      }

      var closeTrigger = event.target instanceof Element ? event.target.closest('[data-modal-close]') : null;
      if (closeTrigger) {
        event.preventDefault();
        closeModal(closeTrigger.getAttribute('data-modal-close'));
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') {
        return;
      }
      var openedModal = document.querySelector('.accounting-modal:not([hidden])');
      if (!openedModal) {
        return;
      }
      closeModal(openedModal.id);
    });

    document.addEventListener('input', function (event) {
      if (!(event.target instanceof Element)) {
        return;
      }
      if (event.target.id === 'entry_label') {
        autoCategorizeFromLabel();
      }
    });
  })();
</script>
