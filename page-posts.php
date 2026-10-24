<?php
/**
 * Template para a página /posts/ (todos os textos)
 *
 * @package ViniciusCodes
 */

get_header();
?>

<header class="article__header"><h1 class="article__title">todos os textos</h1></header>

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