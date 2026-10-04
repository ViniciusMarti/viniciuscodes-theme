<?php
/**
 * O template padrão para páginas estáticas (com suporte a template dedicado por slug).
 *
 * @package ViniciusCodes
 */

$vc_slug = (string) get_post_field( 'post_name' );

$template_especifico = get_theme_file_path( "page-{$vc_slug}.php" );
if ( file_exists( $template_especifico ) ) {
	include $template_especifico;
	return;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article data-page="<?php echo esc_attr( $vc_slug ); ?>">
		<header class="article__header">
			<h1 class="article__title"><?php the_title(); ?></h1>
		</header>

		<div class="prose"><?php the_content(); ?></div>
	</article>
	<?php
endwhile;

get_footer();
