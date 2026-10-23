<?php
/**
 * Template para a página /posts/ (todos os textos)
 *
 * @package ViniciusCodes
 */

get_header();
?>

<header class="article__header" style="margin-bottom: 2.5rem;">
	<p style="font-size: var(--text-xs); font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--color-meta); margin-bottom: 0.5rem;">arquivo de textos</p>
	<h1 class="article__title" style="font-size: var(--text-2xl); font-weight: 700; letter-spacing: -0.01em; margin: 0;">todos os textos</h1>
</header>

<?php
$paged = max( 1, (int) get_query_var( 'paged' ) );
$custom_query = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 10,
		'paged'          => $paged,
	)
);

global $wp_query;
$temp_query = $wp_query;
$wp_query   = $custom_query;

get_template_part(
	'template-parts/lista-de-textos',
	null,
	array(
		'mostrar_temas'  => true,
		'mostrar_resumo' => true,
	)
);

vc_paginacao( (int) $custom_query->max_num_pages, $paged );

$wp_query = $temp_query;
wp_reset_query();

get_footer();