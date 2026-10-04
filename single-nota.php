<?php
/**
 * Uma nota sozinha: `/notas/nota-20261004-1430/`.
 *
 * O `<h1>` É A DATA, e isso é o recurso, não uma limitação.
 *
 * O WordPress gravou um título interno ("Nota — 4 de outubro de 2026, 14:30")
 * para o RSS e para o banco. Mostrar esse título aqui seria deixar o
 * mecanismo aparecer na tela: quem abre a nota quer ler a nota, e a
 * identificação de uma nota é QUANDO ela foi escrita. A data que já era o
 * título interno vira o cabeçalho, e o texto começa logo abaixo.
 *
 * A ESTRUTURA É A DO TEMA. `.article`, `.article__header`, `.article__title`,
 * `.article__meta` e `.prose` são as classes do `single.php`, com as mesmas
 * regras de `global.css` — inclusive as de tema escuro. O que este arquivo não
 * reusa é o `template-parts/article-share.php`, porque o bloco de
 * compartilhamento do tema publica o TÍTULO do post no texto do
 * compartilhamento (`data-title`), e aqui o título é um carimbo de data.
 *
 * MICROFORMATOS. `h-entry` no artigo, `e-content` no texto, `dt-published` na
 * data e `u-url` no link permanente. Sem `p-name`, pelo mesmo motivo do
 * arquivo: o nome de uma nota é o que ela diz.
 *
 * @package ViniciusCodes
 */

get_header();

$vc_post_type = defined( 'VC_NOTA' ) ? VC_NOTA : 'nota';

while ( have_posts() ) :
	the_post();

	$vc_temas = get_the_terms( get_the_ID(), 'category' );

	if ( is_wp_error( $vc_temas ) || ! is_array( $vc_temas ) ) {
		$vc_temas = array();
	}

	$vc_anterior = get_previous_post();
	$vc_proxima  = get_next_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'article article--nota h-entry' ); ?>>
		<header class="article__header">
			<h1 class="article__title">
				<time class="dt-published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
					<?php echo esc_html( get_the_date( 'j \d\e F \d\e Y' ) ); ?>
				</time>
			</h1>

			<div class="article__meta">
				<?php
				$vc_numero = vc_nota_numero();
				$vc_autor  = (string) get_the_author();

				if ( $vc_numero > 0 ) :
					?>
					<span class="article__meta__part vc-nota-numero">#<?php echo (int) $vc_numero; ?></span>
					<span class="article__meta-sep" aria-hidden="true">·</span>
					<?php
				endif;

				/*
				 * O separador vem ANTES de cada item, e não depois do anterior:
				 * assim, um item que não sai (o número de uma nota antiga sem
				 * contador, o autor de uma nota sem autor) não deixa separador
				 * pendurado nem separador duplicado.
				 */
				?>
				<span class="article__meta__part"><?php echo esc_html( get_the_date( 'H:i' ) ); ?></span>

				<?php if ( '' !== $vc_autor ) : ?>
					<span class="article__meta-sep" aria-hidden="true">·</span>

					<span class="p-author h-card article__meta__part">
						<a class="u-url p-name" rel="author" href="<?php echo esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ); ?>"><?php echo esc_html( $vc_autor ); ?></a>
					</span>
				<?php endif; ?>
			</div>
		</header>

		<div class="e-content prose">
			<?php the_content(); ?>
		</div>

		<?php if ( $vc_temas ) : ?>
			<div class="article__end-themes" aria-label="<?php echo esc_attr__( 'temas desta nota', 'viniciuscodes' ); ?>">
				<?php foreach ( $vc_temas as $vc_tema ) : ?>
					<?php
					$vc_link = get_term_link( $vc_tema );

					if ( is_wp_error( $vc_link ) ) {
						continue;
					}
					?>
					<a class="article__end-theme-link" href="<?php echo esc_url( $vc_link ); ?>">
						<span class="meta-icon" aria-hidden="true">
							<?php echo vc_icone( vc_icone_do_tema( $vc_tema->slug ), 14 ); ?>
						</span>
						<?php
						/*
						 * O RÓTULO É O `name`, NÃO O `slug`.
						 *
						 * No site, o slug de um tema é uma coisa e o nome é
						 * outra: o slug é `criacao-conteudo-indieweb` e o nome é
						 * `indieweb`. O `global.css` e o `tema-icons.json`
						 * trabalham com o slug (é ele que resolve o ícone), mas
						 * o que aparece na tela — na lista e no artigo — é o
						 * nome. Usar o slug aqui escreveria
						 * "#criacao-conteudo-indieweb", que não é como o tema
						 * chama o tema em lugar nenhum.
						 */
						?>
						<span>#<?php echo esc_html( $vc_tema->name ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php
		/*
		 * O "GOSTAR" VEM ANTES DA NAVEGAÇÃO, e não depois.
		 *
		 * É a ordem dos textos, e ela é intencional lá também: primeiro o que se
		 * faz com o que se acabou de ler, depois para onde ir. Aqui a navegação é
		 * o "outro lugar" — as notas vizinhas e o arquivo —, então ela fecha.
		 *
		 * O BOTÃO É O DOS TEXTOS, com o rótulo e a pílula inteira: na lista ele
		 * aparece na forma compacta por causa da densidade, mas nesta página há
		 * espaço, e esta é a forma que a pessoa já conhece dos posts.
		 *
		 * O RÓTULO DIZ "NOTA". O padrão do parcial é "gostar deste texto", que é
		 * o que os posts usam; numa nota, "nota" é o nome certo da coisa, e aqui
		 * não há custo em dizer o nome certo — a página tem um botão só.
		 */
		?>
		<div class="nota__acoes">
			<?php
			get_template_part(
				'template-parts/article',
				'like',
				array( 'rotulo' => __( 'gostar desta nota', 'viniciuscodes' ) )
			);
			?>
		</div>

		<footer class="article__footer">
			<?php
			/*
			 * As notas vizinhas.
			 *
			 * `get_previous_post()` e `get_next_post()` já se limitam ao MESMO
			 * post type: uma nota nunca leva a um texto longo. O rótulo é o
			 * tempo relativo, e não o título, porque o título de uma nota é
			 * um carimbo de data — "← há 3 dias" diz o que a pessoa quer saber
			 * e "← Nota — 1 de outubro de 2026, 09:12" não diz nada.
			 */
			?>
			<nav class="notas-vizinhas" aria-label="<?php echo esc_attr__( 'outras notas do arquivo', 'viniciuscodes' ); ?>">
				<?php if ( $vc_anterior instanceof WP_Post ) : ?>
					<a class="notas-vizinhas__elo" rel="prev" href="<?php echo esc_url( get_permalink( $vc_anterior ) ); ?>">
						<span aria-hidden="true">←</span>
						<span><?php echo esc_html( vc_nota_tempo_relativo( $vc_anterior ) ); ?></span>
					</a>
				<?php endif; ?>

				<a class="notas-vizinhas__elo notas-vizinhas__arquivo" href="<?php echo esc_url( (string) get_post_type_archive_link( $vc_post_type ) ); ?>">
					<?php esc_html_e( 'todas as notas', 'viniciuscodes' ); ?>
				</a>

				<?php if ( $vc_proxima instanceof WP_Post ) : ?>
					<a class="notas-vizinhas__elo" rel="next" href="<?php echo esc_url( get_permalink( $vc_proxima ) ); ?>">
						<span><?php echo esc_html( vc_nota_tempo_relativo( $vc_proxima ) ); ?></span>
						<span aria-hidden="true">→</span>
					</a>
				<?php endif; ?>
			</nav>
		</footer>

		<?php
		/*
		 * SÓ DESENHA O BLOCO DE COMENTÁRIOS QUANDO JÁ EXISTE CONVERSA.
		 *
		 * Com os comentários fechados, o `comments_template()` do núcleo devia
		 * sair sozinho antes de carregar o `comments.php`. Medido neste
		 * WordPress (7.1.2): não sai. Numa nota recém-publicada — sem conversa
		 * nenhuma — a página trazia o `<section id="comentarios">` inteiro, com
		 * o título "conversa pública" e o aviso "os comentários estão
		 * encerrados". Uma caixa que anuncia uma conversa que não existe é pior
		 * que caixa nenhuma, e é justamente o que o pedido de "notas sem
		 * comentário" queria longe daqui.
		 *
		 * E POR QUE `have_comments()` E NÃO SIMPLESMENTE NÃO CHAMAR: as
		 * respostas que chegam pelo ActivityPub e pelo Webmention são gravadas
		 * como comentário. Enquanto elas não existirem, a nota não mostra nada;
		 * quando existirem, a conversa aparece — sem formulário para escrever
		 * uma nova, porque os comentários estão fechados para todo mundo, e é
		 * o `comments.php` do tema que decide isso.
		 */
		if ( have_comments() ) {
			comments_template();
		}
		?>
	</article>
	<?php
endwhile;

get_footer();
