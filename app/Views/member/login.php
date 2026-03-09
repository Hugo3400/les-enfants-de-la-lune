<section class="auth-shell">
  <article class="card auth-panel">
    <div class="auth-intro">
      <p class="kicker"><i class="fa-solid fa-moon"></i> Les Enfants de la Lune</p>
      <h1>Espace membre</h1>
      <p>Connectez-vous pour accéder à votre espace personnel.</p>
      <a class="mini-link" href="/">← Retour au site</a>
    </div>

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

    <div style="text-align:center;margin-top:16px;">
      <small style="color:#8a8a8a;">Vous n'avez pas de compte ? Contactez l'association.</small>
    </div>
  </article>
</section>
