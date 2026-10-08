<?php
/**
 * Template Name: Blogroll
 *
 * A página `/blogroll/`: sites e blogs que eu acompanho pela internet.
 *
 * Cada linha exibe o favicon local do site, o nome do blog e o domínio correspondente.
 * O clique na linha abre o site em uma nova aba.
 *
 * @package ViniciusCodes
 */

get_header();

require_once get_theme_file_path( 'inc/blogroll-data.php' );
$vc_blogroll = vc_get_blogroll_items();

?>
<article data-page="blogroll">
	<header class="term-header">
		<h1 class="term-header__name"><?php the_title(); ?></h1>
		<p class="term-header__desc">blogs e páginas pessoais que eu acompanho pela internet.</p>
	</header>

	<ul class="blogroll-list">
		<?php foreach ( $vc_blogroll as $vc_item ) : ?>
			<?php
			$vc_fav_arquivo = $vc_item['favicon'] ?? ( $vc_item['dominio'] . '.png' );
			$vc_favicon_src = get_theme_file_uri( "assets/favicons/{$vc_fav_arquivo}" );
			?>
			<li class="blogroll-item">
				<a href="<?php echo esc_url( $vc_item['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="blogroll-link">
					<span class="blogroll-favicon-wrap">
						<img src="<?php echo esc_url( $vc_favicon_src ); ?>" alt="" class="blogroll-favicon" width="16" height="16" loading="lazy" decoding="async" onerror="this.style.display='none'">
					</span>
					<span class="blogroll-name"><?php echo esc_html( $vc_item['nome'] ); ?></span>
					<span class="blogroll-domain"><?php echo esc_html( $vc_item['dominio'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</article>

<?php
get_footer();
