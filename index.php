<?php
/**
 * A página inicial: apresentação editorial e fluxo contínuo de textos & notas.
 *
 * Uma única coluna vertical, contínua, tipográfica, respirada e sem foco em datas cronológicas.
 * A exploração temática completa fica a cargo da barra lateral permanente.
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

$vc_temas_da_home = is_home() ? vc_temas_ordenados() : array();
?>

<?php if ( ! empty( $vc_temas_da_home ) ) : ?>
	<section class="section" id="temas">
		<h2 class="section-title">temas</h2>

		<ul class="grid">
			<?php foreach ( $vc_temas_da_home as $vc_tema ) : ?>
				<li>
					<a class="card" href="<?php echo esc_url( '/tema/' . $vc_tema->slug . '/' ); ?>">
						<span class="card__icon" aria-hidden="true"><?php echo vc_icone( vc_icone_do_tema( $vc_tema->slug ), 24 ); ?></span>
						<p class="card__title"><?php echo esc_html( mb_strtolower( (string) $vc_tema->name, 'UTF-8' ) ); ?></p>
						<p class="card__meta"><?php echo esc_html( $vc_tema->count . ( 1 === (int) $vc_tema->count ? ' post' : ' posts' ) ); ?></p>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<section class="section" id="textos">
	<h2 class="section-title">textos</h2>

	<?php if ( have_posts() ) : ?>
		<ul class="post-list post-list--editorial">
			<?php
			while ( have_posts() ) :
				the_post();

				$vc_id    = (int) get_the_ID();
				$vc_temas = vc_temas_do_post( $vc_id );
				?>
				<li class="post-item post-item--editorial">
					<?php if ( ! empty( $vc_temas ) ) : ?>
						<?php $vc_tema_principal = reset( $vc_temas ); ?>
						<span class="post-item__theme">
							<a href="<?php echo esc_url( '/tema/' . $vc_tema_principal['slug'] . '/' ); ?>">
								<span class="meta-icon" aria-hidden="true"><?php echo vc_icone( $vc_tema_principal['icone'], 15 ); ?></span>
								<span><?php echo esc_html( mb_strtolower( (string) $vc_tema_principal['nome'], 'UTF-8' ) ); ?></span>
							</a>
						</span>
					<?php endif; ?>

					<h2 class="post-item__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
				</li>
				<?php
			endwhile;
			?>
		</ul>
	<?php else : ?>
		<p class="empty">nada por aqui ainda.</p>
	<?php endif; ?>

	<p class="section__foot">
		<a href="<?php echo esc_url( home_url( '/posts/' ) ); ?>">ver todo o arquivo de publicações →</a>
	</p>
</section>

<?php
get_footer();
