<section class="auth-shell">
  <article class="card auth-panel">
    <div class="auth-intro">
      <p class="kicker">Espace interne</p>
      <h1>Connexion administration</h1>
      <p>Accès réservé à l'équipe de gestion de l'association.</p>
      <a class="mini-link" href="/">← Retour au site</a>
    </div>

    <form method="post" action="/admin/login" class="form-grid auth-form">
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
  </article>
</section>
