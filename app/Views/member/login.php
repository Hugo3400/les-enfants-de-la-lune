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
          <input type="password" name="password" placeholder="••••••••" required>
        </label>

        <button type="submit">Se connecter</button>
      </form>

      <p class="member-auth-note">Vous n'avez pas de compte ? Contactez l'association.</p>
    </div>
  </article>
</section>
