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

			<?php
			/*
			 * A DATA SAIU DAQUI, E O LUGAR DELA É O PÉ.
			 *
			 * Ela abria o texto logo abaixo do título, e num site que publica quando
			 * tem o que dizer isso transformava cada visita numa cobrança: quem
			 * chegasse depois de um mês de silêncio lia a data do silêncio antes de
			 * ler o texto. O leitor não precisa saber quando foi escrito para
			 * aproveitar o que está escrito.
			 *
			 * A DATA CONTINUA NA PÁGINA, no pé, e continua no `datetime` para
			 * buscador e leitor de tela. O que mudou é a ordem: primeiro o texto,
			 * depois a procedência.
			 *
			 * O "atualizado em" SAIU DE VEZ. Ele aparecia em 24 de 24 textos, e em
			 * quase todos era a data da importação para o WordPress, não uma revisão:
			 * uma segunda data que não informava nada e dobrava a superfície onde a
			 * passagem do tempo fica visível.
			 */
			?>
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
						<a class="article__end-theme-link" href="<?php echo esc_url( vc_url_do_tema( $vc_tema['slug'] ) ); ?>">
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

			if ( ! empty( $vc_citados ) || ! empty( $vc_backlinks ) ) :
				?>
				<section class="post-connections" aria-label="conexões deste texto">
					<?php if ( ! empty( $vc_citados ) ) : ?>
						<div class="post-connections__group">
							<h3 class="post-connections__title">
								<span class="meta-icon" aria-hidden="true"><?php echo vc_icone( 'arrow-up-right', 15 ); ?></span>
								<span>textos citados aqui</span>
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
								<span>textos que citam este</span>
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
			 * A ordem é a mesma do site, e ela é intencional:
			 *
			 *   compartilhar   → o leitor decide se aquilo serve para outra pessoa
			 *   comentários    → decide se quer falar comigo sobre o que leu
			 *   relacionadas   → decide o que ler depois
			 *
			 * O BLOCO "RESPONDER" SAIU, e o que ele oferecia continua aqui: era um convite
			 * a escrever por e-mail ou responder no Bluesky, e agora a conversa acontece na
			 * própria página. Manter os dois seria pedir a mesma coisa duas vezes, em dois
			 * lugares, com dois esforços diferentes.
			 */
			get_template_part( 'template-parts/article', 'share' );

			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

			get_template_part( 'template-parts/article', 'related' );
			?>
			<?php
			/*
			 * O colofão do texto: a procedência, no fim de tudo.
			 *
			 * A classe `article__dates` já estava na folha de estilo, com o desenho
			 * pronto para uma coluna de datas no pé do artigo, e nenhum template a
			 * imprimia. O desenho já previa este lugar.
			 */
			?>
			<p class="article__dates">
				<time class="article__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $vc_id ) ) ?>">
					publicado em <?php echo esc_html( vc_data_longa( $vc_id ) ); ?>
				</time>
			</p>

			<p class="article__back">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">← voltar para o início</a>
			</p>
		</footer>
	</article>
	<?php
endwhile;

get_footer();
