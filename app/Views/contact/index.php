<section class="contact-page">
  <article class="card contact-hero">
    <p class="contact-kicker"><i class="fa-solid fa-envelope-open-text"></i> Contact association</p>
    <h1>Parlons de votre besoin</h1>
    <p>
      Que ce soit pour une demande d'aide, rejoindre l'aventure ou un partenariat,
      l'équipe vous répond rapidement avec un suivi humain.
    </p>
  </article>

  <div class="contact-grid">
    <aside class="card contact-info-card">
      <h2>Comment pouvons-nous vous aider ?</h2>
      <div class="contact-categories">
        <article class="contact-category" data-contact-category="aide" tabindex="0" role="button" aria-label="Choisir demander de l'aide">
          <span class="contact-category-icon" aria-hidden="true"><i class="fa-solid fa-life-ring"></i></span>
          <div>
            <strong>Demander de l'aide</strong>
            <p>Logement, mobilité, démarches administratives, urgence sociale.</p>
          </div>
        </article>

        <article class="contact-category" data-contact-category="benevole" tabindex="0" role="button" aria-label="Choisir rejoindre l'aventure">
          <span class="contact-category-icon" aria-hidden="true"><i class="fa-solid fa-handshake-angle"></i></span>
          <div>
            <strong>Rejoindre l'aventure</strong>
            <p>Participez aux actions de terrain selon vos disponibilités.</p>
          </div>
        </article>

        <article class="contact-category" data-contact-category="partenariat" tabindex="0" role="button" aria-label="Choisir partenariat">
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

      <form method="post" action="/contact" class="form-grid contact-form-grid" id="contactForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

        <div class="contact-field-group">
          <label>
            Motif du contact
            <select name="category" id="contactCategory" required>
              <option value="aide">Demander de l'aide</option>
              <option value="benevole">Rejoindre l'aventure</option>
              <option value="partenariat">Proposition de partenariat</option>
              <option value="general" selected>Question générale</option>
            </select>
            <small class="contact-field-error" data-error-for="category" aria-live="polite"></small>
          </label>
        </div>

        <div class="contact-field-group">
          <div class="contact-form-row">
            <label>
              Nom complet
              <input type="text" name="name" required placeholder="Votre nom" minlength="2" maxlength="120">
              <small class="contact-field-error" data-error-for="name" aria-live="polite"></small>
            </label>

            <label>
              Email
              <input type="email" name="email" required placeholder="vous@email.fr" maxlength="190">
              <small class="contact-field-error" data-error-for="email" aria-live="polite"></small>
            </label>
          </div>
        </div>

        <div class="contact-field-group">
          <label>
            Sujet
            <input type="text" name="subject" required placeholder="Objet de votre demande" minlength="3" maxlength="160">
            <small class="contact-field-error" data-error-for="subject" aria-live="polite"></small>
          </label>
        </div>

        <div class="contact-field-group">
          <label>
            Message
            <textarea name="message" id="contactMessage" rows="7" required placeholder="Décrivez votre situation ou votre proposition..." minlength="30" maxlength="1500"></textarea>
            <small class="contact-field-help" id="contactMessageCount">0 / 1500 caractères</small>
            <small class="contact-field-error" data-error-for="message" aria-live="polite"></small>
          </label>
        </div>

        <div class="actions-row contact-actions-row">
          <button type="submit" id="contactSubmitButton">Envoyer le message</button>
        </div>
      </form>
    </article>
  </div>

</section>

<script src="/public/assets/js/contact-form.js?v=1773062000" defer></script>
