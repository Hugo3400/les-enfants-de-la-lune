<?php
  $firstName = htmlspecialchars((string) ($member['first_name'] ?? ''));
  $lastName  = htmlspecialchars((string) ($member['last_name'] ?? ''));
  $email     = htmlspecialchars((string) ($member['email'] ?? ''));
  $phone     = htmlspecialchars((string) ($member['phone'] ?? ''));
  $roleName  = $roles[$member['role'] ?? 'membre'] ?? 'Membre';
  $statusMap = ['active' => 'Actif', 'inactive' => 'Inactif', 'suspended' => 'Suspendu'];
  $statusLabel = $statusMap[$member['status'] ?? 'active'] ?? $member['status'];
  $joinedAt  = $member['joined_at'] ?? null;
?>

<section class="member-page">
  <div class="section-head">
    <h1><i class="fa-solid fa-user"></i> Mon profil</h1>
  </div>

  <article class="card member-profil-card">
    <div class="member-profil-header">
      <div class="member-avatar"><?= mb_strtoupper(mb_substr($firstName, 0, 1)) . mb_strtoupper(mb_substr($lastName, 0, 1)) ?></div>
      <div>
        <h2><?= $firstName ?> <?= $lastName ?></h2>
        <p class="member-role-tag"><?= $roleName ?></p>
      </div>
    </div>

    <div class="member-profil-grid">
      <div class="member-profil-item">
        <span class="profil-label"><i class="fa-solid fa-envelope"></i> Email</span>
        <span class="profil-value"><?= $email ?: '<em>Non renseigné</em>' ?></span>
      </div>
      <div class="member-profil-item">
        <span class="profil-label"><i class="fa-solid fa-phone"></i> Téléphone</span>
        <span class="profil-value"><?= $phone ?: '<em>Non renseigné</em>' ?></span>
      </div>
      <div class="member-profil-item">
        <span class="profil-label"><i class="fa-solid fa-clipboard-list"></i> Statut</span>
        <span class="profil-value"><span class="member-tag <?= $member['status'] ?? 'active' ?>"><?= $statusLabel ?></span></span>
      </div>
      <div class="member-profil-item">
        <span class="profil-label"><i class="fa-regular fa-calendar"></i> Membre depuis</span>
        <span class="profil-value"><?= $joinedAt ? date('d/m/Y', strtotime($joinedAt)) : '<em>Non renseigné</em>' ?></span>
      </div>
      <?php if (!empty($user)): ?>
        <div class="member-profil-item">
          <span class="profil-label"><i class="fa-solid fa-lock"></i> Compte lié</span>
          <span class="profil-value"><?= htmlspecialchars((string) ($user['email'] ?? '')) ?></span>
        </div>
      <?php endif; ?>
    </div>

    <div class="member-profil-notice">
      <p><i class="fa-solid fa-lightbulb" style="color:#e67e22;"></i> Pour modifier vos informations, contactez l'administration de l'association.</p>
    </div>
  </article>
</section>
