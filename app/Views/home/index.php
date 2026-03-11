<section class="home-hero card">
	<div class="hero-grid">
		<div class="hero-content">
			<p class="kicker">Association solidaire · Blaine County</p>
			<h1 class="hero-main-title"><?= htmlspecialchars($heroTitle ?? "Ensemble, on change les choses") ?></h1>
			<div class="hero-underline" aria-hidden="true"></div>
			<p class="lead"><?= htmlspecialchars($heroText ?? "Les Enfants de la Lune accompagnent les habitants de Blaine County au quotidien : logement, mobilité, démarches et liens sociaux.") ?></p>

			<div class="hero-tags">
				<span class="tag">Entraide</span>
				<span class="tag">Logement</span>
				<span class="tag">Mobilité</span>
				<span class="tag">Démarches</span>
			</div>

			<div class="actions-row">
				<a class="button-link" href="/contact">Demander de l'aide</a>
				<a class="button-secondary" href="/actualites">Nos actualités</a>
			</div>
		</div>

		<aside class="hero-side showcase-panel">
			<h3>À venir</h3>
			<div class="showcase-stack">
				<?php $events = $upcomingEvents ?? []; ?>
				<?php if (empty($events)): ?>
					<article class="showcase-card">
						<strong>Rien de prévu pour l'instant</strong>
						<p>Les prochains événements seront affichés ici.</p>
					</article>
				<?php else: ?>
					<?php foreach ($events as $ev): ?>
						<article class="showcase-card">
							<strong><?= htmlspecialchars((string) ($ev['title'] ?? '')) ?></strong>
							<?php if (!empty($ev['description'])): ?>
								<p><?= htmlspecialchars((string) $ev['description']) ?></p>
							<?php elseif (!empty($ev['event_date']) || !empty($ev['event_time'])): ?>
								<p>
									<?php if (!empty($ev['event_date'])): ?>
										<?= htmlspecialchars((string) $ev['event_date']) ?>
									<?php endif; ?>
									<?php if (!empty($ev['event_time'])): ?>
										· <?= htmlspecialchars((string) $ev['event_time']) ?>
									<?php endif; ?>
								</p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<a class="mini-link" href="/contact">Proposer une action →</a>
		</aside>
	</div>
</section>

<section class="card live-strip">
	<div class="section-head">
		<h2>Nos actions concrètes</h2>
		<a href="/contact">Rejoindre l'aventure</a>
	</div>
	<div class="live-grid">
		<article class="live-item">
			<strong>Permanence entraide</strong>
			<p>Accueil chaleureux, écoute et orientation vers les bonnes ressources locales.</p>
		</article>
		<article class="live-item">
			<strong>Réseau mobilité</strong>
			<p>Solutions de transport et mise en relation avec les partenaires véhicules.</p>
		</article>
		<article class="live-item">
			<strong>Accompagnement installation</strong>
			<p>Soutien des nouveaux arrivants dans leurs premières démarches à Blaine County.</p>
		</article>
	</div>
</section>

<section class="card">
	<div class="section-head">
		<h2>Locations disponibles</h2>
		<a href="/locations">Voir toutes les locations</a>
	</div>

	<?php if (empty($availableRentals)): ?>
		<p>Aucune location disponible actuellement.</p>
	<?php else: ?>
		<div class="cards">
			<?php foreach (array_slice($availableRentals, 0, 3) as $rental): ?>
				<article class="card rental-card">
					<h3><?= htmlspecialchars((string) $rental['title']) ?></h3>
					<p><strong>Lieu :</strong> <?= htmlspecialchars((string) $rental['location_label']) ?></p>
					<p><strong>Prix :</strong> <?= number_format((float) $rental['price'], 2, ',', ' ') ?> €</p>
					<span class="status-pill status-available">Disponible</span>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>

<section class="cards home-values">
	<article class="card">
		<h2>Notre mission</h2>
		<p>
			Chaque habitant de Blaine County mérite un coup de main quand il en a besoin.
			Nous sommes là pour écouter, orienter et agir concrètement.
		</p>
	</article>
	<article class="card">
		<h2>Ce qu'on fait</h2>
		<ul>
			<li>Aide au logement et à l'installation</li>
			<li>Solutions de mobilité locale</li>
			<li>Accompagnement administratif</li>
			<li>Mise en réseau avec les acteurs locaux</li>
		</ul>
	</article>
	<article class="card">
		<h2>Pour qui ?</h2>
		<ul>
			<li>Nouveaux arrivants à Blaine County</li>
			<li>Habitants traversant une période difficile</li>
			<li>Habitants, commerces et entreprises solidaires</li>
		</ul>
	</article>
</section>

<section class="section-block">
	<div class="section-head">
		<h2>Comment nous aider ?</h2>
	</div>
	<div class="help-grid">
		<article class="card impact-card">
			<h3>Rejoindre l'aventure</h3>
			<p>Participez à nos actions selon vos disponibilités et contribuez à l'accompagnement du quartier.</p>
		</article>
		<article class="card impact-card">
			<h3>Faire un don</h3>
			<p>Chaque contribution compte. Équipements, fournitures ou aide logistique pour nos actions.</p>
		</article>
		<article class="card impact-card">
			<h3>Devenir partenaire</h3>
			<p>Commerces et entreprises locales, construisons ensemble des solutions durables pour le quartier.</p>
		</article>
	</div>
</section>

<section class="card home-quote">
	<p>
		« Personne ne devrait affronter seul les difficultés du quotidien.
		Ensemble, nous pouvons faire la différence. »
	</p>
</section>

<section class="card home-cta">
	<h2>Nous avons besoin de vous</h2>
	<ul class="soft-list">
		<li>Renforts pour les permanences d'accueil</li>
		<li>Partenaires mobilité et transport</li>
		<li>Parrainages pour les nouveaux arrivants</li>
	</ul>
	<p><a class="button-link" href="/contact">Rejoindre l'aventure</a></p>
</section>

<section>
	<div class="section-head">
		<h2>Dernières actualités</h2>
		<a href="/actualites">Tout voir</a>
	</div>
	<?php if (empty($posts)): ?>
		<p>Les prochaines actions seront publiées ici très bientôt.</p>
	<?php else: ?>
		<div class="cards">
			<?php foreach ($posts as $post): ?>
				<article class="card">
					<?php
						$createdAtRaw = (string) ($post['created_at'] ?? '');
						$createdAtTs = strtotime($createdAtRaw);
						$createdAtLabel = $createdAtTs ? date('d/m/Y', $createdAtTs) : $createdAtRaw;
					?>
					<h3>
						<a href="/actualites/<?= urlencode((string) $post['slug']) ?>">
							<?= htmlspecialchars((string) $post['title']) ?>
						</a>
					</h3>
					<?php if (!empty($post['excerpt'])): ?>
						<p><?= nl2br(htmlspecialchars((string) $post['excerpt'])) ?></p>
					<?php endif; ?>
					<small>Publié le <?= htmlspecialchars($createdAtLabel) ?></small>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
