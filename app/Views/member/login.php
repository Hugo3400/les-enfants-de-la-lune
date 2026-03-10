<section class="auth-shell">
  <article class="card auth-panel">
    <div class="auth-intro">
      <p class="kicker"><i class="fa-solid fa-moon"></i> Les Enfants de la Lune</p>
      <h1>Espace membre</h1>
      <p>Connectez-vous pour accéder à votre espace personnel.</p>
      <a class="mini-link" href="/">← Retour au site</a>
    </div>

    <div class="member-auth-form-wrap">
      <form method="post" action="/espace-membre/connexion" class="form-grid auth-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

        <label>
          Email
          <input type="email" name="email" placeholder="votre@email.fr" required>
        </label>

        <label>
          Mot de passe
          <div class="password-field">
            <input type="password" id="memberPassword" name="password" placeholder="••••••••" required>
            <button
              type="button"
              class="password-toggle"
              data-password-toggle="memberPassword"
              aria-label="Afficher le mot de passe"
              aria-pressed="false"
            >
              <i class="fa-regular fa-eye" aria-hidden="true"></i>
            </button>
          </div>
        </label>

        <button type="submit">Se connecter</button>
      </form>

      <p class="member-auth-note">Vous n'avez pas de compte ? Contactez l'association.</p>
    </div>
  </article>
</section>

<script src="/public/assets/js/password-toggle.js?v=1773062800" defer></script>
