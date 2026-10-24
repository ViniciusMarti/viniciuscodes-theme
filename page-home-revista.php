<?php
/**
 * Template Name: Home Revista (teste)
 *
 * Home de exemplo em blocos, estilo revista, com a imagem de destaque.
 *
 * @package ViniciusCodes
 */

get_header();

// O destaque é o texto mais recente que tem imagem de destaque.
$destaque = get_posts(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 1,
		'meta_key'            => '_thumbnail_id',
		'ignore_sticky_posts' => true,
		'fields'              => 'ids',
	)
);

$consulta = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 10,
		'post__not_in'        => $destaque,
		'ignore_sticky_posts' => true,
	)
);

if ( $destaque ) {
	array_unshift( $consulta->posts, get_post( $destaque[0] ) );
	$consulta->post_count = count( $consulta->posts );
}

$indice = 0;
?>
<section class="home-revista" aria-label="Últimos textos">
	<?php
	while ( $consulta->have_posts() ) :
		$consulta->the_post();
		$classe  = 0 === $indice ? 'cartao cartao--destaque' : 'cartao';
		$tem_img = has_post_thumbnail();
		$tamanho = 0 === $indice ? 'large' : 'medium_large';
		$temas   = get_the_category();
		?>
		<article class="<?php echo esc_attr( $classe . ( $tem_img ? '' : ' cartao--sem-imagem' ) ); ?>">
			<a class="cartao__link" href="<?php the_permalink(); ?>">
				<?php if ( $tem_img ) : ?>
					<div class="cartao__midia">
						<?php the_post_thumbnail( $tamanho, array( 'class' => 'cartao__img', 'loading' => 0 === $indice ? 'eager' : 'lazy' ) ); ?>
					</div>
				<?php endif; ?>
				<div class="cartao__corpo">
					<?php if ( ! empty( $temas ) ) : ?>
						<span class="cartao__tema"><?php echo esc_html( $temas[0]->name ); ?></span>
					<?php endif; ?>
					<h2 class="cartao__titulo"><?php the_title(); ?></h2>
					<p class="cartao__resumo"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 0 === $indice ? 34 : 18 ) ); ?></p>
					<time class="cartao__data" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				</div>
			</a>
		</article>
		<?php
		++$indice;
	endwhile;
	wp_reset_postdata();
	?>
</section>
<?php
get_footer();
