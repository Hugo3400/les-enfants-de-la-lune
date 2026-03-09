<section class="contact-page">

  <div class="card contact-intro">
    <h1>Nous contacter</h1>
    <p class="lead">
      Besoin d'aide, envie de devenir bénévole ou proposition de partenariat ?
      Écris-nous, l'équipe te répond rapidement.
    </p>

    <div class="contact-categories">
      <div class="contact-category">
        <span class="contact-category-icon" aria-hidden="true"><i class="fa-solid fa-life-ring"></i></span>
        <strong>Demander de l'aide</strong>
        <p>Logement, mobilité, démarches…</p>
      </div>
      <div class="contact-category">
        <span class="contact-category-icon" aria-hidden="true"><i class="fa-solid fa-handshake"></i></span>
        <strong>Devenir bénévole</strong>
        <p>Donner de son temps au quartier</p>
      </div>
      <div class="contact-category">
        <span class="contact-category-icon" aria-hidden="true"><i class="fa-solid fa-building"></i></span>
        <strong>Partenariat</strong>
        <p>Entreprises et acteurs locaux</p>
      </div>
    </div>
  </div>

  <article class="card contact-form-card">
    <h2>Envoyer un message</h2>

    <form method="post" action="/contact" class="form-grid">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

      <label>
        Motif du contact
        <select name="category" required>
          <option value="aide">Demander de l'aide</option>
          <option value="benevole">Devenir bénévole</option>
          <option value="partenariat">Proposition de partenariat</option>
          <option value="general" selected>Question générale</option>
        </select>
      </label>

      <label>
        Nom complet
        <input type="text" name="name" required placeholder="Ton nom">
      </label>

      <label>
        Email
        <input type="email" name="email" required placeholder="ton@email.com">
      </label>

      <label>
        Sujet
        <input type="text" name="subject" required placeholder="De quoi as-tu besoin ?">
      </label>

      <label>
        Message
        <textarea name="message" rows="6" required placeholder="Décris ta situation ou ta demande…"></textarea>
      </label>

      <div class="actions-row">
        <button type="submit">Envoyer le message</button>
      </div>
    </form>
  </article>

</section>
