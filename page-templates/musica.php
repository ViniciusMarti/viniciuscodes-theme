<?php
/**
 * Template Name: Música (Spotify Top Anual)
 *
 * Página `/musica/`: álbuns, artistas, músicas e podcasts mais ouvidos por ano.
 * Consulta obtida via API oficial do Spotify e estruturada em JSON estático.
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

$vc_anos = $vc_musica_dados['anos'] ?? array();

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="article article--musica" data-page="musica">
		<header class="musica__header">
			<h1 class="article__title"><?php the_title(); ?></h1>
			<p class="musica__intro">
				os discos, as vozes, as músicas que mais tocaram nos meus fones e os podcasts que me acompanharam, organizados ano a ano através do spotify.
			</p>
		</header>

		<?php if ( ! empty( $vc_anos ) ) : ?>
			<!-- Navegação por ano (abas) -->
			<div class="musica__anos-nav" role="tablist" aria-label="<?php esc_attr_e( 'selecionar ano', 'viniciuscodes' ); ?>">
				<?php
				$vc_primeiro = true;
				foreach ( $vc_anos as $vc_ano_key => $vc_ano_data ) :
					$vc_ativo = $vc_primeiro ? ' is-active' : '';
					$vc_aria_sel = $vc_primeiro ? 'true' : 'false';
					$vc_primeiro = false;
					?>
					<button
						type="button"
						role="tab"
						class="musica__ano-btn<?php echo esc_attr( $vc_ativo ); ?>"
						id="tab-ano-<?php echo esc_attr( $vc_ano_key ); ?>"
						aria-controls="painel-ano-<?php echo esc_attr( $vc_ano_key ); ?>"
						aria-selected="<?php echo esc_attr( $vc_aria_sel ); ?>"
						data-ano="<?php echo esc_attr( $vc_ano_key ); ?>"
					>
						<?php echo esc_html( $vc_ano_data['rotulo'] ?? $vc_ano_key ); ?>
					</button>
				<?php endforeach; ?>
			</div>

			<!-- Painéis de cada ano -->
			<?php
			$vc_primeiro = true;
			foreach ( $vc_anos as $vc_ano_key => $vc_ano_data ) :
				$vc_ativo = $vc_primeiro ? ' is-active' : '';
				$vc_primeiro = false;
				?>
				<div
					id="painel-ano-<?php echo esc_attr( $vc_ano_key ); ?>"
					role="tabpanel"
					tabindex="0"
					aria-labelledby="tab-ano-<?php echo esc_attr( $vc_ano_key ); ?>"
					class="musica__ano-bloco<?php echo esc_attr( $vc_ativo ); ?>"
					data-ano-bloco="<?php echo esc_attr( $vc_ano_key ); ?>"
				>
					<?php if ( ! empty( $vc_ano_data['descricao'] ) ) : ?>
						<div class="musica__ano-meta">
							<p class="musica__ano-meta-titulo"><?php echo esc_html( $vc_ano_data['rotulo'] ?? $vc_ano_key ); ?></p>
							<p class="musica__ano-meta-desc"><?php echo esc_html( $vc_ano_data['descricao'] ); ?></p>
						</div>
					<?php endif; ?>

					<!-- 1. Top Álbuns -->
					<?php if ( ! empty( $vc_ano_data['albuns'] ) ) : ?>
						<section class="musica__secao">
							<div class="musica__secao-cabecalho">
								<h2 class="musica__secao-titulo">
									<?php esc_html_e( 'álbuns mais ouvidos', 'viniciuscodes' ); ?>
								</h2>
								<span class="musica__secao-tag"><?php esc_html_e( 'top 5', 'viniciuscodes' ); ?></span>
							</div>

							<ul class="musica__grade-cards">
								<?php foreach ( $vc_ano_data['albuns'] as $vc_idx => $vc_album ) : ?>
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

					<!-- 2. Top Artistas -->
					<?php if ( ! empty( $vc_ano_data['artistas'] ) ) : ?>
						<section class="musica__secao">
							<div class="musica__secao-cabecalho">
								<h2 class="musica__secao-titulo">
									<?php esc_html_e( 'artistas mais ouvidos', 'viniciuscodes' ); ?>
								</h2>
								<span class="musica__secao-tag"><?php esc_html_e( 'top 5', 'viniciuscodes' ); ?></span>
							</div>

							<ul class="musica__grade-cards">
								<?php foreach ( $vc_ano_data['artistas'] as $vc_idx => $vc_artista ) : ?>
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

					<!-- 3. Top Músicas (Faixas) -->
					<?php if ( ! empty( $vc_ano_data['musicas'] ) ) : ?>
						<section class="musica__secao">
							<div class="musica__secao-cabecalho">
								<h2 class="musica__secao-titulo">
									<?php esc_html_e( 'músicas mais tocadas', 'viniciuscodes' ); ?>
								</h2>
								<span class="musica__secao-tag"><?php esc_html_e( 'top 5', 'viniciuscodes' ); ?></span>
							</div>

							<ul class="musica__lista-faixas">
								<?php foreach ( $vc_ano_data['musicas'] as $vc_idx => $vc_musica ) : ?>
									<li class="musica__faixa-item">
										<span class="musica__faixa-pos"><?php echo (int) ( $vc_idx + 1 ); ?></span>
										<?php if ( ! empty( $vc_musica['imagem'] ) ) : ?>
											<div class="musica__faixa-capa">
												<img src="<?php echo esc_url( $vc_musica['imagem'] ); ?>" alt="" loading="lazy" width="44" height="44" />
											</div>
										<?php endif; ?>
										<div class="musica__faixa-info">
											<p class="musica__faixa-nome"><?php echo esc_html( $vc_musica['nome'] ); ?></p>
											<p class="musica__faixa-artista"><?php echo esc_html( $vc_musica['artista'] ); ?> <?php echo ! empty( $vc_musica['album'] ) ? '— ' . esc_html( $vc_musica['album'] ) : ''; ?></p>
										</div>
										<?php if ( ! empty( $vc_musica['spotify_url'] ) ) : ?>
											<a
												href="<?php echo esc_url( $vc_musica['spotify_url'] ); ?>"
												class="musica__faixa-link"
												target="_blank"
												rel="noopener noreferrer"
												title="<?php esc_attr_e( 'ouvir no spotify', 'viniciuscodes' ); ?>"
											>
												<span aria-hidden="true"><?php echo vc_icone( 'play', 15 ); ?></span>
											</a>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endif; ?>

					<!-- 4. Podcasts -->
					<?php if ( ! empty( $vc_ano_data['podcasts'] ) ) : ?>
						<section class="musica__secao">
							<div class="musica__secao-cabecalho">
								<h2 class="musica__secao-titulo">
									<?php esc_html_e( 'podcasts acompanhados', 'viniciuscodes' ); ?>
								</h2>
								<span class="musica__secao-tag"><?php esc_html_e( 'menção honrosa', 'viniciuscodes' ); ?></span>
							</div>

							<ul class="musica__grade-podcasts">
								<?php foreach ( $vc_ano_data['podcasts'] as $vc_pod ) : ?>
									<li class="musica__podcast-card">
										<div>
											<h3 class="musica__podcast-nome"><?php echo esc_html( $vc_pod['nome'] ); ?></h3>
											<p class="musica__podcast-desc"><?php echo esc_html( $vc_pod['descricao'] ); ?></p>
										</div>
										<?php if ( ! empty( $vc_pod['link'] ) ) : ?>
											<a href="<?php echo esc_url( $vc_pod['link'] ); ?>" class="musica__podcast-link" target="_blank" rel="noopener noreferrer">
												<?php esc_html_e( 'conhecer no spotify', 'viniciuscodes' ); ?> <span aria-hidden="true">↗</span>
											</a>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endif; ?>

				</div>
			<?php endforeach; ?>

		<?php else : ?>
			<p class="empty"><?php esc_html_e( 'nenhuma estatística musical cadastrada no momento.', 'viniciuscodes' ); ?></p>
		<?php endif; ?>

		<footer class="musica__rodape-nota">
			<span><?php esc_html_e( 'dados sincronizados via spotify web api · consulta pontual estática', 'viniciuscodes' ); ?></span>
			<span><?php echo esc_html( date( 'Y' ) ); ?></span>
		</footer>
	</article>

	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var buttons = document.querySelectorAll('.musica__ano-btn');
		var blocks = document.querySelectorAll('.musica__ano-bloco');

		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var ano = this.getAttribute('data-ano');

				buttons.forEach(function (b) {
					b.classList.remove('is-active');
					b.setAttribute('aria-selected', 'false');
				});
				blocks.forEach(function (block) {
					block.classList.remove('is-active');
				});

				this.classList.add('is-active');
				this.setAttribute('aria-selected', 'true');

				var target = document.getElementById('painel-ano-' + ano);
				if (target) {
					target.classList.add('is-active');
				}
			});
		});
	});
	</script>
	<?php
endwhile;

get_footer();
