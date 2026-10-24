<?php
/**
 * Template Name: Home Revista (teste)
 *
 * Home de exemplo: mesmo feed da home atual (coluna única, meta, título,
 * resumo e tempo de leitura), com a imagem de destaque de cada texto.
 *
 * @package ViniciusCodes
 */

get_header();

$consulta = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 10,
		'paged'               => max( 1, (int) get_query_var( 'paged' ) ),
		'ignore_sticky_posts' => true,
	)
);
?>
<div class="substack-feed substack-feed--imagens">
	<?php
	$indice = 0;
	while ( $consulta->have_posts() ) :
		$consulta->the_post();

		$vc_id    = (int) get_the_ID();
		$vc_temas = vc_temas_do_post( $vc_id );
		$vc_tempo = max( 1, round( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) );
		$vc_data  = mb_strtoupper( get_the_date( 'M j, Y', $vc_id ), 'UTF-8' );
		$vc_resumo = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content() ), 34, '…' );
		$vc_img   = has_post_thumbnail();
		$vc_dest  = ( 0 === $indice && 1 === max( 1, (int) get_query_var( 'paged' ) ) && $vc_img );
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

				<?php if ( ! empty( $vc_resumo ) ) : ?>
					<p class="substack-feed__excerpt"><?php echo esc_html( $vc_resumo ); ?></p>
				<?php endif; ?>

				<div class="substack-feed__footer"><span><?php echo (int) $vc_tempo; ?> min de leitura</span></div>
			</div>

			<?php if ( $vc_img ) : ?>
				<a class="substack-feed__imagem" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
					<?php the_post_thumbnail( $vc_dest ? 'large' : 'medium', array( 'loading' => 0 === $indice ? 'eager' : 'lazy', 'alt' => '' ) ); ?>
				</a>
			<?php endif; ?>
		</article>
		<?php
		++$indice;
	endwhile;
	?>
</div>
<?php
vc_paginacao( (int) $consulta->max_num_pages, max( 1, (int) get_query_var( 'paged' ) ) );
wp_reset_postdata();

get_footer();
