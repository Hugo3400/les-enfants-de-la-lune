(function () {
  var navToggle = document.getElementById('navToggle');
  var mainNav = document.getElementById('mainNav');

  if (!navToggle || !mainNav) {
    return;
  }

  navToggle.addEventListener('click', function () {
    navToggle.classList.toggle('active');
    mainNav.classList.toggle('nav-open');
  });
})();
