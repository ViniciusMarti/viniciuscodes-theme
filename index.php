<?php
/**
 * A página inicial: fluxo contínuo de publicações do blog.
 *
 * @package ViniciusCodes
 */

get_header();
?>

<section class="section" id="fluxo">
	<?php
	get_template_part(
		'template-parts/lista-de-textos',
		null,
		array(
			'vazio'          => 'nada por aqui ainda.',
			'mostrar_resumo' => true,
			'mostrar_temas'  => true,
		)
	);
	?>

	<div class="section__foot">
		<a href="<?php echo esc_url( home_url( '/posts/' ) ); ?>">
			<?php esc_html_e( 'ver todos os textos →', 'viniciuscodes' ); ?>
		</a>
	</div>
</section>

<?php
get_footer();
