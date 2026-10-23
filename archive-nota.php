<?php
/**
 * A linha do tempo: `/notas/`.
 *
 * O QUE MUDA EM RELAÇÃO AO `archive.php`. O arquivo do tema lista TÍTULOS: a
 * pessoa chega para escolher o que ler, e o `.post-item` foi desenhado para
 * isso — meta em cima, título embaixo, e nada mais. Uma nota não tem título: o
 * texto É o título. Aqui a lista continua sendo a do tema (`.post-list` e
 * `.post-item`, com o ritmo, as bordas e o respiro que vêm de graça, inclusive
 * a regra que cancela a borda dupla embaixo do `.term-header`), e o que muda é
 * o miolo: o conteúdo no lugar do `<h2>`.
 *
 * A PAGINAÇÃO É A DO SERVIDOR, e não a do `filtros.js`.
 *
 * A lista de `/posts/` pagina no navegador porque o filtro por tema precisa
 * ver a lista inteira para saber quantas sobraram. Aqui não há filtro, então
 * a paginação é a do WordPress — a mesma que o `archive.php` do tema usa, com
 * os endereços que o núcleo calcula (`/notas/page/2/`).
 *
 * MICROFORMATOS (IndieWeb).
 *
 * Cada nota é um `h-entry`, o texto é o `e-content`, a data vai num
 * `dt-published` com ISO no `<time>` e o link do horário carrega o `u-url`.
 *
 * NÃO HÁ `p-name`, e é a decisão mais importante deste arquivo: o nome de uma
 * nota é aquilo que ela diz. Um `p-name` com a data — que é o que sobraria,
 * já que não há título — faria os parsers da IndieWeb lerem "4 de outubro de
 * 2026" como o título da nota, e um leitor federado mostraria a data duas
 * vezes em vez do texto.
 *
 * @package ViniciusCodes
 */

get_header();

$vc_post_type = defined( 'VC_NOTA' ) ? VC_NOTA : 'nota';
$vc_objeto    = get_post_type_object( $vc_post_type );
$vc_feed      = get_post_type_archive_feed_link( $vc_post_type );
?>
<header class="term-header">
	<h1 class="term-header__name">
		<?php echo esc_html( $vc_objeto instanceof WP_Post_Type ? $vc_objeto->labels->name : __( 'microblog', 'viniciuscodes' ) ); ?>
	</h1>

	<p class="term-header__desc">
		o que eu escreveria numa rede social, mas escrito aqui: curto, sem título e no meu domínio.
		<?php if ( $vc_feed ) : ?>
			<a href="<?php echo esc_url( $vc_feed ); ?>">assine por RSS</a> ou me siga no Fediverso.
		<?php endif; ?>
	</p>
</header>

<?php if ( have_posts() ) : ?>
	<ul class="post-list">
		<?php
		while ( have_posts() ) :
			the_post();

			$vc_id     = (int) get_the_ID();
			$vc_numero = function_exists( 'vc_nota_numero' ) ? vc_nota_numero( $vc_id ) : 0;
			$vc_autor  = (string) get_the_author();
			$vc_tempo  = function_exists( 'vc_nota_tempo_relativo' ) ? vc_nota_tempo_relativo( $vc_id ) : get_the_date();
			$vc_slugs  = function_exists( 'vc_nota_slugs_de_tema' ) ? vc_nota_slugs_de_tema( $vc_id ) : '';
			$vc_avatar = function_exists( 'vc_midia' ) ? vc_midia( 'vinicius-profile.webp' ) : '';
			$vc_autor_url = get_author_posts_url( (int) get_the_author_meta( 'ID' ) );
			?>
			<li id="nota-<?php echo (int) $vc_id; ?>" <?php post_class( 'post-item post-item--nota h-entry' ); ?> data-temas="<?php echo esc_attr( $vc_slugs ); ?>">
				<header class="nota__cabecalho">
					<span class="p-author h-card" style="display:none;">
						<a class="u-url p-name" rel="author" href="<?php echo esc_url( $vc_autor_url ); ?>"><?php echo esc_html( $vc_autor ?: 'viniciuscodes' ); ?></a>
					</span>

					<a class="nota__data-link u-url" href="<?php the_permalink(); ?>">
						<time class="dt-published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $vc_id ) ); ?>" title="<?php echo esc_attr( get_the_date( 'j \d\e F \d\e Y, H:i', $vc_id ) ); ?>"><?php echo esc_html( $vc_tempo ); ?></time>
					</a>
				</header>

				<div class="e-content prose nota__conteudo">
					<?php the_content(); ?>
				</div>

				<footer class="nota__rodape">
					<div class="nota__acoes">
						<?php
						get_template_part(
							'template-parts/article',
							'like',
							array(
								'compacto' => true,
								'rotulo'   => $vc_numero > 0
									/* translators: %d: o número da nota. */
									? sprintf( __( 'gostar da nota #%d', 'viniciuscodes' ), $vc_numero )
									: __( 'gostar desta nota', 'viniciuscodes' ),
							)
						);
						?>
					</div>

					<?php if ( $vc_numero > 0 ) : ?>
						<a class="nota__numero-link" href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'link da nota #%d', 'viniciuscodes' ), $vc_numero ) ); ?>">#<?php echo (int) $vc_numero; ?></a>
					<?php endif; ?>
				</footer>
			</li>
			<?php
		endwhile;
		?>
	</ul>

	<?php
	$vc_total_paginas = (int) ( $GLOBALS['wp_query']->max_num_pages ?? 1 );
	$vc_pagina_atual  = max( 1, (int) get_query_var( 'paged' ) );
	?>
	<div class="feed-paginacao-wrap">
		<?php if ( $vc_total_paginas > 1 ) : ?>
			<div class="feed-paginacao">
				<?php vc_paginacao( $vc_total_paginas, $vc_pagina_atual ); ?>
			</div>
		<?php endif; ?>
	</div>
<?php else : ?>
	<p class="empty"><?php esc_html_e( 'ainda não há notas por aqui.', 'viniciuscodes' ); ?></p>
<?php endif; ?>

<?php
get_footer();
