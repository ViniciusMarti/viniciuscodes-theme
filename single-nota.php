<?php
/**
 * Uma nota sozinha: `/notas/nota-20261004-1430/`.
 *
 * A PÁGINA É O CARTÃO DA LISTA, EM TAMANHO DE PÁGINA.
 *
 * O `<h1>` ERA A DATA, e a data era o cabeçalho inteiro: quem abria a nota lia
 * um carimbo de data em corpo de título antes de ler a nota que ele
 * apresentava. A identificação de uma nota é QUEM escreveu e QUANDO, na mesma
 * linha — e é assim que o cartão do arquivo já fazia.
 *
 * O `<h1>` CONTINUA EXISTINDO, e agora carrega a linha de identidade do
 * cartão: avatar, nome e tempo relativo, com as mesmas classes. A página tem
 * de ter um título, e o título de uma nota é o que ela diz sobre si.
 *
 * A ESTRUTURA É A DO TEMA, e o cabeçalho é a exceção: `.article__header` e
 * `.prose` são as classes do `single.php`, mas `.article__title` aqui carrega o
 * `nota__cabecalho`, que é o desenho da linha de identidade do cartão. O
 * `template-parts/article-share.php` não é reusado porque ele publica o TÍTULO
 * do post no texto do compartilhamento (`data-title`), e o título de uma nota é
 * a própria linha de identidade.
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
			<?php
			/*
			 * O CABEÇALHO É O DO CARTÃO DA LISTA, com as mesmas classes e na mesma
			 * ordem: avatar, nome do autor e tempo relativo. Quem chega pela lista
			 * reconhece o que estava lendo; quem chega pelo permalink encontra a
			 * mesma coisa.
			 *
			 * A DATA ABSOLUTA NÃO SUMIU: ela está no `datetime` e no `title` do tempo,
			 * que é onde o cartão também a guarda. O que saiu foi o carimbo de data no
			 * lugar do título, em corpo de 1.3rem, antes da nota que ele apresenta.
			 */
			$vc_numero = vc_nota_numero();
			$vc_autor  = (string) get_the_author();
			$vc_login  = (string) get_the_author_meta( 'user_login' );
			$vc_avatar = vc_midia( 'vinicius-profile.webp' );
			?>
			<h1 class="article__title nota__cabecalho">
				<?php if ( '' !== $vc_avatar ) : ?>
					<img
						class="nota__avatar"
						src="<?php echo esc_url( $vc_avatar ); ?>"
						alt=""
						width="34"
						height="34"
						decoding="async"
					/>
				<?php endif; ?>

				<?php if ( '' !== $vc_autor ) : ?>
					<span class="p-author h-card nota__autor">
						<a class="u-url p-name" rel="author" href="<?php echo esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ); ?>"><?php echo esc_html( $vc_autor ); ?></a>
					</span>
				<?php endif; ?>

				<?php if ( '' !== $vc_login && $vc_login !== $vc_autor ) : ?>
					<span class="nota__handle">@<?php echo esc_html( $vc_login ); ?></span>
				<?php endif; ?>

				<span class="nota__sep" aria-hidden="true">·</span>

				<time class="dt-published nota__tempo" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>" title="<?php echo esc_attr( get_the_date( 'j \d\e F \d\e Y, H:i' ) ); ?>">
					<?php echo esc_html( vc_nota_tempo_relativo() ); ?>
				</time>
			</h1>
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

			/*
			 * O NÚMERO FECHA A LINHA, como no cartão da lista. Na lista ele é um
			 * link para a nota; aqui a nota já é esta página, então ele é um
			 * `<span>`: o mesmo identificador, sem um link que recarregaria o que
			 * a pessoa já está lendo.
			 */
			if ( $vc_numero > 0 ) :
				?>
				<span class="vc-nota-numero nota__numero">#<?php echo (int) $vc_numero; ?></span>
				<?php
			endif;
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
		 * O BLOCO DE COMENTÁRIOS, COM AS DUAS CONVERSAS.
		 *
		 * A CONDIÇÃO É A MESMA DO `single.php`: comentário aberto OU já existe
		 * algum. Ela substitui um `have_comments()` que existia para o oposto — na
		 * época as notas nasciam com o comentário fechado, e o bloco só devia
		 * aparecer quando uma resposta do fediverso chegasse. Numa nota sem
		 * conversa, a caixa anunciava uma conversa que não existia, com o aviso de
		 * comentários encerrados.
		 *
		 * AGORA A NOTA RECEBE COMENTÁRIO, e o formulário precisa aparecer mesmo sem
		 * nenhum ainda: sem ele ninguém escreve o primeiro. As respostas que chegam
		 * pelo ActivityPub e pelo Webmention continuam entrando no mesmo lugar — o
		 * plugin as grava como comentário, e o `comments.php` desenha as duas do
		 * mesmo jeito.
		 */
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;
		?>
	</article>
	<?php
endwhile;

get_footer();
