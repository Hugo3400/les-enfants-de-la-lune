# Audit technique complet — Les Enfants de la Lune

Date: 2026-03-09  
Périmètre: PHP (Core/Controllers/Models), vues HTML/PHP, CSS, JS, sécurité applicative, structure projet.

## 1) Résumé exécutif

### État global
- Architecture MVC claire et cohérente.
- Requêtes SQL majoritairement sécurisées via PDO préparé.
- Protection CSRF présente et bien appliquée sur les actions POST sensibles.
- Échappement XSS globalement correct dans les vues.

### Risques prioritaires
- **Risque élevé**: redirections basées sur `HTTP_REFERER` (open redirect possible selon environnement/proxy).
- **Risque élevé**: durcissement session incomplet (pas de `session_regenerate_id` à la connexion, pas de flags cookie explicitement définis).
- **Risque moyen**: protection anti brute-force absente sur login admin/membre.
- **Risque moyen**: politique d’en-têtes sécurité incomplète (pas de CSP, HSTS, Permissions-Policy, etc.).
- **Risque moyen**: migrations automatiques en runtime + exceptions silencieuses sur certains `ALTER TABLE`.

### Dette technique Front
- 88 styles inline dans les vues (maintenabilité faible).
- 3 scripts inline, dossier JS statique vide.
- Pas d’outillage qualité (tests, linting structuré, CI) détecté.

---

## 2) Constat détaillé par axe

## 2.1 Sécurité backend (PHP)

### Points positifs
- CSRF token en place via [app/Core/Auth.php](app/Core/Auth.php#L141-L157) et vérifié dans la majorité des contrôleurs POST.
- Permissions centralisées via rôles dans [app/Models/UserModel.php](app/Models/UserModel.php).
- Accès admin protégé par `requirePermission` dans [app/Core/Auth.php](app/Core/Auth.php#L129-L139).
- Mots de passe hashés/validés avec `password_hash` / `password_verify` dans [app/Models/UserModel.php](app/Models/UserModel.php#L62-L95) et [app/Core/Auth.php](app/Core/Auth.php#L31-L33).

### Vulnérabilités / lacunes

1. **Redirection via `HTTP_REFERER` (élevé)**  
   - [app/Controllers/AdminPostController.php](app/Controllers/AdminPostController.php#L146)  
   - [app/Controllers/AdminRentalController.php](app/Controllers/AdminRentalController.php#L212)  
   Impact: possibilité de redirection externe non souhaitée (phishing/chaîne d’attaque) si referer manipulé.

2. **Session fixation / durcissement session incomplet (élevé)**  
   - Session démarrée brut dans [public/index.php](public/index.php#L4)  
   - Absence de `session_regenerate_id(true)` après login (aucune occurrence détectée).  
   - Pas de configuration explicite `httponly`, `secure`, `samesite` avant `session_start()`.

3. **Anti brute-force absent (moyen)**  
   - Login admin sans throttling: [app/Controllers/AuthController.php](app/Controllers/AuthController.php#L25-L52).  
   - Login membre même logique de tentative simple: [app/Controllers/MemberPortalController.php](app/Controllers/MemberPortalController.php).

4. **En-têtes sécurité partiels (moyen)**  
   - Présents: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` dans [public/index.php](public/index.php#L21-L23).  
   - Manquants: `Content-Security-Policy`, `Strict-Transport-Security`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`, `Cross-Origin-Resource-Policy`.

5. **Migrations en runtime + exceptions avalées (moyen)**  
   - Migration auto au boot dans [app/Core/Database.php](app/Core/Database.php).  
   - `tryExec` absorbe silencieusement les erreurs (bloc catch vide), rendant certains écarts de schéma difficiles à détecter.

---

## 2.2 SQL / Modèles / Données

### Points positifs
- Requêtes préparées majoritaires (ex: [app/Models/PostModel.php](app/Models/PostModel.php), [app/Models/EventModel.php](app/Models/EventModel.php), [app/Models/AccountingModel.php](app/Models/AccountingModel.php)).
- Filtres comptables construits avec paramètres bindés dans [app/Models/AccountingModel.php](app/Models/AccountingModel.php#L237-L306).

### Points d’attention
- Les contrôleurs font beaucoup de validation; une centralisation (DTO/validator) réduirait la duplication et les divergences futures.
- Certaines validations métier restent minimales (formats de date/heure, contraintes de longueur).

---

## 2.3 XSS / rendu HTML

### Points positifs
- Échappement `htmlspecialchars` largement présent dans les vues.
- Rendu enrichi d’articles implémente une whitelist de syntaxe (titres/listes/citations/liens `https?`) dans [app/Views/blog/show.php](app/Views/blog/show.php).

### Points d’attention
- Les layouts injectent `<?= $content ?>` (normal en moteur de vues) dans:  
  - [app/Views/layouts/main.php](app/Views/layouts/main.php#L69)  
  - [app/Views/layouts/admin.php](app/Views/layouts/admin.php#L60)  
  - [app/Views/layouts/member.php](app/Views/layouts/member.php#L50)  
  => nécessite discipline stricte d’échappement dans chaque vue (actuellement globalement respectée).

---

## 2.4 JavaScript

### Constat
- 3 scripts inline détectés (notamment [app/Views/admin/posts/form.php](app/Views/admin/posts/form.php), [app/Views/layouts/main.php](app/Views/layouts/main.php)).
- Dossier JS statique vide: [public/assets/js](public/assets/js).

### Impact
- Maintenabilité réduite (JS mêlé aux templates).
- Plus difficile de définir une CSP stricte sans `unsafe-inline`.

---

## 2.5 CSS / UI

### Constat
- 8 fichiers CSS (ok), mais 88 styles inline dans les vues.
- Cohérence design globalement bonne, mais dette de style dispersée dans les templates.

### Impact
- Régression visuelle plus probable.
- Refactoring plus coûteux (beaucoup de styles non mutualisés).

---

## 2.6 Architecture / Ops / Qualité

### Constat
- Pas de fichiers d’outillage détectés: `composer.json`, `phpunit.xml`, `phpstan.neon`, etc.
- Pas de README opérationnel détecté.
- Logs applicatifs écrits dans [storage/logs/app.log](storage/logs/app.log) via [app/Core/ErrorHandler.php](app/Core/ErrorHandler.php), sans rotation native.

### Impact
- Pas de garde-fous automatiques (tests/lint/static analysis).
- Onboarding et exploitation plus fragiles.

---

## 3) Plan de remédiation priorisé

## P0 — À faire immédiatement
1. **Supprimer les redirections basées sur `HTTP_REFERER`**  
   Remplacer par des routes internes fixes (ex: `/admin/articles/new`, `/admin/locations/{id}/edit`) ou whitelist stricte.
2. **Durcir la session**  
   - `session_set_cookie_params` avec `httponly=true`, `secure=true` (prod HTTPS), `samesite=Lax/Strict`.  
   - `session_regenerate_id(true)` après authentification réussie.
3. **Ajouter anti brute-force login**  
   - compteur par IP + email, fenêtre glissante, temporisation progressive.

## P1 — Sécurité/plateforme
4. **Ajouter en-têtes sécurité manquants**  
   CSP progressive, HSTS (si HTTPS strict), Permissions-Policy.
5. **Sortir les migrations du runtime**  
   Script de migration explicite + journal d’état de schéma.
6. **Renforcer validation métier**  
   Formats date/heure, longueurs max, normalisation stricte des entrées texte.

## P2 — Qualité et maintenabilité
7. **Externaliser JS inline** vers [public/assets/js](public/assets/js) (fichiers par module: admin/public/member).  
8. **Réduire styles inline** et migrer vers modules CSS existants.  
9. **Mettre en place outillage**: phpunit + phpstan + règles lint + pipeline CI minimale.

---

## 4) Quick wins (1 à 2 jours)

- Corriger les 2 usages `HTTP_REFERER` (2 fichiers ciblés).  
- Ajouter `session_regenerate_id(true)` après `Auth::attempt(...)` réussi.  
- Ajouter `session_set_cookie_params(...)` avant `session_start()`.  
- Créer un fichier JS pour l’éditeur d’article et déplacer le script inline.  
- Ajouter un mini README exploitation (lancement, env vars, sécurité de base).

---

## 5) Score de maturité (indicatif)

- Sécurité applicative: **6.5/10** (base saine, mais 2-3 lacunes importantes).  
- Qualité code backend: **7/10** (MVC clair, validations présentes, dette d’industrialisation).  
- Front (HTML/CSS/JS): **6/10** (UX active, mais forte dette inline).  
- Exploitabilité/DevEx: **4/10** (absence d’outillage/CI/documentation).

---

## 6) Conclusion

Le projet est proprement structuré et déjà protégé sur plusieurs fondamentaux (CSRF, permissions, SQL préparé, échappement global). Le niveau peut monter rapidement avec quelques corrections ciblées (redirections, session, brute-force, headers) puis un chantier de maintenabilité front et outillage.
