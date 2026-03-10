(function () {
  const form = document.getElementById('contactForm');
  if (!form) {
    return;
  }

  const categorySelect = document.getElementById('contactCategory');
  const messageField = document.getElementById('contactMessage');
  const messageCount = document.getElementById('contactMessageCount');
  const submitButton = document.getElementById('contactSubmitButton');
  const categoryCards = Array.from(document.querySelectorAll('[data-contact-category]'));

  const fields = {
    category: form.querySelector('[name="category"]'),
    name: form.querySelector('[name="name"]'),
    email: form.querySelector('[name="email"]'),
    subject: form.querySelector('[name="subject"]'),
    message: form.querySelector('[name="message"]')
  };

  const validators = {
    category(value) {
      return value ? '' : 'Sélectionnez un motif de contact.';
    },
    name(value) {
      const trimmed = value.trim();
      if (!trimmed) return 'Le nom est requis.';
      if (trimmed.length < 2) return 'Le nom doit contenir au moins 2 caractères.';
      return '';
    },
    email(value) {
      const trimmed = value.trim();
      if (!trimmed) return 'L’email est requis.';
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(trimmed)) return 'Entrez une adresse email valide.';
      return '';
    },
    subject(value) {
      const trimmed = value.trim();
      if (!trimmed) return 'Le sujet est requis.';
      if (trimmed.length < 3) return 'Le sujet doit contenir au moins 3 caractères.';
      return '';
    },
    message(value) {
      const trimmed = value.trim();
      if (!trimmed) return 'Le message est requis.';
      if (trimmed.length < 30) return 'Le message doit contenir au moins 30 caractères.';
      if (trimmed.length > 1500) return 'Le message ne doit pas dépasser 1500 caractères.';
      return '';
    }
  };

  function updateCategoryCards() {
    if (!categorySelect) {
      return;
    }

    categoryCards.forEach((card) => {
      const isActive = card.getAttribute('data-contact-category') === categorySelect.value;
      card.classList.toggle('is-active', isActive);
    });
  }

  function updateMessageCounter() {
    if (!messageField || !messageCount) {
      return;
    }

    messageCount.textContent = `${messageField.value.length} / 1500 caractères`;
  }

  function setFieldError(fieldName, errorMessage) {
    const input = fields[fieldName];
    const errorNode = form.querySelector(`[data-error-for="${fieldName}"]`);

    if (!input || !errorNode) {
      return true;
    }

    if (errorMessage) {
      errorNode.textContent = errorMessage;
      input.classList.add('is-invalid');
      return false;
    }

    errorNode.textContent = '';
    input.classList.remove('is-invalid');
    return true;
  }

  function validateField(fieldName) {
    const input = fields[fieldName];
    const validator = validators[fieldName];

    if (!input || !validator) {
      return true;
    }

    const errorMessage = validator(input.value);
    return setFieldError(fieldName, errorMessage);
  }

  function validateForm() {
    return Object.keys(fields).every((fieldName) => validateField(fieldName));
  }

  Object.keys(fields).forEach((fieldName) => {
    const input = fields[fieldName];
    if (!input) {
      return;
    }

    input.addEventListener('input', function () {
      validateField(fieldName);
      if (fieldName === 'message') {
        updateMessageCounter();
      }
      if (fieldName === 'category') {
        updateCategoryCards();
      }
    });

    input.addEventListener('blur', function () {
      validateField(fieldName);
    });
  });

  categoryCards.forEach((card) => {
    const categoryValue = card.getAttribute('data-contact-category');

    const selectCategory = function () {
      if (!categorySelect || !categoryValue) {
        return;
      }
      categorySelect.value = categoryValue;
      validateField('category');
      updateCategoryCards();
      const subjectField = fields.subject;
      if (subjectField && !subjectField.value.trim()) {
        const labels = {
          aide: 'Demande d’aide',
          benevole: 'Rejoindre l’aventure',
          partenariat: 'Proposition de partenariat'
        };
        subjectField.value = labels[categoryValue] || subjectField.value;
      }
    };

    card.addEventListener('click', selectCategory);
    card.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        selectCategory();
      }
    });
  });

  form.addEventListener('submit', function (event) {
    updateMessageCounter();

    if (!validateForm()) {
      event.preventDefault();
      const firstInvalid = form.querySelector('.is-invalid');
      if (firstInvalid) {
        firstInvalid.focus();
      }
      return;
    }

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Envoi en cours…';
    }
  });

  updateCategoryCards();
  updateMessageCounter();
})();
