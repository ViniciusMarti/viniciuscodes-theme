<?php
/**
 * Um texto, sozinho. Reproduz o `PostLayout.astro`.
 *
 * O texto tem data, tempo de leitura e temas porque é um texto. Página não tem
 * nada disso — é o `page.php`, e era justamente o vazamento desses metadados
 * para as páginas que o site original levou tempo para arrumar.
 *
 * @package ViniciusCodes
 */

get_header();

while ( have_posts() ) :
	the_post();

	$vc_id    = (int) get_the_ID();
	$vc_temas = vc_temas_do_post( $vc_id );
	?>
	<article class="article">
		<header class="article__header">
			<h1 class="article__title"><?php the_title(); ?></h1>

			<div class="article__meta">
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $vc_id ) ); ?>"><?php echo esc_html( vc_data_longa( $vc_id ) ); ?></time>
				<?php if ( get_the_modified_time( 'U', $vc_id ) > get_the_time( 'U', $vc_id ) ) : ?>
					<span class="article__meta-sep" aria-hidden="true">·</span>
					<span class="article__meta-updated">
						atualizado em <time datetime="<?php echo esc_attr( get_the_modified_date( DATE_W3C, $vc_id ) ); ?>"><?php echo esc_html( vc_data_modificada_longa( $vc_id ) ); ?></time>
					</span>
				<?php endif; ?>
			</div>
		</header>

		<?php
		/*
		 * O fluxo do artigo começa diretamente no primeiro parágrafo,
		 * sem banners decorativos ou imagens de capa externas.
		 */
		?>
		<div class="prose">
			<?php the_content(); ?>

			<?php if ( $vc_temas ) : ?>
				<div class="article__end-themes" aria-label="temas deste texto">
					<?php foreach ( $vc_temas as $vc_tema ) : ?>
						<a class="article__end-theme-link" href="<?php echo esc_url( '/tema/' . $vc_tema['slug'] . '/' ); ?>">
							<span class="meta-icon" aria-hidden="true"><?php echo vc_icone( $vc_tema['icone'], 14 ); ?></span>
							<span>#<?php echo esc_html( mb_strtolower( (string) $vc_tema['nome'], 'UTF-8' ) ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php
		/*
		 * O cartão da resenha fica FORA do `.prose` porque não faz parte do
		 * texto: ele é o objeto sobre o qual o texto fala, e não deve herdar o
		 * ritmo dos parágrafos nem o enfeite de link do corpo.
		 */
		get_template_part( 'template-parts/card', 'livro' );
		get_template_part( 'template-parts/card', 'local' );
		?>

		<footer class="article__footer">

			<?php
			/* Conexões de ida e volta: notas citadas e notas que citam este texto */
			$vc_citados   = vc_get_outbound_internal_links( $vc_id );
			$vc_backlinks = vc_get_backlinks( $vc_id );

			$vc_tem_conexoes = ! empty( $vc_citados ) || ! empty( $vc_backlinks );

			if ( $vc_tem_conexoes ) :
				?>
				<section class="post-connections" aria-label="conexões deste texto">
					<?php if ( ! empty( $vc_citados ) ) : ?>
						<div class="post-connections__group">
							<h3 class="post-connections__title">
								<span class="meta-icon" aria-hidden="true"><?php echo vc_icone( 'arrow-up-right', 15 ); ?></span>
								<span>posts citados neste texto</span>
							</h3>
							<ul class="post-connections__list">
								<?php foreach ( $vc_citados as $vc_link ) : ?>
									<li><a href="<?php echo esc_url( $vc_link['url'] ); ?>"><?php echo esc_html( mb_strtolower( (string) $vc_link['title'], 'UTF-8' ) ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $vc_backlinks ) ) : ?>
						<div class="post-connections__group">
							<h3 class="post-connections__title">
								<span class="meta-icon" aria-hidden="true"><?php echo vc_icone( 'link', 15 ); ?></span>
								<span>posts que citam este texto</span>
							</h3>
							<ul class="post-connections__list">
								<?php foreach ( $vc_backlinks as $vc_bl ) : ?>
									<li><a href="<?php echo esc_url( $vc_bl['url'] ); ?>"><?php echo esc_html( mb_strtolower( (string) $vc_bl['title'], 'UTF-8' ) ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</section>
				<?php
			endif;

			/*
			 * Compartilhar e comentários.
			 */
			get_template_part( 'template-parts/article', 'share' );

			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

			/*
			 * Só exibe posts relacionados genéricos se não houver conexões internas
			 * específicas (evita empilhar listas duplicadas de sugestões).
			 */
			if ( ! $vc_tem_conexoes ) :
				get_template_part( 'template-parts/article', 'related' );
			endif;
			?>
			<p class="article__back">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">← voltar para o início</a>
			</p>
		</footer>
	</article>
	<?php
endwhile;

get_footer();
