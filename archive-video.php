<?php
/**
 * O arquivo de vídeos: `/videos/`.
 *
 * Utiliza o padrão nativo dos blocos de consulta do WordPress / Gutenberg
 * (`wp-block-query`, `wp-block-post-template is-layout-grid`).
 *
 * @package ViniciusCodes
 */

get_header();
?>
<header class="term-header">
	<h1 class="term-header__name">vídeos</h1>

	<p class="term-header__desc">
		ensaios em vídeo, análises de tecnologia, rotina e experimentos do meu canal no YouTube.
		<a href="https://www.youtube.com/@viniciuscodes?sub_confirmation=1" target="_blank" rel="noopener">inscreva-se no canal @viniciuscodes &rarr;</a>
	</p>
</header>

<?php if ( have_posts() ) : ?>
	<section class="wp-block-query vc-videos-query" aria-label="Lista de vídeos">
		<ul class="wp-block-post-template is-layout-grid columns-3">
			<?php
			while ( have_posts() ) :
				the_post();
				$vc_thumb = vc_video_thumbnail_url( get_the_ID() );
				?>
				<li id="video-<?php the_ID(); ?>" <?php post_class( 'wp-block-post' ); ?>>
					<figure class="wp-block-post-featured-image">
						<a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
							<?php if ( $vc_thumb ) : ?>
								<img
									src="<?php echo esc_url( $vc_thumb ); ?>"
									alt="<?php the_title_attribute(); ?>"
									loading="lazy"
									decoding="async"
									width="480"
									height="360"
								/>
							<?php else : ?>
								<div class="vc-video-placeholder">
									<span class="icon-play" aria-hidden="true"><?php echo vc_icone( 'play', 32 ); ?></span>
								</div>
							<?php endif; ?>
						</a>
					</figure>

					<div class="wp-block-post-date">
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
							<?php echo esc_html( get_the_date( 'j \d\e F \d\e Y' ) ); ?>
						</time>
					</div>

					<h2 class="wp-block-post-title">
						<a href="<?php the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
					</h2>
				</li>
			<?php endwhile; ?>
		</ul>

		<?php
		global $wp_query;
		if ( $wp_query->max_num_pages > 1 ) :
			?>
			<div class="vc-videos-pagination">
				<?php vc_paginacao( (int) $wp_query->max_num_pages, max( 1, (int) get_query_var( 'paged' ) ) ); ?>
			</div>
		<?php endif; ?>
	</section>
<?php else : ?>
	<p class="empty">nenhum vídeo publicado ainda.</p>
<?php endif; ?>

<?php
get_footer();
