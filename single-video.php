<?php
/**
 * O vídeo individual: `/video/<slug>/`.
 *
 * Exibe o player incorporado (bloco nativo wp-block-embed), notas/conteúdo do vídeo,
 * navegação cronológica anterior/próximo e link de retorno aos vídeos.
 *
 * @package ViniciusCodes
 */

get_header();

while ( have_posts() ) :
	the_post();
	$vc_yt_id    = vc_video_youtube_id();
	$vc_anterior = get_previous_post();
	$vc_proximo  = get_next_post();
	?>
	<article id="video-<?php the_ID(); ?>" <?php post_class( 'article article--video' ); ?>>
		<header class="article__header">
			<h1 class="article__title"><?php the_title(); ?></h1>

			<div class="article__meta">
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
					<?php echo esc_html( vc_data_longa( get_the_ID() ) ); ?>
				</time>
				<?php if ( '' !== (string) get_the_author() ) : ?>
					<span class="article__meta-sep" aria-hidden="true">·</span>
					<span class="article__meta__part"><?php echo esc_html( get_the_author() ); ?></span>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( $vc_yt_id ) : ?>
			<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9">
				<div class="wp-block-embed__wrapper">
					<iframe
						src="https://www.youtube-nocookie.com/embed/<?php echo esc_attr( $vc_yt_id ); ?>?rel=0"
						title="<?php the_title_attribute(); ?>"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
						allowfullscreen
						loading="eager"
					></iframe>
				</div>
			</figure>
		<?php endif; ?>

		<div class="prose">
			<?php
			$vc_conteudo = get_the_content();
			if ( ! empty( trim( $vc_conteudo ) ) ) {
				the_content();
			} else {
				$vc_excerpt = get_the_excerpt();
				if ( ! empty( $vc_excerpt ) ) {
					echo wp_kses_post( wpautop( $vc_excerpt ) );
				}
			}
			?>
		</div>

		<footer class="article__footer">
			<nav class="article-nav" aria-label="Navegação entre vídeos">
				<div class="article-nav__col">
					<?php if ( $vc_anterior instanceof WP_Post ) : ?>
						<p class="article-nav__label">&larr; vídeo anterior</p>
						<a class="article-nav__link" href="<?php echo esc_url( get_permalink( $vc_anterior->ID ) ); ?>">
							<?php echo esc_html( get_the_title( $vc_anterior->ID ) ); ?>
						</a>
					<?php endif; ?>
				</div>
				<div class="article-nav__col article-nav__col--next">
					<?php if ( $vc_proximo instanceof WP_Post ) : ?>
						<p class="article-nav__label">próximo vídeo &rarr;</p>
						<a class="article-nav__link" href="<?php echo esc_url( get_permalink( $vc_proximo->ID ) ); ?>">
							<?php echo esc_html( get_the_title( $vc_proximo->ID ) ); ?>
						</a>
					<?php endif; ?>
				</div>
			</nav>

			<p class="article__back">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'video' ) ); ?>">← voltar para os vídeos</a>
			</p>
		</footer>
	</article>
<?php
endwhile;

get_footer();
