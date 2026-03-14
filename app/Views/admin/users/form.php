<?php
  $isEdit = !empty($editUser);
  $action = $isEdit ? '/admin/utilisateurs/' . (int) $editUser['id'] . '/update' : '/admin/utilisateurs';
  $heading = $isEdit ? 'Modifier le compte' : 'Créer un compte';
  $currentUser = \App\Core\Auth::user();
  $isSelf = $isEdit && $currentUser && (int) $currentUser['id'] === (int) $editUser['id'];
?>

<section>
  <div class="section-head">
    <h1><?= htmlspecialchars($heading) ?></h1>
    <a class="button-secondary" href="/admin/utilisateurs">← Retour</a>
  </div>

  <article class="card" style="padding:24px;margin-top:12px;">
    <form method="post" action="<?= htmlspecialchars($action) ?>" class="form-grid" style="max-width:600px;">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

      <label>
        Nom complet *
        <input type="text" name="name" required value="<?= htmlspecialchars((string) ($editUser['name'] ?? '')) ?>">
      </label>

      <label>
        Adresse email *
        <input type="email" name="email" required value="<?= htmlspecialchars((string) ($editUser['email'] ?? '')) ?>">
      </label>

      <label>
        Mot de passe <?= $isEdit ? '(laisser vide pour ne pas changer)' : '*' ?>
        <input type="password" name="password" <?= $isEdit ? '' : 'required' ?> minlength="8" placeholder="<?= $isEdit ? 'Ne pas modifier' : 'Minimum 8 caractères' ?>">
      </label>

      <label>
        Rôle
        <select name="role" <?= $isSelf ? 'disabled' : '' ?>>
          <?php foreach (($roles ?? []) as $roleKey => $roleLabel): ?>
            <option value="<?= htmlspecialchars($roleKey) ?>" <?= ((string) ($editUser['role'] ?? 'member')) === $roleKey ? 'selected' : '' ?>>
              <?= htmlspecialchars($roleLabel) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if ($isSelf): ?>
          <input type="hidden" name="role" value="<?= htmlspecialchars((string) ($editUser['role'] ?? 'webmaster')) ?>">
          <small style="color:#8a8a8a;">Vous ne pouvez pas modifier votre propre rôle.</small>
        <?php endif; ?>
      </label>

      <label style="display:flex;flex-direction:row;align-items:center;gap:8px;">
        <input type="checkbox" name="is_active" value="1" <?= ((int) ($editUser['is_active'] ?? 1)) === 1 ? 'checked' : '' ?> <?= $isSelf ? 'disabled checked' : '' ?> style="width:auto;">
        Compte actif
        <?php if ($isSelf): ?>
          <input type="hidden" name="is_active" value="1">
        <?php endif; ?>
      </label>

      <div class="actions-row">
        <button type="submit"><?= $isEdit ? 'Enregistrer' : 'Créer le compte' ?></button>
        <a class="button-secondary" href="/admin/utilisateurs">Annuler</a>
      </div>
    </form>
  </article>
</section>
