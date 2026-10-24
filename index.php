<?php
/**
 * A página inicial: fluxo paginado de publicações do blog com imagens.
 *
 * @package ViniciusCodes
 */

get_header();

$vc_pagina = max( 1, (int) get_query_var( 'paged' ) );

if ( have_posts() ) :
	?>
	<div class="substack-feed substack-feed--imagens">
		<?php
		$indice = 0;
		while ( have_posts() ) :
			the_post();

			$vc_id    = (int) get_the_ID();
			$vc_temas = vc_temas_do_post( $vc_id );
			$vc_tempo = max( 1, round( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) );
			$vc_data  = mb_strtoupper( get_the_date( 'M j, Y', $vc_id ), 'UTF-8' );
			$vc_pri   = ( 0 === $indice && 1 === $vc_pagina );
			$vc_html  = vc_imagem_do_texto( $vc_id, $vc_pri ? 'large' : 'medium', 0 === $indice ? 'eager' : 'lazy' );
			$vc_img   = '' !== $vc_html;
			$vc_dest  = ( $vc_pri && $vc_img );
			?>
			<article id="post-<?php echo (int) $vc_id; ?>" class="substack-feed__item<?php echo $vc_img ? ' substack-feed__item--com-imagem' : ''; ?><?php echo $vc_dest ? ' substack-feed__item--destaque' : ''; ?>">
				<div class="substack-feed__texto">
					<div class="substack-feed__meta">
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $vc_id ) ); ?>"><?php echo esc_html( $vc_data ); ?></time>
						<?php if ( ! empty( $vc_temas ) ) : ?>
							<span>&bull;</span>
							<a href="<?php echo esc_url( '/tema/' . $vc_temas[0]['slug'] . '/' ); ?>" class="substack-feed__tag"><?php echo esc_html( mb_strtolower( $vc_temas[0]['nome'], 'UTF-8' ) ); ?></a>
						<?php endif; ?>
					</div>

					<h2 class="substack-feed__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

					<div class="substack-feed__footer"><span><?php echo (int) $vc_tempo; ?> min de leitura</span></div>
				</div>

				<?php if ( $vc_img ) : ?>
					<a class="substack-feed__imagem" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
						<?php echo $vc_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
			</article>
			<?php
			++$indice;
		endwhile;
		?>
	</div>
	<?php
	global $wp_query;
	vc_paginacao( (int) $wp_query->max_num_pages, $vc_pagina );
else :
	echo '<p class="empty">nada por aqui ainda.</p>';
endif;

get_footer();
