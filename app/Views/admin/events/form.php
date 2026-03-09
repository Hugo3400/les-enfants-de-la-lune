<?php
  $ev = $event ?? [];
  $isEdit = !empty($ev['id']);
  $action = $formAction ?? '/admin/evenements';
?>

<section class="admin-dashboard">
  <div class="section-head">
    <h1><?= $isEdit ? 'Modifier l\'événement' : 'Nouvel événement' ?></h1>
    <a href="/admin/evenements" class="button-secondary">← Retour</a>
  </div>
  <p class="admin-lead">Cet événement apparaîtra dans le bloc « À venir » de la page d'accueil s'il est visible.</p>

  <article class="card" style="max-width:640px;">
    <form method="post" action="<?= htmlspecialchars($action) ?>" class="form-grid">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

      <label>
        Titre *
        <input type="text" name="title" value="<?= htmlspecialchars((string) ($ev['title'] ?? '')) ?>"
               placeholder="Ex : Permanence solidaire" required>
      </label>

      <label>
        Description courte
        <input type="text" name="description" value="<?= htmlspecialchars((string) ($ev['description'] ?? '')) ?>"
               placeholder="Ex : Lundi 18h · Accueil et orientation">
      </label>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <label>
          Date
          <input type="date" name="event_date" value="<?= htmlspecialchars((string) ($ev['event_date'] ?? '')) ?>">
        </label>

        <label>
          Heure
          <input type="text" name="event_time" value="<?= htmlspecialchars((string) ($ev['event_time'] ?? '')) ?>"
                 placeholder="Ex : 18h00">
        </label>
      </div>

      <label>
        Ordre d'affichage
        <input type="number" name="sort_order" value="<?= (int) ($ev['sort_order'] ?? 0) ?>" min="0">
        <small style="color:#8a8a8a;">Les événements sont triés par ordre croissant (0 = premier).</small>
      </label>

      <label class="inline-check">
        <input type="checkbox" name="is_visible" value="1"
          <?= ((int) ($ev['is_visible'] ?? 1)) ? 'checked' : '' ?>>
        Visible sur la page d'accueil
      </label>

      <div class="actions-row">
        <button type="submit"><?= $isEdit ? 'Mettre à jour' : 'Créer l\'événement' ?></button>
        <a class="button-secondary" href="/admin/evenements">Annuler</a>
      </div>
    </form>
  </article>
</section>
