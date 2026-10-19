<?php
/**
 * A página inicial: apresentação editorial e fluxo contínuo de publicações (textos e notas).
 *
 * Uma única linha do tempo cronológica com microformatos IndieWeb para notas
 * e destaques editoriais para artigos longos.
 *
 * @package ViniciusCodes
 */

get_header();

/*
 * Apresentação de abertura do autor (bio, retrato e frase de acolhimento).
 */
if ( is_home() ) {
	get_template_part( 'template-parts/apresentacao' );
}

$vc_post_type_nota = defined( 'VC_NOTA' ) ? VC_NOTA : 'nota';
?>

<section class="section" id="fluxo">
	<h2 class="section-title"><?php esc_html_e( 'recentes', 'viniciuscodes' ); ?></h2>

	<?php if ( have_posts() ) : ?>
		<ul class="post-list">
			<?php
			while ( have_posts() ) :
				the_post();

				$vc_id   = (int) get_the_ID();
				$vc_tipo = get_post_type( $vc_id );

				if ( $vc_post_type_nota === $vc_tipo ) :
					$vc_numero = function_exists( 'vc_nota_numero' ) ? vc_nota_numero( $vc_id ) : 0;
					$vc_autor  = (string) get_the_author();
					$vc_tempo  = function_exists( 'vc_nota_tempo_relativo' ) ? vc_nota_tempo_relativo( $vc_id ) : get_the_date();
					$vc_slugs  = function_exists( 'vc_nota_slugs_de_tema' ) ? vc_nota_slugs_de_tema( $vc_id ) : '';
					$vc_avatar = function_exists( 'vc_midia' ) ? vc_midia( 'vinicius-profile.webp' ) : '';
					$vc_autor_url = get_author_posts_url( (int) get_the_author_meta( 'ID' ) );
					?>
					<li id="nota-<?php echo (int) $vc_id; ?>" <?php post_class( 'post-item post-item--nota h-entry' ); ?> data-temas="<?php echo esc_attr( $vc_slugs ); ?>">
						<header class="nota__cabecalho">
							<span class="p-author h-card" style="display:none;">
								<a class="u-url p-name" rel="author" href="<?php echo esc_url( $vc_autor_url ); ?>"><?php echo esc_html( $vc_autor ?: 'viniciuscodes' ); ?></a>
							</span>

							<a class="nota__data-link u-url" href="<?php the_permalink(); ?>">
								<time class="dt-published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $vc_id ) ); ?>" title="<?php echo esc_attr( get_the_date( 'j \d\e F \d\e Y, H:i', $vc_id ) ); ?>"><?php echo esc_html( $vc_tempo ); ?></time>
							</a>
						</header>

						<div class="e-content prose nota__conteudo">
							<?php the_content(); ?>
						</div>

						<footer class="nota__rodape">
							<div class="nota__acoes">
								<?php
								get_template_part(
									'template-parts/article',
									'like',
									array(
										'compacto' => true,
										'rotulo'   => $vc_numero > 0
											/* translators: %d: o número da nota. */
											? sprintf( __( 'gostar da nota #%d', 'viniciuscodes' ), $vc_numero )
											: __( 'gostar desta nota', 'viniciuscodes' ),
									)
								);
								?>
							</div>

							<?php if ( $vc_numero > 0 ) : ?>
								<a class="nota__numero-link" href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'link da nota #%d', 'viniciuscodes' ), $vc_numero ) ); ?>">#<?php echo (int) $vc_numero; ?></a>
							<?php endif; ?>
						</footer>
					</li>
				<?php else : ?>
					<?php
					$vc_temas = function_exists( 'vc_temas_do_post' ) ? vc_temas_do_post( $vc_id ) : array();
					$vc_slugs = array();
					foreach ( $vc_temas as $vc_t ) {
						$vc_slugs[] = $vc_t['slug'];
					}
					?>
					<li id="post-<?php echo (int) $vc_id; ?>" <?php post_class( 'post-item post-item--editorial h-entry' ); ?> data-temas="<?php echo esc_attr( implode( ' ', $vc_slugs ) ); ?>">
						<p class="post-item__meta">
							<?php if ( ! empty( $vc_temas ) ) : ?>
								<?php $vc_tema_principal = reset( $vc_temas ); ?>
								<span class="post-item__meta__part">
									<a href="<?php echo esc_url( '/tema/' . $vc_tema_principal['slug'] . '/' ); ?>">
										<span class="meta-icon" aria-hidden="true"><?php echo vc_icone( $vc_tema_principal['icone'], 15 ); ?></span>
										<span><?php echo esc_html( mb_strtolower( (string) $vc_tema_principal['nome'], 'UTF-8' ) ); ?></span>
									</a>
								</span>
							<?php endif; ?>

							<time class="dt-published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $vc_id ) ); ?>"><?php echo esc_html( function_exists( 'vc_data_curta' ) ? vc_data_curta( $vc_id ) : get_the_date( 'd/m/Y', $vc_id ) ); ?></time>
						</p>

						<h2 class="post-item__title p-name">
							<a class="u-url" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>

						<?php
						$vc_resumo = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content() ), 35, '…' );
						if ( '' !== trim( $vc_resumo ) ) :
							?>
							<p class="post-item__excerpt e-summary">
								<?php echo esc_html( $vc_resumo ); ?>
							</p>
						<?php endif; ?>
					</li>
				<?php endif; ?>
				<?php
			endwhile;
			?>
		</ul>
	<?php else : ?>
		<p class="empty"><?php esc_html_e( 'nada por aqui ainda.', 'viniciuscodes' ); ?></p>
	<?php endif; ?>

	<div class="feed-paginacao-wrap">
		<?php
		global $wp_query;
		$vc_total_paginas = (int) ( $wp_query->max_num_pages ?? 1 );
		$vc_pagina_atual  = max( 1, (int) get_query_var( 'paged' ) );

		if ( $vc_total_paginas > 1 ) :
			?>
			<div class="feed-paginacao">
				<?php vc_paginacao( $vc_total_paginas, $vc_pagina_atual ); ?>
			</div>
		<?php endif; ?>

		<div class="section__foot" style="display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-top: 1.5rem;">
			<a href="<?php echo esc_url( home_url( '/posts/' ) ); ?>"><?php esc_html_e( 'ver todos os textos →', 'viniciuscodes' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/microblog/' ) ); ?>"><?php esc_html_e( 'ver todo o microblog →', 'viniciuscodes' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
