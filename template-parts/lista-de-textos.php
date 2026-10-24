<?php
/**
 * A lista de textos estilo Substack / Feed Editorial.
 *
 * @package ViniciusCodes
 */

$vc_opcoes = wp_parse_args(
	$args ?? array(),
	array(
		'mostrar_meta'   => true,
		'mostrar_temas'  => true,
		'mostrar_resumo' => true,
		'vazio'          => 'nada por aqui ainda.',
	)
);

if ( ! have_posts() ) {
	printf( '<p class="empty">%s</p>', esc_html( $vc_opcoes['vazio'] ) );
	return;
}
?>
<div class="substack-feed">
	<?php
	while ( have_posts() ) :
		the_post();

		$vc_id    = (int) get_the_ID();
		$vc_temas = vc_temas_do_post( $vc_id );
		$vc_slugs = array();

		foreach ( $vc_temas as $vc_tema ) {
			$vc_slugs[] = $vc_tema['slug'];
		}

		$vc_words = str_word_count( wp_strip_all_tags( get_the_content() ) );
		$vc_tempo = max( 1, round( $vc_words / 200 ) );

		$vc_data_fmt = mb_strtoupper( get_the_date( 'M j, Y', $vc_id ), 'UTF-8' );
		$vc_resumo = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content() ), 34, '…' );
		?>
		<article id="post-<?php echo (int) $vc_id; ?>" class="substack-feed__item">
			<div class="substack-feed__meta">
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $vc_id ) ); ?>"><?php echo esc_html( $vc_data_fmt ); ?></time>
				
				<?php if ( ! empty( $vc_temas ) && $vc_opcoes['mostrar_temas'] ) : ?>
					<span>&bull;</span>
					<a href="<?php echo esc_url( '/tema/' . $vc_temas[0]['slug'] . '/' ); ?>" class="substack-feed__tag">
						<?php echo esc_html( mb_strtolower( $vc_temas[0]['nome'], 'UTF-8' ) ); ?>
					</a>
				<?php endif; ?>
			</div>

			<h2 class="substack-feed__title">
				<a href="<?php the_permalink(); ?>">
					<?php the_title(); ?>
				</a>
			</h2>

			<?php if ( ! empty( $vc_resumo ) ) : ?>
				<p class="substack-feed__excerpt">
					<?php echo esc_html( $vc_resumo ); ?>
				</p>
			<?php endif; ?>

			<div class="substack-feed__footer">
				<span><?php echo (int) $vc_tempo; ?> min de leitura</span>
			</div>
		</article>
		<?php
	endwhile;
	?>
</div>