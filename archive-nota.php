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
		<?php echo esc_html( $vc_objeto instanceof WP_Post_Type ? $vc_objeto->labels->name : __( 'Notas', 'viniciuscodes' ) ); ?>
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
			?>
			<li id="nota-<?php the_ID(); ?>" <?php post_class( 'post-item post-item--nota h-entry' ); ?> data-temas="<?php echo esc_attr( vc_nota_slugs_de_tema() ); ?>">
				<p class="post-item__meta">
					<?php
					$vc_numero = vc_nota_numero();
					$vc_autor  = (string) get_the_author();

					/*
					 * O NÚMERO VEM PRIMEIRO e é ele o link da nota: sem o rodapé
					 * de "link permanente", o `#N` passou a ser a âncora óbvia do
					 * item. Ele NÃO leva `u-url` de propósito — quem carrega o
					 * microformato é a data logo ao lado, e um segundo `u-url` no
					 * mesmo `h-entry` só criaria dúvida sobre qual é o canônico.
					 */
					if ( $vc_numero > 0 ) :
						?>
						<a class="vc-nota-numero" href="<?php the_permalink(); ?>">#<?php echo (int) $vc_numero; ?></a>
						<?php
					endif;

					/*
					 * O AUTOR É OPCIONAL, e a checagem não é paranoia: medido no
					 * site, uma nota criada por `wp post create` sem
					 * `--post_author` fica com `post_author = 0`. Sem o `if`, o
					 * `h-card` sai vazio e o separador do tema
					 * (`.post-item__meta > * + *::before`) fica pendurado depois
					 * da data. O separador é do CSS, então omitir o `<span>` já
					 * o remove junto — não há o que limpar aqui.
					 */
					?>
					<a class="u-url" href="<?php the_permalink(); ?>">
						<time class="dt-published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>" title="<?php echo esc_attr( get_the_date( 'j \d\e F \d\e Y, H:i' ) ); ?>"><?php echo esc_html( vc_nota_tempo_relativo() ); ?></time>
					</a>

					<?php if ( '' !== $vc_autor ) : ?>
						<span class="p-author h-card post-item__meta__part">
							<a class="u-url p-name" rel="author" href="<?php echo esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ); ?>"><?php echo esc_html( $vc_autor ); ?></a>
						</span>
					<?php endif; ?>
				</p>

				<div class="e-content prose nota__conteudo">
					<?php the_content(); ?>
				</div>

				<?php
				/*
				 * AQUI O "GOSTAR" É COMPACTO, e a diferença é de densidade, não de
				 * gosto. Esta lista mostra dez notas de uma vez, e dez pílulas de
				 * 44px embaixo de dez notas viram uma barra de controles — o
				 * oposto do que o próprio tema diz do bloco de compartilhamento,
				 * que se apresenta como um pedaço da informação do texto e não
				 * como um painel parafusado nele. O coração com a contagem diz a
				 * mesma coisa e some no visual.
				 *
				 * O `z-index` DESTE BOTÃO NÃO É ENFEITE. O item da linha do tempo
				 * tem uma área de clique que cobre ele inteiro (ver "a nota
				 * inteira é o alvo do clique", no `assets/notas.css`), e ela nasce
				 * em `z-index: 0`. Sem número maior, o clique seria engolido e o
				 * botão ficaria decorativo — foi o que já aconteceu com o link do
				 * autor antes. A regra de lá agora inclui este botão.
				 *
				 * O RÓTULO LEVA O NÚMERO DA NOTA porque aqui o botão não tem
				 * rótulo visível: sem o número, quem usa leitor de tela ouviria
				 * "gostar desta nota" três vezes na mesma página, sem saber qual
				 * é qual.
				 */
				?>
				<div class="nota__acoes">
					<?php
					get_template_part(
						'template-parts/article',
						'like',
						array(
							'compacto' => true,
							'rotulo'   => $vc_numero > 0
								/* translators: %d: o número da nota, como ele aparece na linha do tempo. */
								? sprintf( __( 'gostar da nota #%d', 'viniciuscodes' ), $vc_numero )
								: __( 'gostar desta nota', 'viniciuscodes' ),
						)
					);
					?>
				</div>
			</li>
			<?php
		endwhile;
		?>
	</ul>

	<?php
	/*
	 * A paginação oficial do arquivo, a mesma do `archive.php` do tema: o
	 * arquivo usa a consulta principal e o WordPress já a pagina.
	 */
	vc_paginacao(
		(int) $GLOBALS['wp_query']->max_num_pages,
		max( 1, (int) get_query_var( 'paged' ) )
	);
	?>
<?php else : ?>
	<p class="empty"><?php esc_html_e( 'ainda não há notas por aqui.', 'viniciuscodes' ); ?></p>
<?php endif; ?>

<?php
get_footer();
