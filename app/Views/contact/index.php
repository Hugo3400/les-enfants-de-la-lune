<section class="contact-page">
  <article class="card contact-hero">
    <p class="contact-kicker"><i class="fa-solid fa-envelope-open-text"></i> Contact association</p>
    <h1>Parlons de votre besoin</h1>
    <p>
      Que ce soit pour une demande d'aide, une mission bénévole ou un partenariat,
      l'équipe vous répond rapidement avec un suivi humain.
    </p>
  </article>

  <div class="contact-grid">
    <aside class="card contact-info-card">
      <h2>Comment pouvons-nous vous aider ?</h2>
      <div class="contact-categories">
        <article class="contact-category">
          <span class="contact-category-icon" aria-hidden="true"><i class="fa-solid fa-life-ring"></i></span>
          <div>
            <strong>Demander de l'aide</strong>
            <p>Logement, mobilité, démarches administratives, urgence sociale.</p>
          </div>
        </article>

        <article class="contact-category">
          <span class="contact-category-icon" aria-hidden="true"><i class="fa-solid fa-handshake-angle"></i></span>
          <div>
            <strong>Devenir bénévole</strong>
            <p>Participez aux actions de terrain selon vos disponibilités.</p>
          </div>
        </article>

        <article class="contact-category">
          <span class="contact-category-icon" aria-hidden="true"><i class="fa-solid fa-building"></i></span>
          <div>
            <strong>Partenariat</strong>
            <p>Entreprises et acteurs locaux : construisons des actions utiles.</p>
          </div>
        </article>
      </div>

      <p class="contact-info-note">
        <i class="fa-regular fa-clock"></i>
        Délai de réponse habituel : 24h à 72h ouvrées.
      </p>
    </aside>

    <article class="card contact-form-card">
      <h2>Envoyer un message</h2>

      <form method="post" action="/contact" class="form-grid contact-form-grid">
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

        <div class="contact-form-row">
          <label>
            Nom complet
            <input type="text" name="name" required placeholder="Votre nom">
          </label>

          <label>
            Email
            <input type="email" name="email" required placeholder="vous@email.fr">
          </label>
        </div>

        <label>
          Sujet
          <input type="text" name="subject" required placeholder="Objet de votre demande">
        </label>

        <label>
          Message
          <textarea name="message" rows="7" required placeholder="Décrivez votre situation ou votre proposition..."></textarea>
        </label>

        <div class="actions-row">
          <button type="submit">Envoyer le message</button>
        </div>
      </form>
    </article>
  </div>

</section>
