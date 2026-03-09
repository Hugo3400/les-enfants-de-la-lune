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
  <?php $entriesCount = count($entries); ?>

  <?php $periodLabel = 'Toutes les semaines'; ?>
  <?php foreach (($weekOptions ?? []) as $weekOption): ?>
    <?php if (((string) ($weekOption['key'] ?? '')) === $activeWeek): ?>
      <?php $periodLabel = (string) ($weekOption['label'] ?? $periodLabel); ?>
    <?php endif; ?>
  <?php endforeach; ?>

  <div class="section-head">
    <h1>Comptabilité</h1>
    <div class="accounting-head-actions">
      <button type="button" class="button-link accounting-open-modal" data-modal-open="entryModal">+ Nouvelle écriture</button>
      <span class="admin-badge">Journal · Trésorerie</span>
    </div>
  </div>

  <article class="card accounting-toolbar">
    <h2>Filtrer le journal</h2>
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
              <td colspan="14" class="accounting-empty-row">Aucune écriture sur cette période.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($entries as $entry): ?>
              <?php $isIncome = ((string) $entry['entry_type']) === 'income'; ?>
              <?php $runningBalance = (float) ($entry['running_balance'] ?? 0); ?>
              <?php $isValidated = ((string) ($entry['entry_status'] ?? 'draft')) === 'validated'; ?>
              <?php $entryAccountCode = (string) ($entry['account_code'] ?? ''); ?>
              <?php $entryPaymentMethod = (string) ($entry['payment_method'] ?? ''); ?>
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
            <th colspan="9">Total période</th>
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
          <select name="payment_method" required>
            <?php foreach (($paymentMethods ?? []) as $methodCode => $methodLabel): ?>
              <option value="<?= htmlspecialchars((string) $methodCode) ?>">
                <?= htmlspecialchars((string) $methodLabel) ?>
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
          <input type="text" name="label" required>
        </label>

        <label>
          Montant (€)
          <input type="number" name="amount" min="0.01" step="0.01" required>
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
  })();
</script>
