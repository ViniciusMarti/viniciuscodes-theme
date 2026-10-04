<?php
/**
 * O feed das notas e os pedaços que os templates das notas usam.
 *
 * POR QUE O `/notas/feed/` PRECISA DE REGRA PRÓPRIA.
 *
 * O endereço `/qualquer/feed/` cai numa regra genérica do núcleo
 * (`(.+?)/feed/`), e essa regra não sabe o que é um post type: o `notas` do
 * caminho vira lixo na consulta e o feed sai com os posts longos — exatamente
 * o contrário do que se quer de um feed de notas. Duas regras escritas antes
 * das do núcleo dizem o que o endereço significa.
 *
 * O FEED PRINCIPAL CONTINUA LIMPO, e isso não é efeito deste arquivo: o
 * `/feed/` do site consulta `post_type=post`, e um post type separado nunca
 * entra ali. Os textos longos e as notas não se misturam em nenhum dos dois
 * sentidos, sem precisar de um `pre_get_posts` para excluir nada.
 *
 * @package ViniciusCodes
 */

defined( 'ABSPATH' ) || exit;

// O `nota` é definido em `cpt-notas.php`: um arquivo que depende de outro
// pede o outro, em vez de confiar na ordem em que o `functions.php` chamou.
require_once __DIR__ . '/cpt-notas.php';

/* -------------------------------------------------------------------------
 * 1. /notas/feed/
 * ---------------------------------------------------------------------- */

/**
 * As regras de rewrite do feed.
 *
 * A última regra cobre `/notas/feed/` sem formato, para quem digita o
 * endereço na mão ou copia de um leitor que normaliza a URL.
 */
function vc_notas_rewrite_do_feed() {
	add_rewrite_rule(
		'^notas/feed/(atom|rss|rss2|rdf)/?$',
		'index.php?post_type=' . VC_NOTA . '&feed=$matches[1]',
		'top'
	);

	add_rewrite_rule(
		'^notas/feed/?$',
		'index.php?post_type=' . VC_NOTA . '&feed=rss2',
		'top'
	);
}
add_action( 'init', 'vc_notas_rewrite_do_feed', 10 );

/**
 * Garante que o feed seja só de notas, e não longo demais.
 *
 * Vinte itens, e não os dez do padrão: uma nota é curta, e um leitor de RSS
 * que só vê as dez últimas perde uma semana de notas em três dias de silêncio
 * do blog.
 *
 * @param WP_Query $consulta A consulta prestes a rodar.
 */
function vc_notas_consulta_do_feed( $consulta ) {
	if ( is_admin() || ! $consulta->is_feed() ) {
		return;
	}

	if ( VC_NOTA !== $consulta->get( 'post_type' ) ) {
		return;
	}

	$consulta->set( 'post_type', VC_NOTA );
	$consulta->set( 'posts_per_page', 20 );
}
add_action( 'pre_get_posts', 'vc_notas_consulta_do_feed' );

/**
 * Anuncia o feed no `<head>` das páginas de nota.
 *
 * O WordPress só imprime o `<link rel="alternate">` do feed geral e dos
 * arquivos de taxonomia. Sem esta linha, quem tem um leitor de RSS precisa
 * adivinhar o endereço — e adivinhar endereço é o que ninguém faz.
 *
 * `get_post_type_archive_feed_link()` devolve exatamente a URL que as regras
 * acima montaram, e devolve `false` num site em que o post type não tenha
 * arquivo: daí a checagem, e não um `printf` confiando no valor.
 */
function vc_notas_link_do_feed() {
	if ( ! is_post_type_archive( VC_NOTA ) && ! is_singular( VC_NOTA ) ) {
		return;
	}

	$vc_url = get_post_type_archive_feed_link( VC_NOTA );

	if ( ! $vc_url ) {
		return;
	}

	printf(
		'<link rel="alternate" type="application/rss+xml" title="%s" href="%s" />' . "\n",
		esc_attr( sprintf( '%s — %s', __( 'Notas', 'viniciuscodes' ), get_bloginfo( 'name' ) ) ),
		esc_url( $vc_url )
	);
}
add_action( 'wp_head', 'vc_notas_link_do_feed' );

/* -------------------------------------------------------------------------
 * 2. O que os templates usam
 * ---------------------------------------------------------------------- */

/**
 * "há 2 horas", com as duas pontas no mesmo fuso.
 *
 * `get_post_time( 'U', true )` devolve o timestamp GMT, e `time()` também é
 * GMT: comparar os dois evita o erro clássico de somar o offset do site duas
 * vezes, que faz uma nota publicada agora aparecer como "há 3 horas".
 *
 * A data absoluta continua no `title` do `<time>` e no atributo `datetime`,
 * no template. "há 8 meses" é uma boa resposta para o que é recente e uma
 * resposta ruim para o que não é; o `title` resolve sem trocar o desenho.
 *
 * @param int|WP_Post|null $vc_post O post (ou o ID), ou null para o atual.
 * @return string O intervalo relativo, ou string vazia sem post.
 */
function vc_nota_tempo_relativo( $vc_post = null ) {
	$vc_post = get_post( $vc_post );

	if ( ! $vc_post ) {
		return '';
	}

	$vc_quando = (int) get_post_time( 'U', true, $vc_post );
	$vc_agora  = time();

	if ( $vc_quando > $vc_agora ) {
		return __( 'agendada', 'viniciuscodes' );
	}

	return sprintf(
		/* translators: %s: intervalo de tempo, ex.: "2 horas". */
		__( 'há %s', 'viniciuscodes' ),
		human_time_diff( $vc_quando, $vc_agora )
	);
}

/*
 * AQUI HAVIA O `vc_nota_rotulo_respostas()`, que escrevia "responder", "1
 * resposta" ou "N respostas" para o rodapé de cada nota. Ele saiu junto com o
 * rodapé: as notas deixaram de aceitar comentário, e o atalho para responder
 * deixou de existir. Fica registrado em vez de simplesmente apagado porque, no
 * dia em que as respostas do Fediverso voltarem a aparecer numa nota, esta é a
 * função que se quer de volta.
 */

/**
 * Os slugs dos temas da nota, para o atributo `data-temas`.
 *
 * É o mesmo dado, na mesma forma, que um `.post-item` das listas do tema
 * carrega: slugs separados por espaço. Nenhum script lê isso em `/notas/`
 * hoje — está aqui porque é o contrato do componente de lista do tema, e um
 * filtro futuro encontra a lista pronta em vez de vazia.
 *
 * @param int|WP_Post|null $vc_post O post (ou o ID), ou null para o atual.
 * @return string Os slugs separados por espaço.
 */
function vc_nota_slugs_de_tema( $vc_post = null ) {
	$vc_post = get_post( $vc_post );

	if ( ! $vc_post ) {
		return '';
	}

	$vc_slugs = wp_get_post_terms( $vc_post->ID, 'category', array( 'fields' => 'slugs' ) );

	if ( is_wp_error( $vc_slugs ) || ! $vc_slugs ) {
		return '';
	}

	return implode( ' ', $vc_slugs );
}

/* -------------------------------------------------------------------------
 * 3. A folha de estilo da linha do tempo
 * ---------------------------------------------------------------------- */

/**
 * Carrega `assets/notas.css` só onde ele é usado.
 *
 * O tema já faz isso com `filtros.css`: a folha da lista de textos só entra na
 * lista de textos. A razão vale igual aqui — a linha do tempo tem regras que
 * não têm o que fazer no resto do site.
 *
 * `filemtime()` no lugar de um número escrito à mão: é um arquivo só, e assim
 * o cache do navegador cai sozinho quando o arquivo muda. A versão do tema
 * (`1.0.42`) continua intocada por este recurso.
 */
function vc_notas_assets() {
	if ( ! is_singular( VC_NOTA ) && ! is_post_type_archive( VC_NOTA ) ) {
		return;
	}

	$vc_caminho = get_theme_file_path( 'assets/notas.css' );

	if ( ! file_exists( $vc_caminho ) ) {
		return;
	}

	wp_enqueue_style(
		'vc-notas',
		get_theme_file_uri( 'assets/notas.css' ),
		array(),
		(string) filemtime( $vc_caminho )
	);
}
add_action( 'wp_enqueue_scripts', 'vc_notas_assets' );
