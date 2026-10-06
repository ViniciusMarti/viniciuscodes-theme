<?php
/**
 * Template Name: Feed RSS
 *
 * A página do feed do site, e a explicação de como assinar.
 *
 * UM FEED SÓ. O site publicou dois endereços até 05/10/2026, quando o trabalho
 * do Fediverso fundiu tudo: `/feed.xml` passou a trazer os textos e as notas
 * juntos, na ordem da data. A separação em dois feeds era do WordPress, que dá
 * um feed por tipo de conteúdo; a fusão é do site, e é ela que faz a ponte do
 * Fediverso classificar certo cada publicação. Esta página anunciava os dois e
 * mandava assinar os dois — ficou mentindo por um dia.
 *
 * O `/notas/feed/` CONTINUA RESPONDENDO, e continua de propósito: um endereço de
 * feed é uma promessa, e quem assinou antes de a fusão existir não pode ser
 * avisado de que a promessa mudou de lugar. Ele não é anunciado aqui; só a nota
 * de rodapé diz que existe, para quem chegou pelo endereço antigo.
 *
 * POR QUE OS ENDEREÇOS SAEM DO CÓDIGO E NÃO DO EDITOR. Um endereço de feed é uma
 * promessa: quem assina guarda aquele caminho, e um feed que muda de lugar não
 * avisa ninguém — a pessoa só descobre quando percebe que parou de receber
 * texto. Escritos à mão no editor, eles envelheceriam calados se um tipo de post
 * fosse renomeado. Aqui saem da API do WordPress, que é quem sabe montar cada
 * um. O texto de abertura e o passo a passo continuam sendo do editor.
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
 * A ORDEM INVERTEU NESTA VERSÃO, E POR UM MOTIVO. Antes o texto vinha primeiro e
 * os endereços depois. Agora o endereço vem primeiro: a página existe para
 * entregar um endereço, e quem chegou aqui já sabe o que é um leitor de feeds. A
 * explicação continua logo abaixo, para quem não sabe.
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

$vc_textos_qtd = (int) wp_count_posts( 'post' )->publish;
$vc_notas_qtd  = (int) wp_count_posts( 'nota' )->publish;
$vc_total      = $vc_textos_qtd + $vc_notas_qtd;

$vc_feed_rss  = home_url( '/feed.xml' );
$vc_feed_atom = home_url( '/feed/atom/' );


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

		<h2 class="post-item__title">
			<a href="<?php echo esc_url( $vc_rss ); ?>"><?php echo esc_html( $vc_rotulo ); ?></a>
		</h2>

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
 * @param int    $vc_numero   Quantidade.
 */
$vc_contagem = static function ( string $vc_singular, string $vc_plural, int $vc_numero ): string {
	return sprintf( _n( $vc_singular, $vc_plural, $vc_numero ), number_format_i18n( $vc_numero ) );
};
?>
<article data-page="<?php echo esc_attr( (string) get_post_field( 'post_name' ) ); ?>">
	<header class="article__header">
		<h1 class="article__title"><?php the_title(); ?></h1>
	</header>

	<section class="feed-grupo">
		<ul class="post-list">
			<?php
			$vc_linha(
				'@viniciuscodes',
				sprintf(
					/* translators: 1: total de itens, 2: quantos textos, 3: quantas notas. */
					esc_html__( '%1$s, entre %2$s e %3$s', 'viniciuscodes' ),
					$vc_contagem( '%s item', '%s itens', $vc_total ),
					$vc_contagem( '%s texto', '%s textos', $vc_textos_qtd ),
					$vc_contagem( '%s nota', '%s notas', $vc_notas_qtd )
				),
				$vc_feed_rss,
				$vc_feed_atom
			);
			?>
		</ul>
	</section>

	<div class="prose"><?php the_content(); ?></div>

	<?php
	/*
	 * A NOTA DE RODAPÉ SOBRE O ENDEREÇO ANTIGO.
	 *
	 * Ela é gerada, e não escrita no editor, porque depende de o feed das notas
	 * ainda existir: no dia em que ele sair, esta linha sai sozinha em vez de
	 * mandar alguém para um endereço morto. Ela fica discreta e no fim de
	 * propósito — não é uma oferta, é a explicação para quem tinha assinado o
	 * outro.
	 */
	if ( $vc_notas_qtd > 0 ) :
		$vc_notas_rss = (string) get_post_type_archive_feed_link( 'nota' );
		?>
		<p class="feed-antigo">
			<?php
			printf(
				/* translators: %s: endereço do feed antigo, que trazia só as notas. */
				esc_html__( 'Se você assinou este site antes de outubro de 2026, pode ter guardado o endereço %s, que trazia só as notas. Ele continua funcionando e continua trazendo só as notas. O endereço de cima traz tudo, os textos e as notas.', 'viniciuscodes' ),
				'<code>' . esc_html( $vc_notas_rss ) . '</code>'
			);
			?>
		</p>
		<?php
	endif;
	?>
</article>

<?php
get_footer();
