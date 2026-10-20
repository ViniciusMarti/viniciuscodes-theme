<?php
/**
 * A página inicial: apresentação editorial e fluxo contínuo de publicações unificadas do blog.
 *
 * Uma única linha do tempo cronológica com microformatos IndieWeb para todos os textos.
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
?>

<section class="section" id="fluxo">
	<h2 class="section-title"><?php esc_html_e( 'recentes', 'viniciuscodes' ); ?></h2>

	<?php if ( have_posts() ) : ?>
		<ul class="post-list">
			<?php
			while ( have_posts() ) :
				the_post();

				$vc_id   = (int) get_the_ID();
				$vc_temas = function_exists( 'vc_temas_do_post' ) ? vc_temas_do_post( $vc_id ) : array();
				$vc_slugs = array();
				foreach ( $vc_temas as $vc_t ) {
					$vc_slugs[] = $vc_t['slug'];
				}

				$eh_status = has_post_format( 'status', $vc_id );
				?>
				<li id="post-<?php echo (int) $vc_id; ?>" <?php post_class( 'post-item ' . ( $eh_status ? 'post-item--nota' : 'post-item--editorial' ) . ' h-entry' ); ?> data-temas="<?php echo esc_attr( implode( ' ', $vc_slugs ) ); ?>">
					<?php if ( $eh_status ) : ?>
						<header class="nota__cabecalho">
							<span class="p-author h-card" style="display:none;">
								<a class="u-url p-name" rel="author" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_the_author() ?: 'viniciuscodes' ); ?></a>
							</span>

							<a class="nota__data-link u-url" href="<?php the_permalink(); ?>">
								<time class="dt-published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $vc_id ) ); ?>" title="<?php echo esc_attr( get_the_date( 'j \d\e F \d\e Y, H:i', $vc_id ) ); ?>">
									<?php echo esc_html( get_the_date( 'd/m/Y \à\s H:i', $vc_id ) ); ?>
								</time>
							</a>
						</header>

						<div class="e-content prose nota__conteudo">
							<?php the_content(); ?>
						</div>
					<?php else : ?>
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
					<?php endif; ?>
				</li>
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

		<div class="section__foot" style="display: flex; justify-content: flex-end; gap: 1rem; flex-wrap: wrap; margin-top: 1.5rem;">
			<a href="<?php echo esc_url( home_url( '/posts/' ) ); ?>"><?php esc_html_e( 'ver todos os textos →', 'viniciuscodes' ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
