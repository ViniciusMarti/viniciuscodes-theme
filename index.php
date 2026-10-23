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
