(function () {
  const toggleButtons = document.querySelectorAll('[data-password-toggle]');

  toggleButtons.forEach(function (button) {
    const targetId = button.getAttribute('data-password-toggle');
    if (!targetId) {
      return;
    }

    const input = document.getElementById(targetId);
    if (!input) {
      return;
    }

    const icon = button.querySelector('i');

    button.addEventListener('click', function () {
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';

      button.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
      button.setAttribute('aria-label', isPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe');

      if (icon) {
        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
      }
    });
  });
})();
