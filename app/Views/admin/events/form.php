<?php
  $ev = $event ?? [];
  $isEdit = !empty($ev['id']);
  $action = $formAction ?? '/admin/evenements';
?>

<section class="admin-events-form-page">
  <div class="section-head">
    <h1><?= $isEdit ? 'Modifier l\'événement' : 'Nouvel événement' ?></h1>
    <a href="/admin/evenements" class="button-secondary">← Retour</a>
  </div>
  <p class="admin-lead">Cet événement apparaîtra dans le bloc « À venir » de la page d'accueil s'il est visible.</p>

  <div class="admin-events-form-layout">
    <article class="card events-form-card">
      <form method="post" action="<?= htmlspecialchars($action) ?>" class="form-grid events-form-grid">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

        <div class="events-form-block">
          <h2>Informations</h2>
          <label>
            Titre *
            <input type="text" name="title" value="<?= htmlspecialchars((string) ($ev['title'] ?? '')) ?>" placeholder="Ex : Permanence solidaire" required>
          </label>

          <label>
            Description courte
            <input type="text" name="description" value="<?= htmlspecialchars((string) ($ev['description'] ?? '')) ?>" placeholder="Ex : Lundi 18h · Accueil et orientation">
          </label>
        </div>

        <div class="events-form-block">
          <h2>Inscription</h2>
          <label>
            Lien d'inscription (optionnel)
            <input type="url" name="registration_url" value="<?= htmlspecialchars((string) ($ev['registration_url'] ?? '')) ?>" placeholder="https://...">
            <small class="events-help-text">Si renseigné, ce lien sera utilisé pour l'inscription. Sinon, une demande interne sera envoyée.</small>
          </label>
        </div>

        <div class="events-form-block">
          <h2>Planification</h2>
          <div class="events-two-cols">
            <label>
              Date
              <input type="date" name="event_date" value="<?= htmlspecialchars((string) ($ev['event_date'] ?? '')) ?>">
            </label>

            <label>
              Heure
              <input type="text" name="event_time" value="<?= htmlspecialchars((string) ($ev['event_time'] ?? '')) ?>" placeholder="Ex : 18h00">
            </label>
          </div>

          <label>
            Ordre d'affichage
            <input type="number" name="sort_order" value="<?= (int) ($ev['sort_order'] ?? 0) ?>" min="0">
            <small class="events-help-text">Les événements sont triés par ordre croissant (0 = premier).</small>
          </label>
        </div>

        <div class="events-form-block">
          <h2>Publication</h2>
          <label class="inline-check">
            <input type="checkbox" name="is_visible" value="1" <?= ((int) ($ev['is_visible'] ?? 1)) ? 'checked' : '' ?>>
            Visible sur la page d'accueil
          </label>
        </div>

        <div class="actions-row">
          <button type="submit"><?= $isEdit ? 'Mettre à jour' : 'Créer l\'événement' ?></button>
          <a class="button-secondary" href="/admin/evenements">Annuler</a>
        </div>
      </form>
    </article>

    <aside class="card events-help-card">
      <h2><i class="fa-solid fa-bullhorn"></i> Bonnes pratiques</h2>
      <ul>
        <li>Utilisez un titre court et actionnable.</li>
        <li>Ajoutez une heure explicite pour éviter les ambiguïtés.</li>
        <li>Laissez l'événement en invisible tant qu'il n'est pas validé.</li>
      </ul>
      <div class="events-help-badges">
        <span class="admin-badge">Communication</span>
        <span class="admin-badge">Planning</span>
      </div>
    </aside>
  </div>
</section>
