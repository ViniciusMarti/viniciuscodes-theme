<?php
/**
 * Template Name: Música (Spotify 2026)
 *
 * Página `/musica/`: dados de 2026 extraídos diretamente do Spotify,
 * com prévia de áudio interativa (player nativo discreto) para cada faixa.
 *
 * @package ViniciusCodes
 */

wp_enqueue_style(
	'vc-musica',
	get_theme_file_uri( 'assets/musica.css' ),
	array( 'vc-global' ),
	(string) filemtime( get_theme_file_path( 'assets/musica.css' ) )
);

$vc_dados_path = get_theme_file_path( 'data/musica_spotify.json' );
$vc_musica_dados = array();
if ( file_exists( $vc_dados_path ) ) {
	$vc_conteudo_json = (string) file_get_contents( $vc_dados_path );
	$vc_musica_dados  = json_decode( $vc_conteudo_json, true ) ?: array();
}

$vc_info = $vc_musica_dados['dados'] ?? array();
$vc_albuns = $vc_info['albuns'] ?? array();
$vc_artistas = $vc_info['artistas'] ?? array();
$vc_musicas = $vc_info['musicas'] ?? array();
$vc_podcasts = $vc_info['podcasts'] ?? array();

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="article article--musica" data-page="musica">
		<header class="musica__header">
			<h1 class="article__title"><?php the_title(); ?> · 2026</h1>
			<p class="musica__intro">
				os discos, as vozes e as faixas que mais me acompanharam em 2026 no spotify, com prévia de áudio para ouvir cada uma.
			</p>
		</header>

		<?php if ( ! empty( $vc_info ) ) : ?>

			<!-- 1. Top Músicas (Faixas com player interativo) -->
			<?php if ( ! empty( $vc_musicas ) ) : ?>
				<section class="musica__secao">
					<div class="musica__secao-cabecalho">
						<h2 class="musica__secao-titulo">
							<?php esc_html_e( 'músicas mais tocadas', 'viniciuscodes' ); ?>
						</h2>
						<span class="musica__secao-tag"><?php esc_html_e( 'top 5 · clique para ouvir', 'viniciuscodes' ); ?></span>
					</div>

					<ul class="musica__lista-faixas">
						<?php foreach ( $vc_musicas as $vc_idx => $vc_musica ) : ?>
							<li class="musica__faixa-item" data-track-id="<?php echo esc_attr( $vc_musica['id'] ?? '' ); ?>">
								<span class="musica__faixa-pos"><?php echo (int) ( $vc_idx + 1 ); ?></span>
								
								<?php if ( ! empty( $vc_musica['imagem'] ) ) : ?>
									<div class="musica__faixa-capa">
										<img src="<?php echo esc_url( $vc_musica['imagem'] ); ?>" alt="" loading="lazy" width="44" height="44" />
									</div>
								<?php endif; ?>

								<div class="musica__faixa-info">
									<p class="musica__faixa-nome"><?php echo esc_html( $vc_musica['nome'] ); ?></p>
									<p class="musica__faixa-artista">
										<?php echo esc_html( $vc_musica['artista'] ); ?>
										<?php echo ! empty( $vc_musica['album'] ) ? ' — ' . esc_html( $vc_musica['album'] ) : ''; ?>
									</p>
								</div>

								<div class="musica__faixa-acoes">
									<?php if ( ! empty( $vc_musica['preview_url'] ) ) : ?>
										<button
											type="button"
											class="musica__player-btn"
											data-audio-src="<?php echo esc_url( $vc_musica['preview_url'] ); ?>"
											aria-label="<?php echo esc_attr( sprintf( __( 'ouvir prévia de %s', 'viniciuscodes' ), $vc_musica['nome'] ) ); ?>"
											title="<?php esc_attr_e( 'ouvir trecho de 30s', 'viniciuscodes' ); ?>"
										>
											<span class="musica__player-icon--play" aria-hidden="true"><?php echo vc_icone( 'play', 14 ); ?></span>
											<span class="musica__player-icon--pause" aria-hidden="true" style="display: none;"><?php echo vc_icone( 'pause', 14 ); ?></span>
											<span class="musica__player-label"><?php esc_html_e( 'ouvir', 'viniciuscodes' ); ?></span>
										</button>
									<?php endif; ?>

									<?php if ( ! empty( $vc_musica['spotify_url'] ) ) : ?>
										<a
											href="<?php echo esc_url( $vc_musica['spotify_url'] ); ?>"
											class="musica__faixa-link"
											target="_blank"
											rel="noopener noreferrer"
											title="<?php esc_attr_e( 'abrir no spotify', 'viniciuscodes' ); ?>"
										>
											<span aria-hidden="true">↗</span>
										</a>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<!-- 2. Top Álbuns -->
			<?php if ( ! empty( $vc_albuns ) ) : ?>
				<section class="musica__secao">
					<div class="musica__secao-cabecalho">
						<h2 class="musica__secao-titulo">
							<?php esc_html_e( 'álbuns mais ouvidos', 'viniciuscodes' ); ?>
						</h2>
						<span class="musica__secao-tag"><?php esc_html_e( 'top 5', 'viniciuscodes' ); ?></span>
					</div>

					<ul class="musica__grade-cards">
						<?php foreach ( $vc_albuns as $vc_idx => $vc_album ) : ?>
							<li class="musica__card">
								<a
									href="<?php echo esc_url( $vc_album['spotify_url'] ?? '#' ); ?>"
									class="musica__card-link"
									target="_blank"
									rel="noopener noreferrer"
									title="<?php echo esc_attr( $vc_album['nome'] . ' — ' . $vc_album['artista'] ); ?>"
								>
									<div class="musica__card-capa">
										<span class="musica__card-posicao"><?php echo (int) ( $vc_idx + 1 ); ?></span>
										<?php if ( ! empty( $vc_album['imagem'] ) ) : ?>
											<img
												src="<?php echo esc_url( $vc_album['imagem'] ); ?>"
												alt=""
												loading="lazy"
												width="300"
												height="300"
											/>
										<?php endif; ?>
									</div>
									<div class="musica__card-info">
										<h3 class="musica__card-titulo"><?php echo esc_html( $vc_album['nome'] ); ?></h3>
										<p class="musica__card-subtitulo"><?php echo esc_html( $vc_album['artista'] ); ?></p>
									</div>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<!-- 3. Top Artistas -->
			<?php if ( ! empty( $vc_artistas ) ) : ?>
				<section class="musica__secao">
					<div class="musica__secao-cabecalho">
						<h2 class="musica__secao-titulo">
							<?php esc_html_e( 'artistas mais ouvidos', 'viniciuscodes' ); ?>
						</h2>
						<span class="musica__secao-tag"><?php esc_html_e( 'top 5', 'viniciuscodes' ); ?></span>
					</div>

					<ul class="musica__grade-cards">
						<?php foreach ( $vc_artistas as $vc_idx => $vc_artista ) : ?>
							<li class="musica__card">
								<a
									href="<?php echo esc_url( $vc_artista['spotify_url'] ?? '#' ); ?>"
									class="musica__card-link"
									target="_blank"
									rel="noopener noreferrer"
									title="<?php echo esc_attr( $vc_artista['nome'] ); ?>"
								>
									<div class="musica__card-capa musica__card-capa--circular">
										<span class="musica__card-posicao"><?php echo (int) ( $vc_idx + 1 ); ?></span>
										<?php if ( ! empty( $vc_artista['imagem'] ) ) : ?>
											<img
												src="<?php echo esc_url( $vc_artista['imagem'] ); ?>"
												alt=""
												loading="lazy"
												width="300"
												height="300"
											/>
										<?php endif; ?>
									</div>
									<div class="musica__card-info">
										<h3 class="musica__card-titulo" style="text-align: center;"><?php echo esc_html( $vc_artista['nome'] ); ?></h3>
										<?php if ( ! empty( $vc_artista['generos'] ) ) : ?>
											<p class="musica__card-subtitulo" style="text-align: center;"><?php echo esc_html( implode( ' · ', $vc_artista['generos'] ) ); ?></p>
										<?php endif; ?>
									</div>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<!-- 4. Podcasts -->
			<?php if ( ! empty( $vc_podcasts ) ) : ?>
				<section class="musica__secao">
					<div class="musica__secao-cabecalho">
						<h2 class="musica__secao-titulo">
							<?php esc_html_e( 'podcasts acompanhados', 'viniciuscodes' ); ?>
						</h2>
						<span class="musica__secao-tag"><?php esc_html_e( 'menção honrosa', 'viniciuscodes' ); ?></span>
					</div>

					<ul class="musica__grade-podcasts">
						<?php foreach ( $vc_podcasts as $vc_pod ) : ?>
							<li class="musica__podcast-card">
								<div class="musica__podcast-topo">
									<?php if ( ! empty( $vc_pod['imagem'] ) ) : ?>
										<div class="musica__podcast-capa">
											<img src="<?php echo esc_url( $vc_pod['imagem'] ); ?>" alt="" loading="lazy" width="56" height="56" />
										</div>
									<?php endif; ?>
									<div>
										<h3 class="musica__podcast-nome"><?php echo esc_html( $vc_pod['nome'] ); ?></h3>
									</div>
								</div>
								<p class="musica__podcast-desc"><?php echo esc_html( $vc_pod['descricao'] ); ?></p>
								<?php if ( ! empty( $vc_pod['link'] ) ) : ?>
									<a href="<?php echo esc_url( $vc_pod['link'] ); ?>" class="musica__podcast-link" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'ouvir no spotify', 'viniciuscodes' ); ?> <span aria-hidden="true">↗</span>
									</a>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

		<?php else : ?>
			<p class="empty"><?php esc_html_e( 'nenhuma estatística musical cadastrada no momento.', 'viniciuscodes' ); ?></p>
		<?php endif; ?>

		<footer class="musica__rodape-nota">
			<span><?php esc_html_e( 'dados reais sincronizados via spotify web api · ano de 2026', 'viniciuscodes' ); ?></span>
			<span><?php echo esc_html( date( 'Y' ) ); ?></span>
		</footer>
	</article>

	<!-- Player Global Invisível de Áudio HTML5 -->
	<audio id="vc-audio-player" preload="none"></audio>

	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var player = document.getElementById('vc-audio-player');
		var currentBtn = null;

		function resetButton(btn) {
			if (!btn) return;
			var iconPlay = btn.querySelector('.musica__player-icon--play');
			var iconPause = btn.querySelector('.musica__player-icon--pause');
			var label = btn.querySelector('.musica__player-label');
			if (iconPlay) iconPlay.style.display = 'inline-flex';
			if (iconPause) iconPause.style.display = 'none';
			if (label) label.textContent = 'ouvir';
			btn.classList.remove('is-playing');
		}

		function setPlayingButton(btn) {
			if (!btn) return;
			var iconPlay = btn.querySelector('.musica__player-icon--play');
			var iconPause = btn.querySelector('.musica__player-icon--pause');
			var label = btn.querySelector('.musica__player-label');
			if (iconPlay) iconPlay.style.display = 'none';
			if (iconPause) iconPause.style.display = 'inline-flex';
			if (label) label.textContent = 'pausar';
			btn.classList.add('is-playing');
		}

		var buttons = document.querySelectorAll('.musica__player-btn');
		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var src = this.getAttribute('data-audio-src');
				if (!src) return;

				if (currentBtn === this && !player.paused) {
					player.pause();
					resetButton(this);
					return;
				}

				if (currentBtn && currentBtn !== this) {
					resetButton(currentBtn);
				}

				currentBtn = this;
				player.src = src;
				player.play().then(function () {
					setPlayingButton(btn);
				}).catch(function (err) {
					console.error('Erro ao reproduzir áudio:', err);
					resetButton(btn);
				});
			});
		});

		player.addEventListener('ended', function () {
			if (currentBtn) {
				resetButton(currentBtn);
				currentBtn = null;
			}
		});

		player.addEventListener('pause', function () {
			if (currentBtn && player.ended) {
				resetButton(currentBtn);
				currentBtn = null;
			}
		});
	});
	</script>
	<?php
endwhile;

get_footer();
