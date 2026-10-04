<?php
/**
 * Template Name: Feed RSS
 *
 * A página que reúne os feeds do site, separados entre notas e posts.
 *
 * POR QUE OS ENDEREÇOS SAEM DO CÓDIGO E NÃO DO EDITOR. Um endereço de feed é uma
 * promessa: quem assina guarda aquele caminho, e um feed que muda de lugar não
 * avisa ninguém — a pessoa só descobre quando percebe que parou de receber
 * texto. Escritos à mão no editor, `/notas/feed/` e os endereços por tema
 * envelheceriam calados se o tipo de post fosse renomeado ou um tema novo
 * entrasse no ar. Aqui eles saem da API do WordPress, que é quem sabe montar cada
 * um. O texto de abertura continua sendo do editor.
 *
 * POR QUE `/feed-rss/` E NÃO `/feed/`. `/feed/` é endereço reservado: o WordPress
 * responde por ele antes de qualquer página, então uma página com esse slug nunca
 * seria alcançada — o feed ganharia a disputa em silêncio.
 *
 * POR QUE OS POSTS NÃO USAM `get_feed_link()`. Ele devolve `/feed/`, que neste
 * site é um redirecionamento para `/feed.xml` — o endereço que o site publica
 * desde antes do WordPress, e o único que interessa publicar aqui. Publicar um
 * caminho que redireciona seria entregar dois endereços para o mesmo feed.
 *
 * @package ViniciusCodes
 */

/*
 * A folha e o script desta página, e só dela.
 *
 * AS LINHAS DA LISTA NÃO SÃO DAQUI: elas reaproveitam `.section-title`,
 * `.post-list` e `.post-item` do global, de propósito. Esta página lista feeds e
 * não textos, mas é a mesma lista do site — inventar uma segunda forma de listar
 * coisas daria duas gramáticas para a mesma leitura. O que é próprio daqui cabe
 * em dois arquivos pequenos: o endereço copiável e o botão que copia.
 */
wp_enqueue_style(
	'vc-feeds',
	get_theme_file_uri( 'assets/feeds.css' ),
	array( 'vc-global' ),
	(string) filemtime( get_theme_file_path( 'assets/feeds.css' ) )
);

// No rodapé: o script só liga o botão, e o botão já está no HTML quando ele roda.
wp_enqueue_script(
	'vc-feeds-js',
	get_theme_file_uri( 'assets/feeds.js' ),
	array(),
	(string) filemtime( get_theme_file_path( 'assets/feeds.js' ) ),
	true
);

get_header();

$vc_notas_qtd = (int) wp_count_posts( 'nota' )->publish;
$vc_posts_qtd = (int) wp_count_posts( 'post' )->publish;

$vc_notas_rss  = (string) get_post_type_archive_feed_link( 'nota' );
$vc_notas_atom = (string) get_post_type_archive_feed_link( 'nota', 'atom' );
$vc_posts_rss  = home_url( '/feed.xml' );
$vc_posts_atom = home_url( '/feed/atom/' );


/**
 * Uma linha da lista: o que entra no feed, os dois formatos e o endereço para copiar.
 *
 * O ENDEREÇO APARECE ESCRITO, além de linkado. Copiar e colar é o gesto de quem
 * assina um feed, e ler o endereço é a única forma de conferir que é aquele
 * mesmo antes de colar. O link do título leva ao feed; o endereço abaixo é para
 * quem prefere pegar o texto.
 *
 * @param string $vc_rotulo Nome do feed, como o leitor de feeds vai mostrá-lo.
 * @param string $vc_resumo O que entra neste feed, e em que quantidade.
 * @param string $vc_rss    Endereço RSS 2.0.
 * @param string $vc_atom   Endereço Atom.
 */
$vc_linha = static function ( string $vc_rotulo, string $vc_resumo, string $vc_rss, string $vc_atom ): void {
	?>
	<li class="post-item">
		<p class="post-item__meta">
			<span class="post-item__meta__part"><?php echo esc_html( $vc_resumo ); ?></span>
			<a class="post-item__meta__part" href="<?php echo esc_url( $vc_atom ); ?>">Atom</a>
		</p>

		<h3 class="post-item__title">
			<a href="<?php echo esc_url( $vc_rss ); ?>"><?php echo esc_html( $vc_rotulo ); ?></a>
		</h3>

		<p class="feed-endereco">
			<code><?php echo esc_html( $vc_rss ); ?></code>
			<button type="button" class="feed-copiar" data-endereco="<?php echo esc_attr( $vc_rss ); ?>" hidden>
				<span class="feed-copiar__texto">copiar</span>
			</button>
		</p>
	</li>
	<?php
};

/**
 * A quantidade de itens, escrita por extenso no singular e no plural.
 *
 * @param string $vc_singular Singular com `%s` no lugar do número.
 * @param string $vc_plural   Plural com `%s` no lugar do número.
 * @param int    $vc_total    Quantidade.
 */
$vc_contagem = static function ( string $vc_singular, string $vc_plural, int $vc_total ): string {
	return sprintf( _n( $vc_singular, $vc_plural, $vc_total ), number_format_i18n( $vc_total ) );
};
?>
<article data-page="<?php echo esc_attr( (string) get_post_field( 'post_name' ) ); ?>">
	<header class="article__header">
		<h1 class="article__title"><?php the_title(); ?></h1>
	</header>

	<div class="prose"><?php the_content(); ?></div>

	<section class="feed-grupo">
		<h2 class="section-title">as notas</h2>
		<p class="feed-grupo__nota">
			Os textos curtos, publicados sem título e numerados na ordem em que saem. Um item por
			nota, da mais nova para a mais antiga.
		</p>

		<ul class="post-list">
			<?php
			$vc_linha(
				'todas as notas',
				$vc_contagem( '%s nota', '%s notas', $vc_notas_qtd ),
				$vc_notas_rss,
				$vc_notas_atom
			);
			?>
		</ul>
	</section>

	<section class="feed-grupo">
		<h2 class="section-title">os posts</h2>
		<p class="feed-grupo__nota">
			Os textos longos, com título e data. É o endereço que o site publica em
			<code>/feed.xml</code> desde antes do WordPress — o mesmo em <code>/feed/</code>, que só
			redireciona para lá.
		</p>

		<ul class="post-list">
			<?php
			$vc_linha(
				'todos os posts',
				$vc_contagem( '%s post', '%s posts', $vc_posts_qtd ),
				$vc_posts_rss,
				$vc_posts_atom
			);
			?>
		</ul>
	</section>

	<?php
	/*
	 * AQUI HAVIA a terceira seção, "por tema": um endereço de feed para cada
	 * categoria com texto, com a contagem de publicações de cada uma. Ela saiu a
	 * pedido, em 04/10/2026, e o motivo foi a página inteira — doze linhas de
	 * endereço faziam uma página que existe para dizer "assine as notas e os
	 * posts" parecer um índice de endereços.
	 *
	 * Os feeds por tema continuam existindo e funcionando: o que saiu foi o
	 * anúncio deles aqui, não eles. A consulta que os montava (`get_terms`) saiu
	 * junto, porque sem a seção ela não tinha mais para quem trabalhar; o texto
	 * de abertura, que citava os temas, também foi corrigido no editor.
	 */
	?>
</article>

<?php
get_footer();
