<?php
/**
 * O que o tema precisa saber fazer.
 *
 * O QUE ESTE ARQUIVO NÃO FAZ, E POR QUÊ
 * -------------------------------------
 * Não escreve <style> nem <link> dentro dos templates. Todo estilo entra por
 * `wp_enqueue_style()`, e todo script por `wp_enqueue_script()`. Isso não é
 * preciosismo de padrão: é o que deixa o WordPress cuidar de versão (cache),
 * de ordem entre as folhas e de dependência — e é o que impede a mesma folha
 * ser pedida duas vezes quando um plugin também precisa dela.
 *
 * A ÚNICA EXCEÇÃO É O TEMA CLARO/ESCURO, e ela não está aqui: está no
 * `header.php`, porque a escolha guardada pelo leitor tem de ser aplicada antes
 * da primeira pintura da página. Script enfileirado chega depois do <head> e a
 * página pisca branca antes de escurecer. Ver o comentário lá.
 *
 * @package ViniciusCodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vc_style_path = __DIR__ . '/style.css';
define( 'VC_VERSAO', file_exists( $vc_style_path ) ? (string) filemtime( $vc_style_path ) : '1791744180000' );

/**
 * Retorna o carimbo dinâmico de modificação do arquivo (cache-busting dinâmico granular).
 * Se o arquivo específico foi editado, o navegador baixa a versão nova na hora sem afetar os outros.
 */
function vc_asset_ver( string $caminho_relativo ): string {
	$caminho_completo = get_theme_file_path( $caminho_relativo );
	return file_exists( $caminho_completo ) ? (string) filemtime( $caminho_completo ) : VC_VERSAO;
}

/* -------------------------------------------------------------------------
   RECURSOS DO TEMA
   ------------------------------------------------------------------------- */
add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'title-tag' );
		remove_post_type_support( 'post', 'thumbnail' );

		/*
		 * `automatic-feed-links` NÃO entra, de propósito. Ele faz o núcleo anunciar
		 * o feed em `/feed/`, e o site publica o mesmo feed em `/feed.xml` — e quem
		 * assina um feed guarda o endereço. O anúncio agora é escrito à mão no
		 * `header.php`, apontando para `/feed.xml`.
		 */

		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
		);

		/*
		 * O editor de blocos passa a mostrar o texto com o mesmo CSS do site.
		 * Sem isso, o autor escreve olhando uma tipografia e publica outra — que
		 * foi exatamente o problema que a versão Astro levou tempo para resolver.
		 */
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/editor-style.css' );
	}
);

/* -------------------------------------------------------------------------
   CSS E JAVASCRIPT
   ------------------------------------------------------------------------- */
add_action(
	'wp_enqueue_scripts',
	static function () {
		/*
		 * A ORDEM ENTRE AS DUAS FOLHAS IMPORTA. `fonts.css` declara os
		 * @font-face e `global.css` usa a família. Na ordem inversa o navegador
		 * só descobre a fonte depois de já precisar dela, e o texto troca de
		 * forma na frente do leitor.
		 *
		 * As duas são o CSS do site original, copiado inteiro, sem reescrita:
		 * os templates repetem a mesma estrutura e os mesmos nomes de classe dos
		 * componentes Astro, então o CSS que já existia desenha a página.
		 */
		wp_enqueue_style( 'vc-fontes', get_theme_file_uri( 'assets/fonts.css' ), array(), vc_asset_ver( 'assets/fonts.css' ) );
		// vc-global unificado no vc-substack-stage2

		/*
		 * `true` no último argumento carrega no rodapé. Os dois
		 * comportamentos deste arquivo — trocar o tema e voltar ao topo —
		 * dependem de clique, então atrasá-los não custa nada e não bloqueia a
		 * primeira pintura.
		 */
		wp_enqueue_script( 'vc-site', get_theme_file_uri( 'assets/site.js' ), array(), vc_asset_ver( 'assets/site.js' ), true );

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}


	}
);

/*
 * Blindagem contra fonte de terceiro externa (Google Fonts, Adobe, Typekit).
 * Todas as fontes são autohospedadas em assets/fonts/ para máxima privacidade,
 * zero requisições adicionais de terceiros e sem bloqueio de renderização.
 */
add_filter(
	'style_loader_tag',
	static function ( $tag, $handle ) {
		return preg_match( '#fonts\.(googleapis|gstatic)\.com|use\.typekit\.net|use\.adobe\.com#i', (string) $tag ) ? '' : $tag;
	},
	10,
	2
);

add_filter(
	'wp_resource_hints',
	static function ( $hints, $relation ) {
		foreach ( (array) $hints as $chave => $dica ) {
			$url = is_array( $dica ) ? ( $dica['href'] ?? '' ) : $dica;
			if ( preg_match( '#fonts\.(googleapis|gstatic)\.com|use\.typekit\.net|use\.adobe\.com#i', (string) $url ) ) {
				unset( $hints[ $chave ] );
			}
		}

		return $hints;
	},
	10,
	2
);




/* -------------------------------------------------------------------------
   UM ENDEREÇO QUE PRECISA CONTINUAR VALENDO
   ------------------------------------------------------------------------- */
/*
 * O feed em `/feed.xml`, e não em `/feed/`.
 *
 * O site publicava o feed em `/feed.xml`. O WordPress publica em `/feed/`. Quem
 * assina o feed guarda o endereço, e um 404 ali só aparece quando a pessoa
 * percebe que parou de receber texto — tarde e em silêncio.
 *
 * AQUI HAVIA UM SEGUNDO ENDEREÇO, `/links-citados/`, que levava à página de
 * links citados. Ele saiu junto com a página, e não por esquecimento: sem a
 * página não existe destino, e uma regra apontando para uma página que não
 * existe é pior do que regra nenhuma — troca um 404 claro por uma consulta que
 * não acha nada e cai no mesmo 404.
 */
add_action(
	'init',
	static function () {
		add_rewrite_rule( '^feed\.xml$', 'index.php?feed=rss2', 'top' );
	}
);

/* -------------------------------------------------------------------------
   ÍCONES
   ------------------------------------------------------------------------- */
/**
 * Devolve um ícone do Lucide embutido no HTML, pronto para imprimir.
 *
 * O SVG é embutido em vez de referenciado por <img> pelo mesmo motivo do site
 * original: assim o traço herda `currentColor` do texto em volta, e o mesmo
 * arquivo serve para o tema claro e o escuro sem uma segunda versão.
 *
 * O ícone sai `aria-hidden` por padrão, porque quase sempre é acompanhamento de
 * um texto que já diz o que ele significa. Quando o ícone É o conteúdo, passa-se
 * o rótulo no quarto argumento e ele vira `role="img"` com nome acessível.
 *
 * @param string $nome     Nome do arquivo em assets/icons/lucide, sem .svg.
 * @param int    $tamanho  Lado do ícone em pixels.
 * @param string $classe   Classe extra para o CSS.
 * @param string $rotulo   Nome acessível; vazio significa decoração.
 */
function vc_icone( string $nome, int $tamanho = 20, string $classe = '', string $rotulo = '' ): string {
	$arquivo = get_theme_file_path( "assets/icons/lucide/{$nome}.svg" );

	if ( ! file_exists( $arquivo ) ) {
		/*
		 * Não é fatal de propósito. Um nome errado é erro de digitação, e o log
		 * avisa; derrubar a página inteira por causa de um ícone que faltou
		 * seria trocar um buraco por um site fora do ar.
		 */
		error_log( "[tema] ícone \"{$nome}\" não encontrado em assets/icons/lucide/" );
		return '';
	}

	$svg = (string) file_get_contents( $arquivo );

	// O arquivo original traz comentário, classe e tamanho próprios; os três
	// saem para o ícone obedecer ao CSS do site.
	$svg = preg_replace( '/<!--[\s\S]*?-->/', '', $svg );
	$svg = preg_replace( '/\sclass="[^"]*"/', '', $svg, 1 );
	$svg = preg_replace( '/\swidth="[^"]*"/', '', $svg, 1 );
	$svg = preg_replace( '/\sheight="[^"]*"/', '', $svg, 1 );

	$classes = trim( 'icon ' . $classe );
	$atributos = 'class="' . esc_attr( $classes ) . '" width="' . $tamanho . '" height="' . $tamanho . '"';

	if ( '' === $rotulo ) {
		$atributos .= ' aria-hidden="true" focusable="false"';
	} else {
		$atributos .= ' role="img" aria-label="' . esc_attr( $rotulo ) . '"';
	}

	return (string) preg_replace( '/<svg/', '<svg ' . $atributos, $svg, 1 );
}

/**
 * O ícone de um tema. O mapa é o mesmo do site: slug da categoria => nome do ícone.
 */
function vc_icone_do_tema( string $slug ): string {
	static $mapa = null;

	if ( null === $mapa ) {
		$json = (string) file_get_contents( get_theme_file_path( 'assets/tema-icons.json' ) );
		$mapa = json_decode( $json, true );
		if ( ! is_array( $mapa ) ) {
			$mapa = array();
		}
	}

	return $mapa[ $slug ] ?? 'sprout';
}

/* -------------------------------------------------------------------------
   DADOS QUE OS TEMPLATES PRECISAM
   ------------------------------------------------------------------------- */
/**
 * Os temas de um texto, com nome, endereço e ícone, já na ordem de exibição.
 *
 * @return array<int, array{slug: string, nome: string, icone: string}>
 */
function vc_temas_do_post( int $post_id ): array {
	$termos = get_the_terms( $post_id, 'category' );

	if ( ! is_array( $termos ) ) {
		return array();
	}

	$saida = array();
	foreach ( $termos as $termo ) {
		$saida[] = array(
			'slug'  => $termo->slug,
			'nome'  => $termo->name,
			'icone' => vc_icone_do_tema( $termo->slug ),
		);
	}

	return $saida;
}

/**
 * A ficha do livro resenhado, ou null quando o texto não é uma resenha.
 *
 * @return array<string, mixed>|null
 */
function vc_livro( int $post_id ): ?array {
	$bruto = get_post_meta( $post_id, '_vinicius_livro', true );

	if ( ! is_string( $bruto ) || '' === $bruto ) {
		return null;
	}

	$ficha = json_decode( $bruto, true );

	return is_array( $ficha ) && ! empty( $ficha['titulo'] ) ? $ficha : null;
}

/**
 * A ficha do lugar avaliado, ou null quando o texto não avalia lugar nenhum.
 *
 * Gemea da `vc_livro()`, e guardada do mesmo jeito (JSON numa string só). A
 * chave que confirma que a ficha existe é `nome`: o mesmo lugar pode ser
 * avaliado sem nota, e sem endereço, mas não sem nome.
 *
 * @param int $post_id ID do texto.
 * @return array|null A ficha, ou null.
 */
function vc_local( int $post_id ): ?array {
	$bruto = get_post_meta( $post_id, '_vinicius_local', true );

	if ( ! is_string( $bruto ) || '' === $bruto ) {
		return null;
	}

	$ficha = json_decode( $bruto, true );

	return is_array( $ficha ) && ! empty( $ficha['nome'] ) ? $ficha : null;
}

/**
 * O endereço de um arquivo da biblioteca pelo NOME ORIGINAL dele.
 *
 * A importação guardou em cada anexo o nome que o arquivo tinha no site antigo
 * (`_vinicius_arquivo_original`). Procurar por esse campo é o que permite o tema
 * pedir "a foto do retrato" ou "a imagem de prévia" sem ter o ID chumbado — e o
 * ID muda a cada máquina onde a importação roda.
 *
 * A busca fica em memória: a mesma imagem é pedida mais de uma vez por página.
 */
function vc_midia( string $nome ): string {
	static $memoria = array();

	if ( array_key_exists( $nome, $memoria ) ) {
		return $memoria[ $nome ];
	}

	$achados = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_vinicius_arquivo_original',
			'meta_value'     => $nome,
		)
	);

	$memoria[ $nome ] = $achados ? (string) wp_get_attachment_url( $achados[0] ) : '';

	return $memoria[ $nome ];
}

/**
 * A imagem que representa a página numa prévia de link.
 *
 * A regra é a mesma do site: a primeira imagem do próprio texto; se não houver,
 * a imagem padrão do site. Sem a primeira, todo texto compartilhado sairia com a
 * mesma cara.
 */
function vc_imagem_social( int $post_id = 0 ): string {
	if ( $post_id ) {
		$conteudo = (string) get_post_field( 'post_content', $post_id );

		if ( preg_match( '/<img[^>]+src="([^"]+)"/', $conteudo, $achado ) ) {
			return $achado[1];
		}

		if ( has_post_thumbnail( $post_id ) ) {
			return (string) get_the_post_thumbnail_url( $post_id, 'full' );
		}
	}

	$midia = vc_midia( 'og-default.jpg' );
	if ( ! empty( $midia ) ) {
		return $midia;
	}

	return get_theme_file_uri( 'assets/images/og-default.jpg' );
}

/** Data longa, no formato do site: "25 de setembro de 2026". */

/**
 * Obtém dimensões e tipo MIME da imagem social para WhatsApp, Twitter e Telegram.
 * Sem og:image:width, og:image:height e og:image:type, WhatsApp não reserva o espaço
 * e frequentemente renderiza apenas o quadrado pequeno ao invés do card grande (large summary).
 */
function vc_detalhes_imagem_social( string $url ): array {
	$info = array(
		'url'        => $url,
		'secure_url' => str_starts_with( $url, 'https://' ) ? $url : str_replace( 'http://', 'https://', $url ),
		'width'      => 1200,
		'height'     => 630,
		'type'       => 'image/jpeg',
	);

	if ( empty( $url ) ) {
		return $info;
	}

	$uploads = wp_upload_dir();
	$theme_url = get_theme_file_uri();
	$theme_dir = get_theme_file_path();

	$caminho_local = '';

	if ( str_starts_with( $url, $uploads['baseurl'] ) ) {
		$rel = substr( $url, strlen( $uploads['baseurl'] ) );
		$caminho_local = $uploads['basedir'] . $rel;
	} elseif ( str_starts_with( $url, $theme_url ) ) {
		$rel = substr( $url, strlen( $theme_url ) );
		$caminho_local = $theme_dir . $rel;
	}

	if ( $caminho_local && file_exists( $caminho_local ) ) {
		$sz = @getimagesize( $caminho_local );
		if ( $sz && ! empty( $sz[0] ) && ! empty( $sz[1] ) ) {
			$info['width']  = (int) $sz[0];
			$info['height'] = (int) $sz[1];
			$info['type']   = (string) $sz['mime'];
		}
	}

	return $info;
}

function vc_data_longa( ?int $post_id = null ): string {
	/*
	 * O mês é escrito aqui, e não pedido ao WordPress, porque este site está
	 * configurado em inglês (`get_locale()` devolve `en_US`) e `date_i18n()` só
	 * traduz quando existe idioma instalado. O efeito era "29 de September de
	 * 2026" no cabeçalho de todo texto.
	 *
	 * Doze nomes resolvem sem depender de idioma instalado — e sem trocar o
	 * idioma do painel, que o autor usa em inglês. A data vem do próprio
	 * WordPress (`get_the_date`), que já aplica o fuso do site: converter o
	 * carimbo na mão aplicaria o deslocamento duas vezes.
	 */
	$meses = array(
		1  => 'janeiro',
		2  => 'fevereiro',
		3  => 'março',
		4  => 'abril',
		5  => 'maio',
		6  => 'junho',
		7  => 'julho',
		8  => 'agosto',
		9  => 'setembro',
		10 => 'outubro',
		11 => 'novembro',
		12 => 'dezembro',
	);

	list( $vc_ano, $vc_mes, $vc_dia ) = array_map( 'intval', explode( '-', (string) get_the_date( 'Y-m-d', $post_id ) ) );

	return sprintf( '%d de %s de %d', $vc_dia, $meses[ $vc_mes ], $vc_ano );
}

/** Data de modificação longa, no formato do site: "25 de setembro de 2026". */
function vc_data_modificada_longa( ?int $post_id = null ): string {
	$meses = array(
		1  => 'janeiro',
		2  => 'fevereiro',
		3  => 'março',
		4  => 'abril',
		5  => 'maio',
		6  => 'junho',
		7  => 'julho',
		8  => 'agosto',
		9  => 'setembro',
		10 => 'outubro',
		11 => 'novembro',
		12 => 'dezembro',
	);

	list( $vc_ano, $vc_mes, $vc_dia ) = array_map( 'intval', explode( '-', (string) get_the_modified_date( 'Y-m-d', $post_id ) ) );

	return sprintf( '%d de %s de %d', $vc_dia, $meses[ $vc_mes ], $vc_ano );
}

/**
 * Data curta, a da lista de textos: "25/09/2026".
 *
 * A DATA SAI DE `get_the_date()`, como a longa — e não de `date_i18n()` sobre o
 * carimbo, que é o que estava aqui.
 *
 * A conta na mão aplicava o fuso DUAS vezes: `get_post_timestamp()` já devolve um
 * instante absoluto (ele monta a data com o fuso do site), e `date_i18n()` soma o
 * deslocamento do site outra vez. Medido: um texto gravado às 21:29 de 26/09
 * aparecia na home como **27/09/2026**, um dia à frente, enquanto o próprio
 * `<time datetime>` ao lado dele dizia `2026-09-26T21:29:00-03:00` — a contradição
 * estava na mesma linha da lista.
 *
 * Só aparecia depois das 21h, porque é aí que três horas empurram a data para o
 * dia seguinte. Foi por isso que passou despercebido: os textos publicados tinham
 * horários de manhã e de tarde, e o defeito só se mostrou quando um foi gravado à
 * noite. Vale como lembrete de que este erro não depende do fuso do leitor — ele
 * está na conversão, e é o mesmo para todo mundo.
 */
function vc_data_curta( ?int $post_id = null ): string {
	return (string) get_the_date( 'd/m/Y', $post_id );
}

/**
 * Palavras e minutos de leitura de um post.
 *
 * Devolve os dois números juntos porque quem mostra um quase sempre quer o
 * outro, e porque a contagem do texto é a parte cara: ela tira a marcação de
 * alguns milhares de caracteres. Duas funções fariam esse trabalho duas vezes na
 * mesma página.
 *
 * A contagem é feita sobre o texto sem marcação: contar o HTML trataria o
 * caminho de uma imagem como se fosse palavra.
 *
 * DUAS MEDIÇÕES DECIDIRAM O MÉTODO, na nota de 2.400 palavras do site:
 *
 * 1. A quebra por espaço em branco (a que está aqui) conta 2.403 palavras;
 *    `str_word_count()` conta 2.607. O número maior é o ERRADO: aquela função
 *    parte a palavra em cada caractere que não seja `[a-zA-Z']`, então "cabeça"
 *    vira duas e "nerdzinho-isolado" também. Em português ela exagera.
 * 2. Mas a quebra por espaço também tem fantasma: pontuação solta entre espaços
 *    entra na conta. Medido: 13 tokens em 2.403 (0,5%), e o tempo de leitura
 *    saía igual com ou sem o filtro que os descartaria. Meio por cento que não
 *    muda nada na tela não vale uma passagem de regex por palavra.
 *
 * 200 palavras por minuto, e SEMPRE para cima. Arredondar para o mais próximo
 * faria esta nota anunciar 12 minutos quando são 12,015; o `ceil()` diz 13. É um
 * minuto de diferença, e o erro cai para o lado certo: quem se programa por
 * baixo se atrasa no meio do texto. O "~" impresso ao lado do número assume que
 * é aproximação.
 *
 * @return array{palavras:int,minutos:int}
 */
function vc_get_reading_stats( int $post_id ): array {
	$texto    = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
	$palavras = count( preg_split( '/\s+/', $texto, -1, PREG_SPLIT_NO_EMPTY ) );

	return array(
		'palavras' => $palavras,
		'minutos'  => max( 1, (int) ceil( $palavras / 200 ) ),
	);
}

/**
 * Só os minutos, para quem já chamava esta função antes de o site mostrar a
 * contagem de palavras ao lado. O número vem da mesma fonte, não de uma segunda
 * conta que poderia divergir dela.
 */
function vc_tempo_de_leitura( int $post_id ): int {
	return vc_get_reading_stats( $post_id )['minutos'];
}

/**
 * Todos os temas, do mais cheio para o mais vazio; empate vai por ordem
 * alfabética, em português.
 *
 * @param bool $hide_empty Se deve esconder categorias sem posts (padrão: true).
 * @return array<int, WP_Term>
 */
function vc_temas_ordenados( bool $hide_empty = true ): array {
	$termos = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => $hide_empty,
		)
	);

	if ( is_wp_error( $termos ) || ! is_array( $termos ) ) {
		return array();
	}

	usort(
		$termos,
		static function ( $a, $b ) {
			if ( $a->count !== $b->count ) {
				return $b->count <=> $a->count;
			}
			return strcoll( $a->name, $b->name );
		}
	);

	return $termos;
}

/**
 * As páginas fixas do site, na ordem da barra lateral.
 *
 * A ORDEM É DE INTERESSE DE QUEM LÊ, e não a ordem em que as páginas foram
 * escritas, nem alfabética. Quem chega aqui veio de uma nota, e o menu responde
 * às perguntas nessa sequência:
 *
 *   o que mais tem para ler   -> todos os posts
 *   quem escreveu isso        -> sobre mim
 *   o que essa pessoa indica  -> o acervo, blogroll
 *   que lugar é este          -> manifesto, como o site é feito
 *   falar comigo e acompanhar -> contato, feed RSS
 *
 * UMA MUDANÇA TEM MOTIVO, e não é gosto:
 *
 * 1. "vídeos" desceu de segundo para sétimo. O canal ainda não tem vídeo
 *    publicado, então `/videos/` responde "nenhum vídeo por aqui ainda": um
 *    convite em segundo lugar que leva a uma página vazia ensina ao leitor que
 *    ali não tem nada. É a única página do menu que hoje está vazia, e por isso
 *    é a única que perdeu posição por outro motivo que não o interesse.
 *    QUANDO HOUVER VÍDEOS, este item merece voltar para junto de "todas as
 *    notas" — basta trocar as duas linhas de lugar.
 *
 * "contato" e "feed RSS" ficam no fim de propósito: são ações de quem já
 * decidiu ficar, não de quem está conhecendo o site.
 *
 * Os endereços são os mesmos do site Astro de propósito: as páginas ainda serão
 * criadas aqui, e manter o endereço agora evita ter de corrigir link depois.
 *
 * @return array<int, array{label: string, href: string, icone: string}>
 */
function vc_navegacao(): array {
	return array(
		array( 'label' => 'posts',               'href' => '/posts/',       'icone' => 'newspaper' ),
		array( 'label' => 'sobre mim',           'href' => '/sobre/',       'icone' => 'user-round' ),
		array( 'label' => 'blogroll',            'href' => '/blogroll/',    'icone' => 'handshake' ),
		array( 'label' => 'contato',             'href' => '/contato/',     'icone' => 'mail' ),
		array( 'label' => 'feed RSS',            'href' => '/feed-rss/',    'icone' => 'rss' ),
	);
}

/**
 * O menu do rodapé — vazio de propósito, desde 04/10/2026.
 *
 * AQUI HAVIA três endereços: manifesto, changelog e contato. Os três passaram
 * para a barra lateral, junto das outras páginas do site, que é onde alguém os
 * procura. O changelog subiu para `vc_navegacao()` (logo antes de "contato",
 * onde estava na ordem do rodapé); o manifesto e o contato JÁ estavam lá.
 *
 * A função continua existindo, vazia, porque é ela que `vc_itens_do_menu()`
 * consulta quando o menu do painel não responde — apagá-la trocaria uma lista
 * vazia por um erro fatal. O `footer.php` deixou de chamar o
 * `vc_itens_do_menu( 'vc-rodape' )`, e com isso o `<nav>` do colofão saiu.
 *
 * @return array<int, array{label: string, href: string}>
 */
function vc_rodape(): array {
	return array();
}

/**
 * Retorna a lista de blogs do blogroll.
 *
 * @return array<int, array{nome: string, url: string, dominio: string, favicon: string}>
 */
function vc_blogroll_lista(): array {
	return array(
		array(
			'nome'    => 'aline Valek',
			'url'     => 'https://alinevalek.com.br/',
			'dominio' => 'alinevalek.com.br',
			'favicon' => 'alinevalek.com.br.png',
		),
		array(
			'nome'    => 'Amuri',
			'url'     => 'https://amuri.substack.com/',
			'dominio' => 'amuri.substack.com',
			'favicon' => 'amuri.substack.com.png',
		),
		array(
			'nome'    => 'blog do TH',
			'url'     => 'https://blogdoth.bearblog.dev/blog/',
			'dominio' => 'blogdoth.bearblog.dev',
			'favicon' => 'blogdoth.bearblog.dev.png',
		),
		array(
			'nome'    => 'BMRTT',
			'url'     => 'https://bamoretti.com/',
			'dominio' => 'bamoretti.com',
			'favicon' => 'bamoretti.com.ico',
		),
		array(
			'nome'    => 'Bruno Pulis',
			'url'     => 'https://brunopulis.com',
			'dominio' => 'brunopulis.com',
			'favicon' => 'brunopulis.com.svg',
		),
		array(
			'nome'    => 'Cosmoliko',
			'url'     => 'https://cosmoliko.com/',
			'dominio' => 'cosmoliko.com',
			'favicon' => 'cosmoliko.com.ico',
		),
		array(
			'nome'    => 'daniel Santos',
			'url'     => 'https://danielsantos.org/',
			'dominio' => 'danielsantos.org',
			'favicon' => 'danielsantos.org.ico',
		),
		array(
			'nome'    => 'Gustavo Belo',
			'url'     => 'https://www.gustavobelo.com/',
			'dominio' => 'gustavobelo.com',
			'favicon' => 'gustavobelo.com.ico',
		),
		array(
			'nome'    => 'Gustavo Ribeiro',
			'url'     => 'https://gustavoribeiro.net/blog',
			'dominio' => 'gustavoribeiro.net',
			'favicon' => 'gustavoribeiro.net.png',
		),
		array(
			'nome'    => 'Hey! I\'m With the Band',
			'url'     => 'https://www.heyimwiththeband.com.br/',
			'dominio' => 'heyimwiththeband.com.br',
			'favicon' => 'heyimwiththeband.com.br.ico',
		),
		array(
			'nome'    => 'Igor Medeiroz',
			'url'     => 'https://igormedeiroz.com/',
			'dominio' => 'igormedeiroz.com',
			'favicon' => 'igormedeiroz.com.ico',
		),
		array(
			'nome'    => 'Inventando Assunto',
			'url'     => 'https://www.inventandoassunto.com/',
			'dominio' => 'inventandoassunto.com',
			'favicon' => 'inventandoassunto.com.ico',
		),
		array(
			'nome'    => 'Irrelefante',
			'url'     => 'https://irrelefante.com.br/',
			'dominio' => 'irrelefante.com.br',
			'favicon' => 'irrelefante.com.br.png',
		),
		array(
			'nome'    => 'Maira Namba',
			'url'     => 'https://www.mairanamba.com/',
			'dominio' => 'mairanamba.com',
			'favicon' => 'mairanamba.com.png',
		),
		array(
			'nome'    => 'Manual do Usuário',
			'url'     => 'https://manualdousuario.net',
			'dominio' => 'manualdousuario.net',
			'favicon' => 'manualdousuario.net.png',
		),
		array(
			'nome'    => 'Marcos Ramon',
			'url'     => 'https://marcosramon.net/',
			'dominio' => 'marcosramon.net',
			'favicon' => 'marcosramon.net.png',
		),
		array(
			'nome'    => 'meu Tédio',
			'url'     => 'https://www.meutedio.com/',
			'dominio' => 'meutedio.com',
			'favicon' => 'meutedio.com.ico',
		),
		array(
			'nome'    => 'o sol na cabeça',
			'url'     => 'https://osolnacabeca.com.br/',
			'dominio' => 'osolnacabeca.com.br',
			'favicon' => 'osolnacabeca.com.br.png',
		),
		array(
			'nome'    => 'Papacapim',
			'url'     => 'https://www.papacapim.org/',
			'dominio' => 'papacapim.org',
			'favicon' => 'papacapim.org.png',
		),
		array(
			'nome'    => 'pedro.dalbo',
			'url'     => 'https://pedro.dalbo.me/',
			'dominio' => 'pedro.dalbo.me',
			'favicon' => 'pedro.dalbo.me.png',
		),
		array(
			'nome'    => 'prazer, Willian',
			'url'     => 'https://prazerwillian.blogspot.com/',
			'dominio' => 'prazerwillian.blogspot.com',
			'favicon' => 'prazerwillian.blogspot.com.ico',
		),
		array(
			'nome'    => 'rádio escafandro',
			'url'     => 'https://radioescafandro.com/',
			'dominio' => 'radioescafandro.com',
			'favicon' => 'radioescafandro.com.png',
		),
		array(
			'nome'    => 'Rua da Feira 🥬',
			'url'     => 'https://ruadafeira.varzea.recife.br/',
			'dominio' => 'ruadafeira.varzea.recife.br',
			'favicon' => 'ruadafeira.varzea.recife.br.svg',
		),
		array(
			'nome'    => 'Taddone',
			'url'     => 'https://www.taddone.it/',
			'dominio' => 'taddone.it',
			'favicon' => 'taddone.it.png',
		),
		array(
			'nome'    => 'tech Demo',
			'url'     => 'https://techdemo.bearblog.dev/',
			'dominio' => 'techdemo.bearblog.dev',
			'favicon' => 'techdemo.bearblog.dev.png',
		),
		array(
			'nome'    => 'th.blog.br',
			'url'     => 'https://th.blog.br/',
			'dominio' => 'th.blog.br',
			'favicon' => 'th.blog.br.svg',
		),
		array(
			'nome'    => '🛌 Eu não quero sair da cama.',
			'url'     => 'https://heyimbriel.bearblog.dev/',
			'dominio' => 'heyimbriel.bearblog.dev',
			'favicon' => 'heyimbriel.bearblog.dev.png',
		),
	);
}

/**
 * Retorna uma quantidade N de blogs aleatórios do blogroll.
 *
 * @param int $qtd
 * @return array<int, array{nome: string, url: string, dominio: string, favicon: string}>
 */
function vc_blogroll_aleatorios( int $qtd = 4 ): array {
	$blogs = vc_blogroll_lista();
	shuffle( $blogs );
	return array_slice( $blogs, 0, $qtd );
}

/**
 * Retorna os posts agrupados por Ano e Mês para o arquivo retrátil da barra lateral.
 *
 * @return array<string, array{total: int, meses: array<string, array{nome: string, link: string, total: int, posts: array<int, array{id: int, titulo: string, url: string}>}>}>
 */
function vc_arquivo_agrupado(): array {
	static $arquivo = null;
	if ( null !== $arquivo ) {
		return $arquivo;
	}

	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	$meses_nomes = array(
		'01' => 'jan.',
		'02' => 'fev.',
		'03' => 'mar.',
		'04' => 'abr.',
		'05' => 'mai.',
		'06' => 'jun.',
		'07' => 'jul.',
		'08' => 'ago.',
		'09' => 'set.',
		'10' => 'out.',
		'11' => 'nov.',
		'12' => 'dez.',
	);

	$arquivo = array();

	foreach ( $posts as $p ) {
		$ano = get_the_date( 'Y', $p );
		$mes = get_the_date( 'm', $p );

		if ( ! isset( $arquivo[ $ano ] ) ) {
			$arquivo[ $ano ] = array(
				'total' => 0,
				'meses' => array(),
			);
		}

		if ( ! isset( $arquivo[ $ano ]['meses'][ $mes ] ) ) {
			$arquivo[ $ano ]['meses'][ $mes ] = array(
				'nome'  => $meses_nomes[ $mes ] ?? $mes,
				'link'  => get_month_link( (int) $ano, (int) $mes ),
				'total' => 0,
				'posts' => array(),
			);
		}

		$arquivo[ $ano ]['total']++;
		$arquivo[ $ano ]['meses'][ $mes ]['total']++;
		$arquivo[ $ano ]['meses'][ $mes ]['posts'][] = array(
			'id'     => $p->ID,
			'titulo' => get_the_title( $p ),
			'url'    => get_permalink( $p ),
		);
	}

	return $arquivo;
}

/* -------------------------------------------------------------------------
   A IMAGEM DE DESTAQUE
   ------------------------------------------------------------------------- */
/**
 * A imagem de destaque, como figura do texto.
 *
 * POR QUE ELA NÃO APARECIA. O tema declara `add_theme_support( 'post-thumbnails' )`
 * desde o começo, mas nenhum template imprimia a imagem — ela só alimentava o
 * `og:image`. Marcar a imagem no painel não tinha para onde ir: o dado existia e
 * não havia quem o desenhasse.
 *
 * ELA ENTRA COMO PRIMEIRO FILHO DO `.prose`, e não antes dele. É o que dá de
 * graça toda a linguagem de imagem que o site já tem: borda de `--line`, raio de
 * `--radius`, a legenda em itálico e centralizada, e o ritmo vertical do texto.
 * Fora do `.prose` a mesma figura precisaria de um segundo conjunto de regras, e
 * duas regras para a mesma imagem vivem de divergir.
 *
 * A LEGENDA sai da legenda da MÍDIA (`wp_get_attachment_caption`), que é onde o
 * WordPress guarda esse texto — e não do campo de imagem de destaque, que não tem
 * legenda própria. O `alt` sai da biblioteca de mídia, pelo caminho normal do
 * `wp_get_attachment_image()`. Quando ele está vazio, a imagem é anunciada como
 * DECORAÇÃO, que é o padrão certo para uma capa que só repete o que o título já
 * diz; se a imagem disser algo que o título não diz, o lugar da frase é o `alt`
 * na biblioteca.
 *
 * `loading="eager"` e `fetchpriority="high"` porque ela está acima da dobra em
 * toda nota que a tem, e é o maior elemento da primeira tela: deixar o navegador
 * descobrir isso sozinho significa atrasar o carregamento do que o leitor olha
 * primeiro. O `decoding="async"` é o resto da mesma ideia.
 *
 * O `sizes` é declarado à mão porque o WordPress não tem como adivinhar a
 * largura da coluna: sem ele, o navegador escolhe o arquivo por `100vw` e baixa
 * uma imagem maior do que os 620px de coluna em toda tela larga.
 *
 * @param int $post_id O texto cuja capa deve sair.
 * @return string O `<figure>` pronto, ou string vazia quando não há imagem.
 */
function vc_imagem_de_destaque( int $post_id ): string {
	// Digital Garden minimalista: eliminação completa de capas de posts.
	return '';
}

/* -------------------------------------------------------------------------
   O CAMPO DE BUSCA
   ------------------------------------------------------------------------- */
/**
 * O formulário de busca, no desenho do site — e não no do WordPress.
 *
 * POR QUE NÃO `get_search_form()`. O formulário do núcleo sai com as classes dele
 * (`.search-form`, `.screen-reader-text`, `.search-submit`), que nenhuma regra
 * deste tema conhece. Já havia uma decisão escrita com base nisso: a página 404
 * não oferecia busca porque um campo sem estilo não ajuda mais do que uma lista
 * completa. Com o campo desenhado aqui, aquela decisão pode ser revista — o que
 * faltava não era a busca, era o desenho dela.
 *
 * O DESENHO VEM DO SITE ANTERIOR e não foi inventado: o campo com o ícone à
 * esquerda, borda, fundo e 44px de altura é o mesmo formulário do
 * `Header.astro`, e as medidas são as mesmas. Ele chegou a existir lá e não foi
 * portado para cá — esta é a reposição.
 *
 * A CLASSE É `site-search`, E NÃO `search`, e o motivo é medido: o
 * `body_class()` do WordPress escreve `class="search search-results ..."` no
 * `<body>` das páginas de resultado — justamente a página para onde este
 * formulário leva. Com o nome `search`, as regras do CSS pegavam o corpo
 * inteiro (o `<body>` virava flex, com `max-width: 260px`, borda de campo e
 * fundo) e a página de resultado colapsava de 1280px para 260px de largura.
 *
 * O `ID` É ÚNICO POR PÁGINA, e é por isso que este bloco tem contador. O
 * formulário pode aparecer mais de uma vez no mesmo documento — no cabeçalho e,
 * se a decisão da 404 for revista, no corpo dela — e dois `id` iguais quebram a
 * ligação entre o `<label>` e o `<input>`: o clique no rótulo passaria a levar
 * ao campo errado.
 *
 * O `<label>` sai `visually-hidden` porque o campo já se explica pelo ícone de
 * lupa e pelo texto de exemplo, e um rótulo visível na barra de 68px não cabe.
 * Ele NÃO é dispensável: é o nome acessível do campo, e é o que faz o leitor de
 * tela anunciar "buscar no site" em vez de "campo de edição".
 *
 * O rótulo e o texto de exemplo começam com a MESMA frase, de propósito: quem
 * usa controle por voz diz o que está vendo na tela, e a regra do nome acessível
 * exige que ele contenha o texto visível.
 *
 * O `name="s"` é o do WordPress, e o `action` aponta para a raiz do site: é o
 * que faz o endereço sair `/ ?s=palavra`, que é o formato que a página de
 * resultado do tema já lê.
 *
 * @return string O `<form>` pronto para imprimir.
 */
function vc_formulario_de_busca( string $classe_extra = '' ): string {
	static $quantos = 0;
	$quantos++;

	$id = 1 === $quantos ? 'vc-busca' : 'vc-busca-' . $quantos;
	$classe = trim( 'site-search-form ' . $classe_extra );

	return '<form class="' . esc_attr( $classe ) . '" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">'
		. '<span class="site-search-form__icon" aria-hidden="true">' . vc_icone( 'search', 16 ) . '</span>'
		. '<label class="visually-hidden" for="' . esc_attr( $id ) . '">buscar no site</label>'
		. '<input id="' . esc_attr( $id ) . '" type="search" name="s" placeholder="buscar no site..." value="' . esc_attr( get_search_query() ) . '" autocomplete="off" />'
		. '</form>';
}

/* -------------------------------------------------------------------------
   NAVEGAÇÃO: OS MENUS DO WORDPRESS
   ------------------------------------------------------------------------- */
/*
 * Dois menus, editáveis no painel: a barra lateral e o rodapé. Antes a lista era
 * fixa em `vc_navegacao()`/`vc_rodape()`, e mexer nela era mexer em PHP.
 *
 * O VISUAL NÃO MUDA. Cada item ganha o mesmo ícone de antes, resolvido pelo
 * ENDEREÇO e não guardado no banco: o WordPress não tem campo de ícone em item
 * de menu, e guardar um em meta faria o ícone mentir no dia em que a página
 * mudasse de endereço. O mapa abaixo é a única fonte do ícone.
 *
 * As listas fixas continuam como RESERVA: se o menu for apagado ou esvaziado no
 * painel, o site não fica sem navegação — ele volta ao que estava.
 */
add_action(
	'after_setup_theme',
	static function (): void {
		register_nav_menus(
			array(
				'vc-principal' => 'Navegação principal (barra lateral)',
				'vc-rodape'    => 'Rodapé',
			)
		);
	}
);

/**
 * O ícone de um destino, pelo endereço dele.
 *
 * A busca é por prefixo, como o estado ativo da barra lateral: `/notas/nota-20261004-0951/`
 * herda o ícone de `/notas/`. Endereço sem ícone próprio cai no genérico, para
 * uma página criada depois aparecer com alguma coisa e não com um buraco.
 */
function vc_icone_da_url( string $url ): string {
	$mapa = array(
		'/posts/'     => 'newspaper',
		'/microblog/' => 'square-pen',
		'/notas/'     => 'square-pen',
		'/videos/'    => 'play',
		'/sobre/'     => 'user-round',
		'/livros/'    => 'book',
		'/filmes/'    => 'film',
		'/acervo/'    => 'library-big',
		'/musica/'    => 'music',
		'/blogroll/'  => 'handshake',
		'/manifesto/' => 'scroll-text',
		'/changelog/' => 'history',
		'/contato/'   => 'mail',
		'/feed.xml'   => 'rss',
		'/feed-rss/'  => 'rss',
	);

	$caminho = (string) wp_parse_url( $url, PHP_URL_PATH );

	foreach ( $mapa as $destino => $icone ) {
		if ( $caminho === $destino || 0 === strpos( $caminho, rtrim( $destino, '/' ) ) ) {
			return $icone;
		}
	}

	return 'link';
}

/**
 * O que há no destino, em uma frase, para o rótulo curto não ficar ambíguo.
 *
 * A função nasceu de um problema que os rótulos de então criavam: "colofão" e
 * "blogroll" eram termos de ofício, "posts" era inglês num site em português e
 * "sobre" sozinho era uma preposição sem complemento — nada disso se deduz do
 * texto de um link na barra lateral, e quem precisa de previsibilidade para
 * clicar não deveria ter de adivinhar nem descobrir depois de já ter saído da
 * página.
 *
 * Os quatro rótulos foram reescritos no próprio menu ("todos os posts", "sobre
 * mim", "blogs que eu leio", "como o site é feito") e o problema passou a ser
 * resolvido onde ele nasce. A frase não virou enfeite por causa disso: ela
 * continua acrescentando o que não cabe no rótulo — a ORDEM da lista de notas, o
 * feed de cada blog, onde estão as ferramentas. O que ela perdeu
 * foi o direito de repetir o rótulo, e é por isso que a de `/blogroll/` mudou
 * junto: com o rótulo novo, "os blogs e sites que eu leio" dizia duas vezes a
 * mesma coisa.
 *
 * A frase NÃO substitui o rótulo visível: ela entra no `title` (aparece ao
 * passar o mouse) e no nome acessível, SEMPRE DEPOIS do texto que está na tela.
 * Essa ordem importa por causa da regra "o nome acessível contém o texto
 * visível": quem usa controle por voz continua podendo dizer "clicar sobre
 * mim", e quem usa leitor de tela ouve o rótulo curto e a explicação, nessa
 * ordem.
 *
 * A busca é pelo ENDEREÇO, como a dos ícones, e pela mesma razão: assim a frase
 * acompanha o destino mesmo que o rótulo seja reescrito no painel.
 */
function vc_contexto_da_url( string $url ): string {
	$mapa = array(
		'/posts/'     => 'a lista de todos os textos, do mais novo para o mais antigo',
		'/microblog/' => 'os textos curtos, sem título, na ordem em que saem',
		'/notas/'     => 'os textos curtos, sem título, na ordem em que saem',
		'/videos/'    => 'os vídeos do canal, cada um com transcrição e notas',
		'/sobre/'     => 'quem escreve este site',
		'/livros/'    => 'o que estou lendo, o que já li e a minha estante',
		'/filmes/'    => 'o que eu assisti no cinema e em casa, com as datas de cada sessão',
		'/acervo/'    => 'o que eu li e assisti: livros e filmes',
		'/musica/'    => 'os álbuns, artistas, músicas e podcasts mais ouvidos por ano via spotify',
		'/blogroll/'  => 'cada um com o feed para assinar',
		'/manifesto/' => 'por que este site existe e como ele se organiza',
		'/changelog/' => 'o histórico de mudanças e evolução deste site',
		'/contato/'   => 'como falar comigo',
		'/feed.xml'   => 'assinar os posts em um leitor de feed',
		'/feed-rss/'  => 'todos os endereços do site para assinar em um leitor de feeds',
	);

	$caminho = (string) wp_parse_url( $url, PHP_URL_PATH );

	if ( '' === $caminho ) {
		return '';
	}

	/* Barra final opcional: `/sobre` e `/sobre/` são o mesmo destino. */
	$exato = '/' . trim( $caminho, '/' ) . '/';

	if ( isset( $mapa[ $exato ] ) ) {
		return $mapa[ $exato ];
	}

	/* Endereço filho herda a frase do pai, como herda o ícone. */
	foreach ( $mapa as $destino => $frase ) {
		if ( 0 === strpos( $caminho, rtrim( $destino, '/' ) ) ) {
			return $frase;
		}
	}

	return '';
}

/**
 * Um item de navegação, já com ícone, frase de contexto e o estado "é a página
 * atual".
 *
 * @return array{label: string, href: string, icone: string, contexto: string, atual: bool}
 */
function vc_item_de_menu( string $label, string $href, string $caminho_atual ): array {
	$caminho = (string) wp_parse_url( $href, PHP_URL_PATH );

	return array(
		'label'    => $label,
		'href'     => $href,
		'icone'    => vc_icone_da_url( $href ),
		'contexto' => vc_contexto_da_url( $href ),
		'atual'    => '' !== $caminho && ( '/' === $caminho ? '/' === $caminho_atual : 0 === strpos( $caminho_atual, $caminho ) ),
	);
}

/**
 * Os itens de um menu do WordPress, com a lista fixa como reserva.
 *
 * @param string $local 'vc-principal' ou 'vc-rodape'.
 * @return array<int, array{label: string, href: string, icone: string, atual: bool}>
 */
function vc_itens_do_menu( string $local ): array {
	$caminho_atual = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
	$do_painel     = wp_get_nav_menu_items( $local );
	$itens         = array();

	$desativados = array( '/colofao/' );

	if ( is_array( $do_painel ) && $do_painel ) {
		foreach ( $do_painel as $item ) {
			$url_limpa = '/' . trim( (string) wp_parse_url( (string) $item->url, PHP_URL_PATH ), '/' ) . '/';
			if ( in_array( $url_limpa, $desativados, true ) ) {
				continue;
			}
			$itens[] = vc_item_de_menu( (string) $item->title, (string) $item->url, $caminho_atual );
		}

		return $itens;
	}

	$reserva = 'vc-principal' === $local ? vc_navegacao() : vc_rodape();

	foreach ( $reserva as $item ) {
		$url_limpa = '/' . trim( (string) wp_parse_url( (string) $item['href'], PHP_URL_PATH ), '/' ) . '/';
		if ( in_array( $url_limpa, $desativados, true ) ) {
			continue;
		}
		$itens[] = vc_item_de_menu( $item['label'], $item['href'], $caminho_atual );
	}

	return $itens;
}

/* -------------------------------------------------------------------------
   A PAGINAÇÃO
   ------------------------------------------------------------------------- */
/**
 * A paginação da lista, no desenho do site.
 *
 * A CONTA É DO NÚCLEO: `paginate_links()` calcula as páginas e monta os
 * endereços. O que este código faz é vestir o resultado com as classes do site
 * (`.paginacao__lista`, `.paginacao__botao`), porque o CSS que desenha o pager
 * já existia e não valia a pena reescrevê-lo no desenho do núcleo.
 *
 * As setas aparecem SEMPRE e ficam apagadas na ponta — é o que o site faz. O
 * `paginate_links()` sozinho omitiria a seta quando não há para onde ir, e o
 * pager mudaria de largura a cada troca de página.
 *
 * @param int    $total Total de páginas.
 * @param int    $atual Página atual.
 * @param string $base  Molde do endereço com `%#%`; vazio usa o do arquivo.
 */
function vc_paginacao( int $total, int $atual, string $base = '' ): void {
	if ( $total <= 1 ) {
		return;
	}

	$atual = min( max( 1, $atual ), $total );

	$args = array(
		'type'      => 'array',
		'prev_next' => false,
		'total'     => $total,
		'current'   => $atual,
		'mid_size'  => 4,
	);

	if ( '' !== $base ) {
		$args['base']   = $base;
		$args['format'] = '';
	}

	$numeros = paginate_links( $args );

	if ( ! is_array( $numeros ) ) {
		return;
	}

	$href = static function ( int $pagina ) use ( $base ): string {
		return '' !== $base
			? str_replace( '%#%', (string) $pagina, $base )
			: (string) get_pagenum_link( $pagina );
	};

	echo '<nav class="paginacao" aria-label="paginação"><ul class="paginacao__lista">';

	if ( $atual > 1 ) {
		printf(
			'<li><a class="paginacao__botao" href="%s" rel="prev" aria-label="página anterior">←</a></li>',
			esc_url( $href( $atual - 1 ) )
		);
	} else {
		echo '<li><span class="paginacao__botao paginacao__botao--off" aria-hidden="true">←</span></li>';
	}

	foreach ( $numeros as $numero ) {
		/*
		 * O núcleo escreve `class="page-numbers"` (e `current` na página atual).
		 * A classe do site entra NA FRENTE, sem tirar a do núcleo: uma folha
		 * futura que procure `.page-numbers` continua achando.
		 */
		$numero = (string) preg_replace(
			'/class="([^"]*page-numbers[^"]*)"/',
			'class="paginacao__botao $1"',
			(string) $numero
		);

		echo '<li>' . $numero . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- marcação do núcleo.
	}

	if ( $atual < $total ) {
		printf(
			'<li><a class="paginacao__botao" href="%s" rel="next" aria-label="próxima página">→</a></li>',
			esc_url( $href( $atual + 1 ) )
		);
	} else {
		echo '<li><span class="paginacao__botao paginacao__botao--off" aria-hidden="true">→</span></li>';
	}

	echo '</ul></nav>';
}

/* -------------------------------------------------------------------------
   COMENTÁRIOS: DESLIGADOS
   ------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------
   COMENTÁRIOS E CONVERSA: FORMATO ZEN, MINIMALISTA E COM MODERAÇÃO
   ------------------------------------------------------------------------- */
/*
 * O site tem comentários nativos e suporte a Webmentions/pingbacks da IndieWeb.
 *
 * Princípios do desenho:
 *  1. Sem ruído, sem avatares pesados de terceiros, sem captchas invasivos.
 *  2. Honeypot invisível (campo armadilha para robôs de spam).
 *  3. Moderação humana prévia para quem comenta pela primeira vez.
 *  4. Compatível com Webmentions do fediverso e outros blogs.
 */
add_action(
	'after_setup_theme',
	static function (): void {
		add_post_type_support( 'post', 'comments' );
		add_post_type_support( 'post', 'trackbacks' );
	}
);

// Honeypot anti-spam invisível
add_filter(
	'comment_form_default_fields',
	static function ( array $fields ): array {
		// Adiciona campo invisível que humanos não preenchem
		$fields['vc_hp_check'] = '<p class="vc-hp-field" aria-hidden="true">'
			. '<label for="vc_hp_website">se você é humano, deixe este campo em branco</label>'
			. '<input type="text" name="vc_hp_website" id="vc_hp_website" tabindex="-1" autocomplete="off" value="" />'
			. '</p>';
		return $fields;
	}
);

// Não exigir e-mail obrigatoriamente
add_filter( 'pre_option_require_name_email', '__return_zero' );

// Normalizar URL do autor (adicionar https:// se o usuário digitou sem protocolo)
add_filter(
	'preprocess_comment',
	static function ( array $commentdata ): array {
		// Se o campo honeypot foi preenchido, é um bot de spam
		if ( ! empty( $_POST['vc_hp_website'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			wp_die( 'comentário identificado como spam pelo filtro automatizado.', 'spam detectado', array( 'response' => 403 ) );
		}

		// Se o autor digitou site/blog sem protocolo (ex.: viniciuscodes.com.br), adiciona https://
		if ( ! empty( $commentdata['comment_author_url'] ) ) {
			$url = trim( $commentdata['comment_author_url'] );
			if ( ! preg_match( '#^https?://#i', $url ) ) {
				$commentdata['comment_author_url'] = 'https://' . $url;
			}
		}

		return $commentdata;
	}
);

/*
 * AS REGRAS DE URL PRECISAM SER REGRAVADAS quando CPTs ou taxonomias mudam.
 */
add_action(
	'init',
	static function (): void {
		$vc_versao = '2026-10-01-novideos';

		if ( get_option( 'vc_rewrite_versao' ) === $vc_versao ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( 'vc_rewrite_versao', $vc_versao, false );
	},
	20
);


/* -------------------------------------------------------------------------
   OS ÍCONES DO CONTEÚDO IMPORTADO
   ------------------------------------------------------------------------- */
/**
 * Troca os marcadores de ícone do conteúdo importado pelo SVG de verdade.
 *
 * O HTML que veio do WordPress antigo marca cada ícone com um espaço vazio e o
 * nome do arquivo: `<span class="ico" data-ico="leaf"></span>`. No site Astro
 * essa troca acontece no build; aqui ela acontece ao imprimir, com o mesmo
 * resultado — e pelo mesmo motivo: sem isso, o conteúdo importado sai com
 * buracos onde antes havia ícone.
 *
 * Roda depois de tudo que o WordPress faz com o texto (prioridade 20), porque
 * o último a mexer no conteúdo deve ser quem desenha o ícone.
 */
function vc_render_icones( string $html ): string {
	return (string) preg_replace_callback(
		'/<span class="ico" data-ico="([a-z0-9-]+)"><\/span>/',
		static fn( array $achado ): string => vc_icone( $achado[1], 20 ),
		$html
	);
}
add_filter( 'the_content', 'vc_render_icones', 20 );

/* -------------------------------------------------------------------------
   O MARCADOR DE LINK EXTERNO
   ------------------------------------------------------------------------- */
/**
 * Marca, no INÍCIO, os links que levam para fora do site.
 *
 * POR QUE NO INÍCIO. A leitura de um link vai da esquerda para a direita, e a
 * decisão de clicar vem junto: saber ANTES que o destino é outro site é o que
 * evita a surpresa de sair sem querer. No fim do link, o aviso chega depois de
 * a decisão já ter sido tomada.
 *
 * O ícone sai `aria-hidden` (o `vc_icone()` já o marca assim quando não recebe
 * rótulo), porque a informação útil para quem não vê a tela está no texto
 * escondido "(link externo)". Anunciar os dois faria o leitor de tela dizer a
 * mesma coisa duas vezes.
 *
 * O que NÃO recebe a marca: link relativo, âncora, `mailto:`, `tel:`, o próprio
 * domínio (com ou sem `www`) e link cujo conteúdo é só uma imagem — nesse caso o
 * marcador apareceria antes da figura, como um enfeite solto.
 *
 * Roda na prioridade 30, DEPOIS de `vc_render_icones()` (20): os ícones do
 * conteúdo importado precisam já estar no lugar, senão a marca entraria no meio
 * de uma troca que ainda ia acontecer.
 *
 * @param string $html O conteúdo já filtrado pelo WordPress.
 */
function vc_marcar_links_externos( string $html ): string {
	if ( '' === $html || false === stripos( $html, '<a ' ) ) {
		return $html;
	}

	$host_do_site = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	$host_do_site = (string) preg_replace( '/^www\./', '', $host_do_site );

	return (string) preg_replace_callback(
		'/<a\b([^>]*)>(.*?)<\/a>/is',
		static function ( array $achado ) use ( $host_do_site ): string {
			$atributos = $achado[1];
			$dentro    = $achado[2];

			if ( ! preg_match( '/\bhref\s*=\s*["\']([^"\']+)["\']/i', $atributos, $alvo ) ) {
				return $achado[0];
			}

			$endereco = html_entity_decode( $alvo[1], ENT_QUOTES, 'UTF-8' );

			/* Só http(s) absoluto interessa; o resto é interno por definição. */
			if ( 0 !== stripos( $endereco, 'http://' ) && 0 !== stripos( $endereco, 'https://' ) ) {
				return $achado[0];
			}

			$host = strtolower( (string) wp_parse_url( $endereco, PHP_URL_HOST ) );
			$host = (string) preg_replace( '/^www\./', '', $host );

			if ( '' === $host || $host === $host_do_site ) {
				return $achado[0];
			}

			/* Já marcado: o mesmo conteúdo pode passar por este filtro mais de uma vez. */
			if ( false !== strpos( $dentro, 'link-externo__icone' ) ) {
				return $achado[0];
			}

			/* Link que é só imagem: o marcador ficaria solto ao lado dela. */
			if ( preg_match( '/^\s*<img\b/i', $dentro ) ) {
				return $achado[0];
			}

			// Adiciona a classe link-externo à tag âncora
			if ( preg_match( '/\bclass=["\']([^"\']*)["\']/i', $atributos, $m_class ) ) {
				$nova_classe = trim( $m_class[1] . ' link-externo' );
				$atributos   = preg_replace( '/\bclass=["\'][^"\']*["\']/i', 'class="' . esc_attr( $nova_classe ) . '"', $atributos );
			} else {
				$atributos .= ' class="link-externo"';
			}

			return '<a' . $atributos . '>'
				. '<span class="link-externo__texto">' . $dentro . '</span>'
				. vc_icone( 'external-link', 14, 'link-externo__icone' )
				. '<span class="visually-hidden">(link externo)</span>'
				. '</a>';
		},
		$html
	);
}
add_filter( 'the_content', 'vc_marcar_links_externos', 30 );

/**
 * Remove parágrafos residuais de "iniciado em ..." em páginas de livros.
 *
 * @param string $content Conteúdo HTML da página.
 * @return string Conteúdo limpo sem a menção de "iniciado em".
 */
function vc_remover_iniciado_em_livros( $content ) {
	if ( ! is_admin() && ( is_page( 'livros' ) || is_singular( 'livro' ) ) ) {
		$content = preg_replace( '/<p\b[^>]*>\s*iniciado em\b.*?<\/p>/iu', '', $content );
	}
	return $content;
}
add_filter( 'the_content', 'vc_remover_iniciado_em_livros', 12 );

/* -------------------------------------------------------------------------
   O LIGHTBOX DAS IMAGENS
   ------------------------------------------------------------------------- */
/**
 * Clicar numa imagem do texto abre ela grande — com o lightbox do núcleo.
 *
 * O QUE O NÚCLEO JÁ FAZ SOZINHO. Desde o 6.4 o bloco de imagem tem a opção
 * "ampliar ao clicar". Quando ela está ligada, e a imagem NÃO é link, o núcleo
 * enfileira o módulo de script `@wordpress/block-library/image/view`, procura a
 * figura já renderizada e a envolve no `wp-lightbox-container`: botão de lupa,
 * overlay, navegação entre as imagens da página, Esc para fechar e foco preso
 * enquanto está aberto. A decisão sai de `block_core_image_get_lightbox_settings()`,
 * que lê `attrs.lightbox` do bloco.
 *
 * ENTÃO AQUI NÃO SE ESCREVE LIGHTBOX NENHUM — escreve-se a CHAVE. Este filtro
 * liga a opção em toda imagem do conteúdo, para o recurso não depender de alguém
 * lembrar de marcá-la bloco a bloco em cada texto publicado.
 *
 * RODA EM `render_block_data`, E NÃO EM `render_block`. O núcleo decide na
 * prioridade 15 do `render_block`; `render_block_data` é aplicado antes, quando
 * o bloco ainda é dado (`class-wp-block.php`, linha 614). Filtrar o HTML pronto
 * seria tarde e mais frágil: o atributo é a entrada, o HTML é a saída.
 *
 * QUEM FICA DE FORA, e é de propósito:
 *  - imagem que é LINK: o núcleo exige `linkDestination: none` para agir, e
 *    atropelar um link que o autor escolheu trocaria a função do clique;
 *  - imagem cujo bloco diz `lightbox.enabled: false`: quem desligar à mão
 *    continua desligado, porque a opção do editor manda mais que o padrão.
 *
 * A FOLHA `assets/lightbox.css` só entra na página em que este filtro encontra
 * uma imagem dessas — é o CSS do lightbox copiado do núcleo, e o cabeçalho do
 * arquivo conta por que ele veio para cá em vez de a folha do núcleo entrar.
 */
function vc_lightbox_das_imagens( array $bloco ): array {
	if ( 'core/image' !== ( $bloco['blockName'] ?? '' ) ) {
		return $bloco;
	}

	if ( 'none' !== ( $bloco['attrs']['linkDestination'] ?? 'none' ) ) {
		return $bloco;
	}

	if ( isset( $bloco['attrs']['lightbox']['enabled'] ) && false === $bloco['attrs']['lightbox']['enabled'] ) {
		return $bloco;
	}

	$bloco['attrs']['lightbox'] = array( 'enabled' => true );

	wp_enqueue_style( 'vc-lightbox', get_theme_file_uri( 'assets/lightbox.css' ), array( 'vc-global' ), vc_asset_ver( 'assets/lightbox.css' ) );

	return $bloco;
}
add_filter( 'render_block_data', 'vc_lightbox_das_imagens' );

/* -------------------------------------------------------------------------
   O SUMÁRIO DO ARTIGO
   ------------------------------------------------------------------------- */
/**
 * Índice das seções, montado a partir dos `<h2>` do próprio texto.
 *
 * REGRA DE ATIVAÇÃO: menos de três `<h2>` e o sumário NÃO é impresso. Um índice
 * para duas seções é uma lista que o olho percorre mais rápido do que o dedo
 * clica; ele só ocuparia espaço entre a abertura e o texto.
 *
 * Medido antes de escrever isto: das 17 notas publicadas, 16 têm ZERO `<h2>`
 * (são textos curtos, de 125 a 370 palavras, e alguns usam `<h3>`), e só a nota
 * longa — 2.403 palavras — tem os 16 `<h2>` que ativam o sumário. Ou seja: hoje
 * ele aparece numa nota. Isso é o esperado, não um defeito — o sumário existe
 * para texto longo, e é onde o texto é longo que ele serve.
 *
 * ONDE ELE ENTRA: logo ANTES do primeiro `<h2>`, isto é, depois da abertura. A
 * outra posição possível era antes do primeiro parágrafo, e ela foi descartada
 * por MEDIDA, não por gosto: com 16 seções o índice tem cerca de 400px de altura
 * e, no topo, empurraria a abertura — o gancho do texto — para baixo de uma caixa
 * grande. Entre a abertura e a primeira seção, o leitor já leu por que está ali,
 * e o índice vira mapa em vez de obstáculo.
 *
 * ÂNCORAS: os `<h2>` deste site já vêm com `id` (medido: 16 de 16 na nota
 * longa), então o normal é REAPROVEITAR o que existe. Quando falta, o `id` nasce
 * do texto do título, com sufixo numérico em caso de repetição. Vale saber que os
 * `id` existentes têm acento ("a-cabeça-com-abas-demais"): é HTML válido e o
 * navegador resolve o `#` sozinho, sem precisar de escape.
 *
 * SEM JAVASCRIPT. É link de âncora, o mecanismo mais antigo e mais confiável que
 * existe para pular dentro de uma página: funciona sem script, com leitor de
 * tela, no botão do meio do mouse e antes de a página terminar de carregar.
 *
 * Roda na prioridade 40, depois dos ícones (20) e do marcador de link externo
 * (30): o rótulo de cada item é o texto do título já sem marcação, e a marca de
 * link externo dentro de um `<h2>` não pode acabar dentro do índice.
 */
function vc_sumario_do_artigo( string $html ): string {
	/*
	 * Só na nota, no laço principal, e depois de o texto estar montado. Sem esta
	 * guarda o filtro também rodaria no resumo, na listagem e no feed, onde um
	 * índice de seções não faz sentido nenhum.
	 */
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $html;
	}

	/* A contagem é do próprio casamento, e não de um `strpos()` por `<h2`: a
	   string solta acharia também uma amostra de código que falasse de HTML. */
	if ( preg_match_all( '/<h2\b/i', $html ) < 3 ) {
		return $html;
	}

	$usadas = array();
	$itens  = array();

	$conteudo = (string) preg_replace_callback(
		'/<h2\b([^>]*)>(.*?)<\/h2>/is',
		static function ( array $achado ) use ( &$usadas, &$itens ): string {
			$atributos = $achado[1];
			$rotulo    = trim( wp_strip_all_tags( $achado[2] ) );

			if ( preg_match( '/\bid\s*=\s*["\']([^"\']+)["\']/i', $atributos, $ja_tem ) ) {
				$ancora = $ja_tem[1];
			} else {
				$ancora     = vc_ancora_unica( $rotulo, $usadas );
				$atributos .= ' id="' . esc_attr( $ancora ) . '"';
			}

			$usadas[ $ancora ] = true;
			$itens[]           = array( $ancora, $rotulo );

			return '<h2' . $atributos . '>' . $achado[2] . '</h2>';
		},
		$html
	);

	$lista = '';
	foreach ( $itens as $item ) {
		$lista .= '<li><a href="#' . esc_attr( $item[0] ) . '">' . esc_html( $item[1] ) . '</a></li>';
	}

	$indice = '<nav class="toc-container" aria-label="Sumário do artigo">'
		. '<p class="toc-container__rotulo">Sumário</p>'
		. '<ol class="toc-container__lista">' . $lista . '</ol>'
		. '</nav>';

	if ( ! preg_match( '/<h2\b/i', $conteudo, $primeiro, PREG_OFFSET_CAPTURE ) ) {
		return $conteudo;
	}

	$corte = (int) $primeiro[0][1];

	return substr( $conteudo, 0, $corte ) . $indice . substr( $conteudo, $corte );
}
// // add_filter( 'the_content', 'vc_sumario_do_artigo', 40 ); // Desativado a pedido do usuario

/**
 * Âncora de URL a partir do texto de um título, sem repetir as já usadas.
 *
 * `sanitize_title()` faz o trabalho de verdade: tira acento, tira pontuação e
 * junta por hífen. O que falta nele é a unicidade, e é só isso que esta função
 * acrescenta — dois `<h2>` com o mesmo texto ("o que eu aprendi") precisam de
 * âncoras diferentes, senão o segundo item do sumário levaria ao primeiro.
 *
 * @param array<string,bool> $usadas Âncoras já usadas nesta página.
 */
function vc_ancora_unica( string $texto, array $usadas ): string {
	$base   = sanitize_title( $texto );
	$base   = '' === $base ? 'secao' : $base;
	$slug   = $base;
	$indice = 2;

	while ( isset( $usadas[ $slug ] ) ) {
		$slug = $base . '-' . $indice;
		$indice++;
	}

	return $slug;
}

/**
 * Tira o texto do marcador dos RESUMOS.
 *
 * Medido depois de publicar: a marca aparecia em `<meta name="description">`,
 * `og:description` e `twitter:description` como "Joe Reis e Matt Housley (link
 * externo)Fundamentos de Engenharia de Dados". A causa e o caminho do resumo:
 * `vc_descricao()` chama `get_the_excerpt()`, que aplica os filtros de
 * `the_content` (e portanto injeta o marcador) e so DEPOIS remove as tags — o
 * icone sai, o texto escondido fica, e ainda cola na palavra seguinte.
 *
 * A correcao e aqui e nao dentro de `vc_descricao()` de proposito: qualquer
 * outro consumidor de resumo (o feed, uma listagem) sofreria do mesmo vazamento,
 * e o resumo e um texto de leitura, nao o link. O aviso de link externo vale no
 * lugar onde se clica, e nao num paragrafo que so descreve a pagina.
 */
add_filter(
	'get_the_excerpt',
	static function ( string $trecho ): string {
		return str_replace( '(link externo)', '', $trecho );
	},
	20
);

/*
 * O site é escrito em português, e o `lang` do documento dizia "en-US" porque a
 * instalação está configurada em inglês. Isso engana leitor de tela — que passa
 * a aplicar regras de pronúncia de outro idioma — e enfraquece a página para
 * buscadores, que usam o atributo para decidir a que público ela serve.
 */
add_filter(
	'language_attributes',
	static function ( string $saida ): string {
		return (string) preg_replace( '/lang="[^"]*"/', 'lang="pt-BR"', $saida, 1 );
	}
);

/* -------------------------------------------------------------------------
   NOTAS RELACIONADAS
   ------------------------------------------------------------------------- */
/**
 * Os textos mais próximos deste, por tema em comum.
 *
 * O critério é o mesmo do site, mas a conta é mais simples: lá cada nota é
 * pontuada por vários sinais (temas, palavras em comum, links entre notas)
 * e as três melhores entram. Aqui a única pergunta é quantos temas os dois
 * textos dividem — quem divide dois vem antes de quem divide um, e empate
 * desempata pela data mais recente.
 *
 * A diferença é de propósito: uma consulta com `tax_query` é previsível e
 * barata, e a lista continua fazendo sentido. Inventar uma pontuação parecida
 * com a do site daria um resultado quase igual com muito mais código para
 * manter — e "quase igual" não vale o preço.
 *
 * @return array<int, array{post: WP_Post, comuns: int, tema: string}>
 */
function vc_relacionadas( int $post_id, int $limite = 3 ): array {
	$ids = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'ids' ) );

	if ( is_wp_error( $ids ) || empty( $ids ) ) {
		return array();
	}

	$candidatos = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 30,
			'post__not_in'   => array( $post_id ),
			'no_found_rows'  => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => $ids,
				),
			),
		)
	);

	$pontuados = array();

	foreach ( $candidatos as $candidato ) {
		$termos = wp_get_post_terms( $candidato->ID, 'category' );

		if ( is_wp_error( $termos ) ) {
			continue;
		}

		$comuns = array();
		foreach ( $termos as $termo ) {
			if ( in_array( $termo->term_id, $ids, true ) ) {
				$comuns[] = $termo->name;
			}
		}

		if ( empty( $comuns ) ) {
			continue;
		}

		$pontuados[] = array(
			'post'   => $candidato,
			'comuns' => count( $comuns ),
			// O motivo que aparece na lista é o primeiro tema dividido — o mesmo
			// texto que o site usa na linha "no mesmo tema: ...".
			'tema'   => $comuns[0],
		);
	}

	usort(
		$pontuados,
		static function ( array $a, array $b ): int {
			if ( $a['comuns'] !== $b['comuns'] ) {
				return $b['comuns'] <=> $a['comuns'];
			}
			return strtotime( $b['post']->post_date ) <=> strtotime( $a['post']->post_date );
		}
	);

	return array_slice( $pontuados, 0, $limite );
}

/* -------------------------------------------------------------------------
   MODELOS DE PÁGINA EM SUBPASTA
   ------------------------------------------------------------------------- */
/*
 * O WordPress procura modelos de página na raiz do tema, e não dentro de
 * pastas. Como as páginas que dependem de consulta moram em `page-templates/`,
 * elas precisam ser declaradas — sem isto o painel não oferece a opção, e a
 * página cairia no `page.php` sem ninguém entender por quê.
 */
add_filter(
	'theme_page_templates',
	static function ( array $modelos ): array {
		$modelos['page-templates/lista-de-posts.php'] = 'Lista de todos os textos';
		return $modelos;
	}
);

/* -------------------------------------------------------------------------
   QUANTAS NOTAS A HOME MOSTRA
   ------------------------------------------------------------------------- */
/*
 * Oito, como no site.
 *
 * O número não é escolha nova: está no código do site (`posts.slice(0, 8)`). A
 * home é a porta de entrada, e o caminho para o resto é o link "ver todos os
 * posts" — que só faz sentido porque a lista termina em algum lugar.
 *
 * Sem isto, a home listava as vinte e quatro notas e virava uma segunda página
 * `/posts/`, com um link no fim convidando a ver... a mesma lista. Medido: 61 KB
 * de HTML contra 41 KB do site, quase toda a diferença em linhas repetidas.
 *
 * Isto mexe SÓ na consulta principal da home: o arquivo de um tema e a página
 * `/posts/` têm consulta própria e não passam por aqui, e o painel fica de fora
 * pelo `is_admin()` — no painel, "oito por página" seria uma lista pela metade
 * que ninguém pediu.
 */
add_action(
	'pre_get_posts',
	static function ( $consulta ) {
		if ( is_admin() || ! $consulta->is_main_query() || ! $consulta->is_home() ) {
			return;
		}

		$consulta->set( 'post_type', array( 'post', defined( 'VC_NOTA' ) ? VC_NOTA : 'nota' ) );
		$consulta->set( 'posts_per_page', 12 );
	}
);

/* -------------------------------------------------------------------------
   O QUE O WORDPRESS CARREGA E AQUI NÃO É USADO
   ------------------------------------------------------------------------- */
/*
 * O WordPress é um sistema de blocos, e carrega o CSS dos blocos, os presets de
 * cor e tipografia dele e o CSS do editor clássico em TODA página — mesmo num
 * tema que não usa bloco nenhum. Medido nesta instalação: cinco folhas, com 107
 * ocorrências de variáveis de preset no HTML da home.
 *
 * Não é questão de gosto. Esse CSS traz `--wp--preset--*` e regras `.wp-block-*`
 * que competem em especificidade com o CSS do site: o tamanho de um `h1` passaria
 * a depender de qual folha o navegador aplica por último, e o desenho oscilaria
 * entre páginas conforme o conteúdo de cada uma. Era o mesmo problema que o
 * painel do Sveltia tinha.
 *
 * `wp_dequeue_style()` remove a folha e também o CSS embutido pendurado nela (a
 * parte que aparece no HTML como `-inline-css`), porque o WordPress só imprime o
 * que está na fila. Só que desenfileirar, aqui, NÃO BASTOU: medido no HTML da
 * home, `global-styles` continuava saindo depois de o desenfileiramento rodar na
 * prioridade 20, e o diagnóstico dos ganchos mostrou por quê — o núcleo registra
 * esse estilo por mais de um caminho, e tirar da fila não impede o segundo.
 *
 * O CAMINHO ATÉ AQUI FOI MEDIDO, porque o óbvio não funcionou. Desenfileirar na
 * prioridade 20 removeu quatro das cinco folhas — e o bloco com os presets de cor
 * continuou saindo no HTML. O diagnóstico dos ganchos mostrou por quê, e a
 * resposta estava no lugar menos provável:
 *
 *     == wp_footer
 *        prioridade 1  wp_enqueue_global_styles
 *        prioridade 1  wp_enqueue_stored_styles
 *
 * O núcleo registra esses dois TAMBÉM no rodapé. Limpar o `<head>` deixava o
 * rodapé enfileirar de novo, e o CSS voltava. Então a remoção precisa acontecer
 * nos dois ganchos, e antes que o rodapé chegue — por isso ela fica aqui, no
 * carregamento do arquivo, e não dentro de um retorno de chamada.
 *
 * Nada disso toca o EDITOR: o painel usa os ganchos `enqueue_block_*`, que não
 * estão sendo alterados. O que sai é só o que o visitante recebia sem uso.
 */
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles_custom_css' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_stored_styles' );
remove_action( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_block_template_skip_link' );
remove_action( 'wp_enqueue_scripts', array( 'WP_Duotone', 'output_block_styles' ), 9 );
remove_action( 'wp_enqueue_scripts', array( 'WP_Duotone', 'output_global_styles' ), 11 );

/*
 * A folha do bloco de galeria, que o núcleo enfileira SOZINHA quando há uma
 * galeria na página — e que, por isso, escapou da limpeza acima.
 *
 * Ela não é inofensiva aqui: traz `max-width: 100% !important` na imagem e
 * largura em `calc(50% - ...)` na figura, tudo dimensionado para o layout do
 * `.is-layout-flex` do núcleo, que este tema não carrega. Medido: as imagens
 * saíam com 46px dentro de uma grade de três colunas de 211px.
 *
 * As regras da galeria do site estão no `global.css`, junto das outras regras
 * de bloco. São duas prioridades porque o núcleo enfileira a folha em dois
 * momentos: junto das demais, antes da impressão do `<head>`, e durante a
 * renderização do bloco, quando o que entra tarde é impresso no rodapé.
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_dequeue_style( 'wp-block-gallery' );
	},
	20
);

add_action(
	'wp_footer',
	static function () {
		wp_dequeue_style( 'wp-block-gallery' );
	},
	1
);

remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
remove_action( 'wp_footer', 'wp_enqueue_stored_styles', 1 );

remove_action( 'wp_head', 'wp_enqueue_img_auto_sizes_contain_css_fix', 0 );
remove_action( 'wp_head', 'wp_print_auto_sizes_contain_css_fix', 1 );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );

add_action(
	'wp_enqueue_scripts',
	static function () {
		foreach (
			array(
				'global-styles',             // presets de cor, tipografia e espaçamento
				'core-block-supports',       // regras geradas pelas opções de cada bloco
				'wp-block-library',          // CSS dos blocos de conteúdo
				'wp-block-library-theme',    // ajustes dos blocos dentro do tema
				'classic-theme-styles',      // CSS do editor clássico
				'wp-img-auto-sizes-contain', // correção de tamanho de imagem
				'wp-emoji-styles',           // folha dos emojis
			) as $folha ) {
			wp_dequeue_style( $folha );
		}

		/*
		 * Os emojis não são só uma folha: vêm com um script que busca um sprite em
		 * `s.w.org` e com um `dns-prefetch` apontando para lá. O site não usa emoji
		 * do motor do WordPress, e o visitante não deve abrir conexão com terceiros
		 * por causa de um recurso que ninguém pediu.
		 *
		 * Os filtros de feed e de e-mail saem junto: eles trocam emoji por imagem
		 * do mesmo sprite, e o site original publicava emoji como texto.
		 */
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );
		add_filter(
			'wp_resource_hints',
			static function ( $enderecos, $relacao ) {
				if ( 'dns-prefetch' !== $relacao ) {
					return $enderecos;
				}

				return array_filter(
					$enderecos,
					static function ( $endereco ) {
						return false === strpos( (string) $endereco, 's.w.org' );
					}
				);
			},
			10,
			2
		);
	},
	20
);

/* -------------------------------------------------------------------------
   CABEÇA DAS PÁGINAS: CANÔNICO, TÍTULO E DESCRIÇÃO
   ------------------------------------------------------------------------- */
/*
 * O núcleo só imprime `rel=canonical` em páginas singulares — a home e os
 * arquivos de tema ficavam sem nenhum, medido. A etiqueta do núcleo sai e o
 * `header.php` escreve a sua para toda página, com a mesma URL que o sitemap
 * anuncia.
 */
remove_action( 'wp_head', 'rel_canonical' );

/*
 * `feed_links_extra` anuncia um feed por arquivo (`/tema/marketing/feed/`) e
 * um por texto (`/nome-do-texto/feed/`). O site tem UM feed, em `/feed.xml`, e
 * quem assina não precisa escolher entre três endereços que entregam a mesma
 * coisa. O anúncio do feed único é escrito à mão no `header.php`.
 */
remove_action( 'wp_head', 'feed_links_extra', 3 );

/**
 * O endereço canônico da página que está sendo servida.
 */
function vc_canonical(): string {
	$url = '';

	if ( is_singular() ) {
		$url = (string) get_permalink( (int) get_queried_object_id() );
	} elseif ( is_front_page() || is_home() ) {
		$url = home_url( '/' );
	} elseif ( is_post_type_archive() ) {
		/*
		 * O ARQUIVO DE UM TIPO DE CONTEÚDO — o caso de `/videos/`.
		 *
		 * Este ramo nasceu de uma medição no ar, e não de uma suspeita: sem ele o
		 * endereço não casava com nenhum dos casos acima, a variável saía vazia e
		 * o `if` do fim a mandava para a home. O resultado é que a página criada
		 * para ser indexada avisava ao buscador que ela era DUPLICATA DA HOME —
		 * exatamente o contrário do que o hub existe para fazer.
		 *
		 * `get_queried_object()` devolve o objeto do tipo (`WP_Post_Type`), e não
		 * o nome solto: assim funciona igual para este tipo e para qualquer outro
		 * que venha depois, sem depender de `get_query_var()`.
		 */
		$tipo = get_queried_object();
		$url  = ( $tipo instanceof WP_Post_Type ) ? (string) get_post_type_archive_link( $tipo->name ) : '';
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$termo = get_queried_object();
		$url   = ( $termo instanceof WP_Term ) ? (string) get_term_link( $termo ) : '';
	} elseif ( is_author() ) {
		$autor = get_queried_object();
		$url   = ( $autor instanceof WP_User ) ? (string) get_author_posts_url( $autor->ID ) : '';
	}

	if ( '' === $url || is_wp_error( $url ) ) {
		$url = home_url( '/' );
	}

	/* A página 2 de um arquivo aponta para si mesma, e não para a primeira. */
	if ( is_paged() ) {
		$paginada = get_pagenum_link( (int) get_query_var( 'paged' ) );

		if ( is_string( $paginada ) && '' !== $paginada ) {
			$url = $paginada;
		}
	}

	return $url;
}

/**
 * A descrição da página, para a meta tag e para as prévias de link.
 *
 * UM LUGAR SÓ, e é o ponto. Antes a conta morava no `header.php` e perguntava
 * `is_singular()` — e o `/posts/` é uma PÁGINA, então caía no ramo do singular,
 * não achava resumo nem conteúdo (o corpo dele é gerado por template) e saía sem
 * descrição nenhuma. Uma página que descreve o blog inteiro ficava sem a frase
 * que o Google mostra.
 */
function vc_descricao(): string {
	$padrao = get_bloginfo( 'description' ) ?: 'Tecnologia, dados e vida — um blog pessoal de Vinicius Oliveira.';
	$texto  = '';

	if ( is_front_page() ) {
		/*
		 * A home usa a descrição longa, e não a tagline: é a página que o Google
		 * mostra para quem procura pelo nome do blog, e ali cabe o que o blog
		 * cobre, não só o que ele é. É a mesma frase do site Astro.
		 */
		$texto = 'Tecnologia, dados e vida — blog pessoal de Vinicius Oliveira. Posts sobre tecnologia, AuDHD, leituras e vida em Curitiba.';
	} elseif ( is_singular() ) {
		$id = (int) get_queried_object_id();

		if ( is_page_template( 'page-templates/lista-de-posts.php' ) ) {
			$total = (int) wp_count_posts( 'post' )->publish;
			/* translators: %d: número de textos publicados. */
			$texto = sprintf( 'Os %d posts do blog pessoal de Vinicius Oliveira, do mais recente para o mais antigo.', $total );
		} else {
			$texto = (string) get_the_excerpt( $id );

			if ( '' === trim( $texto ) ) {
				$texto = wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $id ) ), 30 );
			}
		}
	} elseif ( is_category() ) {
		$termo = get_queried_object();

		if ( $termo instanceof WP_Term ) {
			/* translators: %s: nome do tema. */
			$texto = sprintf( 'Tema %s do blog pessoal de Vinicius Oliveira.', $termo->name );
		}
	}

	$texto = trim( (string) preg_replace( '/\s+/', ' ', wp_strip_all_tags( $texto ) ) );

	if ( '' === $texto ) {
		$texto = $padrao;
	}

	return (string) wp_html_excerpt( $texto, 158, '…' );
}

/*
 * O título, igual ao do site: "Título · @viniciuscodes".
 *
 * O WordPress usava "Título – Vinicius Codes" no `<title>` enquanto o `og:title`
 * dizia "Título · @viniciuscodes" — duas versões do mesmo título em prévia de
 * link e em resultado de busca. Agora as duas saem daqui, com o mesmo separador
 * do site.
 *
 * A home é a exceção: lá o título é o do site, sem o handle repetido.
 */
add_filter( 'document_title_separator', static fn(): string => '·' );

add_filter(
	'document_title_parts',
	static function ( array $partes ): array {
		if ( is_front_page() || is_home() ) {
			return array( 'title' => '@viniciuscodes' );
		}

		$partes['site'] = '@viniciuscodes';

		return $partes;
	}
);

/* -------------------------------------------------------------------------
   SEM AGENDAMENTO: SÓ RASCUNHO E PUBLICAR
   ------------------------------------------------------------------------- */
/*
 * O AGENDAMENTO FOI REMOVIDO. Um texto aqui é rascunho ou é publicado; não
 * existe o terceiro estado, "fica para depois", que o WordPress chama de
 * `future`.
 *
 * POR QUE A TRAVA É NO SERVIDOR, E NÃO SÓ NO PAINEL. O botão do editor é a
 * parte visível, e não é a única porta: agendar também entra pela edição rápida,
 * pela edição em massa, pela API REST — que é como o próprio editor de blocos
 * fala com o site — e por qualquer script futuro. O filtro `wp_insert_post_data`
 * fecha todas de uma vez, porque toda gravação de post passa por ali. E passa no
 * momento certo: o `wp_insert_post()` já converteu "publicar com data no futuro"
 * em `future` ANTES de chamar o filtro, então é exatamente aqui que dá para
 * desfazer a conversão.
 *
 * A DATA VAI JUNTO, e não só o status. Publicar e deixar 01/01/2027 escrito no
 * texto manteria no ar uma nota com data no futuro: o leitor veria uma data
 * impossível e a listagem sairia fora de ordem. Quando a data pedida está à
 * frente do relógio, ela vira "agora" — que é o que a pessoa quis dizer ao
 * clicar em publicar.
 *
 * A DATA CONTINUA EDITÁVEL PARA TRÁS, de propósito. Carimbar sempre a hora atual
 * apagaria a possibilidade de datar um texto no dia em que ele foi escrito, que
 * é justamente o que o autor faz. O que não existe mais é o futuro.
 */
add_filter( 'wp_insert_post_data', 'vc_sem_agendamento', 10, 2 );

/**
 * Transforma agendamento em publicação imediata.
 *
 * O filtro entrega também os dados originais da gravação; não são usados aqui,
 * porque o que interessa é o que vai ser gravado.
 *
 * @param array<string, mixed> $dados Dados já limpos que serão gravados.
 * @return array<string, mixed>
 */
function vc_sem_agendamento( array $dados ): array {
	if ( 'future' !== ( $dados['post_status'] ?? '' ) ) {
		return $dados;
	}

	/*
	 * Um segundo atrás, e não "agora". O `wp_insert_post()` guardou o próprio
	 * "agora" um instante antes de chamar este filtro, e é com aquele valor que
	 * ele decide, depois, se o post deve voltar a ser agendado. Devolvendo o
	 * mesmo instante, uma virada de segundo no meio do caminho bastaria para o
	 * texto voltar para o futuro e a trava inteira se perder.
	 */
	$agora = gmdate( 'Y-m-d H:i:s', (int) current_time( 'timestamp' ) - 1 );

	$dados['post_status']   = 'publish';
	$dados['post_date']     = $agora;
	$dados['post_date_gmt'] = get_gmt_from_date( $agora );

	/*
	 * Quem tentou agendar precisa saber. A mensagem é lida em `admin_notices`,
	 * depois do redirecionamento, então o recado tem de sobreviver à requisição
	 * — daí o transiente, curto e por usuário.
	 */
	set_transient( 'vc_agendamento_evitado_' . get_current_user_id(), 1, MINUTE_IN_SECONDS );

	return $dados;
}

/*
 * A ABA "AGENDADOS" SAI DA LISTA. Ela listaria vazio para sempre, e um filtro
 * que não leva a lugar nenhum é ruído — pior, anuncia um recurso que o site não
 * tem mais.
 */
add_filter(
	'views_edit-post',
	static function ( array $visoes ): array {
		unset( $visoes['future'] );

		return $visoes;
	}
);

/*
 * QUEM TENTOU AGENDAR FICA SABENDO, uma vez. Ler a própria data transformada em
 * "agora" sem nenhuma palavra parece defeito do site; com uma linha de aviso,
 * vira uma regra.
 */
add_action(
	'admin_notices',
	static function () {
		$chave = 'vc_agendamento_evitado_' . get_current_user_id();

		if ( ! get_transient( $chave ) ) {
			return;
		}

		delete_transient( $chave );

		echo '<div class="notice notice-info is-dismissible"><p>';
		echo esc_html( 'O agendamento está desligado neste site. O texto foi publicado agora, em vez de ficar marcado para depois.' );
		echo '</p></div>';
	}
);

/*
 * O QUE FALTAVA ERA O PRÓPRIO BOTÃO. Fechar o servidor impede GRAVAR agendado,
 * mas o editor continuaria trocando o rótulo de "Publicar" para "Agendar" assim
 * que alguém digitasse uma data à frente. O `assets/painel.js` devolve a data
 * para agora dentro do editor, então o rótulo nunca chega a mudar; e nas listas
 * ele tira a opção "Agendado" dos seletores de status.
 *
 * ESTE ARQUIVO NOVO NÃO TINHA CACHE PARA INVALIDAR, então não precisa de versão
 * própria. AO EDITAR O `painel.js`, subir o `VC_VERSAO`: sem isso o navegador do
 * autor continua rodando a cópia antiga e a correção parece não ter funcionado.
 */
add_action(
	'admin_enqueue_scripts',
	static function ( string $gancho ): void {
		if ( ! in_array( $gancho, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
			return;
		}

		/*
		 * `wp-data` só nas duas telas do editor, onde vive o estado do post. Na
		 * lista não há estado nenhum para observar — e declarar a dependência ali
		 * arrastaria o React inteiro para uma tela que só precisa remover uma
		 * `<option>`.
		 */
		$dependencias = in_array( $gancho, array( 'post.php', 'post-new.php' ), true )
			? array( 'wp-data' )
			: array();

		wp_enqueue_script( 'vc-painel', get_theme_file_uri( 'assets/painel.js' ), $dependencias, vc_asset_ver( 'assets/painel.js' ), true );
	}
);

/* -------------------------------------------------------------------------
   A MANIFESTAÇÃO DO APP
   ------------------------------------------------------------------------- */
/*
 * Servida em `/manifest.webmanifest` por um gancho, e não publicada como
 * arquivo: os dois ícones vivem na biblioteca de mídia, cujo endereço muda de
 * servidor para servidor. Um arquivo estático apontaria para o lugar errado
 * depois da publicação.
 *
 * Sem `add_rewrite_rule` de propósito: o caminho é comparado direto no
 * `template_redirect`, então não depende de as regras de reescrita terem sido
 * regravadas no servidor novo.
 */
add_action(
	'template_redirect',
	static function (): void {
		$caminho = (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );

		if ( '/manifest.webmanifest' !== $caminho ) {
			return;
		}

		$icones = array();

		foreach ( array( 'icon-192.png' => '192x192', 'icon-512.png' => '512x512' ) as $arquivo => $tamanho ) {
			$url = vc_midia( $arquivo );

			if ( '' !== $url ) {
				$icones[] = array(
					'src'     => $url,
					'sizes'   => $tamanho,
					'type'    => 'image/png',
					'purpose' => 'any',
				);
			}
		}

		$manifesto = array(
			'name'             => 'viniciuscodes',
			'short_name'       => 'viniciuscodes',
			'description'      => get_bloginfo( 'description' ) ?: 'Tecnologia, dados e vida — um blog pessoal de Vinicius Oliveira.',
			'start_url'        => '/',
			'scope'            => '/',
			'display'          => 'standalone',
			'lang'             => 'pt-BR',
			'dir'              => 'ltr',
			'background_color' => '#faf9f5',
			'theme_color'      => '#faf9f5',
			'icons'            => $icones,
		);

		status_header( 200 );
		header( 'Content-Type: application/manifest+json; charset=utf-8' );

		echo wp_json_encode( $manifesto );

		exit;
	},
	1
);



/* -------------------------------------------------------------------------
   PAINEL ADMINISTRATIVO MINIMALISTA (ESCRITA LIMPA)
   ------------------------------------------------------------------------- */

/**
 * Remove o ruído visual do painel inicial (dashboard) e adiciona um espaço sereno para escrita.
 */
function vc_custom_dashboard_clean() {
	global $wp_meta_boxes;

	// Remove todos os widgets residuais do WordPress core ou plugins
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' ); // Novidades do WordPress
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' ); // Rascunho rápido
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' ); // Atividade
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' ); // Agora
	remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' ); // Diagnóstico do site

	// Garante remoção de qualquer widget antigo de jardim digital
	remove_meta_box( 'garden_dashboard_widget', 'dashboard', 'normal' );
	remove_meta_box( 'vc_garden_dashboard', 'dashboard', 'normal' );

	wp_add_dashboard_widget(
		'vc_dashboard_zen',
		'escrever',
		'vc_render_dashboard_zen'
	);
}
add_action( 'wp_dashboard_setup', 'vc_custom_dashboard_clean', 999 );

function vc_render_dashboard_zen() {
	?>
	<div style="padding: 12px 6px;">
		<p style="font-size: 15px; color: #555; margin-bottom: 18px;">
			um espaço calmo para suas ideias e histórias.
		</p>
		<a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>" class="button button-primary button-hero" style="background: #2d5a3e; border-color: #2d5a3e; text-shadow: none; box-shadow: none; border-radius: 6px; text-transform: lowercase; font-weight: 500;">
			escrever novo post
		</a>
	</div>
	<?php
}

/**
 * Simplifica as colunas da listagem de posts (edit.php).
 * Mantém apenas título, temas (categorias) e data.
 *
 * @param array<string, string> $columns
 * @return array<string, string>
 */
add_filter(
	'manage_post_posts_columns',
	static function ( array $columns ): array {
		$clean_columns = array();

		if ( isset( $columns['cb'] ) ) {
			$clean_columns['cb'] = $columns['cb'];
		}

		$clean_columns['title']      = 'título';
		$clean_columns['categories'] = 'temas';
		$clean_columns['date']       = 'data';

		return $clean_columns;
	}
);

/**
 * Simplifica as colunas da tela de comentários (edit-comments.php),
 * removendo colunas técnicas de plugins (como Comment-Type e Protocol)
 * para dar espaço de leitura ao texto do comentário.
 *
 * @param array<string, string> $columns
 * @return array<string, string>
 */
add_filter(
	'manage_edit-comments_columns',
	static function ( array $columns ): array {
		unset( $columns['comment_type'], $columns['comment-type'], $columns['protocol'], $columns['response_protocol'] );

		// Também garante remoção caso o plugin use o rótulo exato "Comment-Type" ou "Protocol"
		foreach ( $columns as $key => $label ) {
			$clean_label = strtolower( trim( wp_strip_all_tags( (string) $label ) ) );
			if ( in_array( $clean_label, array( 'comment-type', 'protocol', 'tipo de comentário', 'protocolo' ), true ) ) {
				unset( $columns[ $key ] );
			}
		}

		return $columns;
	},
	999
);

/**
 * Agrupa comentários, respostas e tréplicas em fios de conversa (threads)
 * na tela de Comentários do WP Admin (edit-comments.php), para que o comentário
 * raiz venha primeiro e suas respostas/tréplicas apareçam logo abaixo, em ordem
 * cronológica da conversa, com recuo visual de nível.
 *
 * @param WP_Comment[] $comments
 * @return WP_Comment[]
 */
add_filter(
	'the_comments',
	static function ( array $comments, WP_Comment_Query $query ): array {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return $comments;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'edit-comments' !== $screen->id ) {
			return $comments;
		}

		// Não reordenar se for uma consulta apenas de contagem ou IDs
		if ( ! empty( $query->query_vars['count'] ) || 'ids' === $query->query_vars['fields'] ) {
			return $comments;
		}

		// Evita loop infinito ao buscar pais/filhos faltantes na página atual
		static $reorganizando = false;
		if ( $reorganizando || empty( $comments ) ) {
			return $comments;
		}
		$reorganizando = true;

		// Mapa de comentários indexado por ID
		$by_id = array();
		foreach ( $comments as $c ) {
			if ( $c instanceof WP_Comment ) {
				$by_id[ (int) $c->comment_ID ] = $c;
			}
		}

		// 1. Garantir que se uma resposta está na página, o ancestral (pai/avô) também esteja
		foreach ( array_values( $by_id ) as $c ) {
			$parent_id = (int) $c->comment_parent;
			$guard     = 0;
			while ( $parent_id > 0 && $guard < 10 ) {
				if ( ! isset( $by_id[ $parent_id ] ) ) {
					$parent_obj = get_comment( $parent_id );
					if ( $parent_obj instanceof WP_Comment ) {
						$by_id[ $parent_id ] = $parent_obj;
						$parent_id           = (int) $parent_obj->comment_parent;
					} else {
						break;
					}
				} else {
					$parent_id = (int) $by_id[ $parent_id ]->comment_parent;
				}
				$guard++;
			}
		}

		// 2. Buscar também respostas/tréplicas filhas dos comentários presentes para não quebrar a conversa
		$post_ids = array();
		foreach ( $by_id as $c ) {
			$post_ids[ (int) $c->comment_post_ID ] = true;
		}
		if ( ! empty( $post_ids ) ) {
			$status_atual = isset( $_REQUEST['comment_status'] ) ? sanitize_key( wp_unslash( $_REQUEST['comment_status'] ) ) : 'all';
			if ( ! in_array( $status_atual, array( 'spam', 'trash' ), true ) ) {
				$extras = get_comments(
					array(
						'post__in' => array_keys( $post_ids ),
						'status'   => array( 'approve', 'hold' ),
						'orderby'  => 'comment_date_gmt',
						'order'    => 'ASC',
						'number'   => 200,
					)
				);
				foreach ( $extras as $ex ) {
					if ( $ex instanceof WP_Comment && ! isset( $by_id[ (int) $ex->comment_ID ] ) ) {
						// Só inclui se pertencer a alguma árvore já listada na página
						$pid   = (int) $ex->comment_parent;
						$check = 0;
						while ( $pid > 0 && $check < 10 ) {
							if ( isset( $by_id[ $pid ] ) ) {
								$by_id[ (int) $ex->comment_ID ] = $ex;
								break;
							}
							$p_obj = get_comment( $pid );
							$pid   = ( $p_obj instanceof WP_Comment ) ? (int) $p_obj->comment_parent : 0;
							$check++;
						}
					}
				}
			}
		}

		// 3. Construir árvore de pais -> filhos
		$children_map = array();
		$roots        = array();

		foreach ( $by_id as $id => $c ) {
			$pid = (int) $c->comment_parent;
			if ( $pid > 0 && isset( $by_id[ $pid ] ) && $pid !== $id ) {
				$children_map[ $pid ][] = $c;
			} else {
				$roots[] = $c;
			}
		}

		// Função auxiliar para descobrir a data mais recente dentro de uma conversa (thread)
		$get_thread_latest_time = static function ( WP_Comment $node ) use ( &$get_thread_latest_time, &$children_map ): int {
			$latest = strtotime( $node->comment_date_gmt ?: $node->comment_date ) ?: 0;
			$id     = (int) $node->comment_ID;
			if ( ! empty( $children_map[ $id ] ) ) {
				foreach ( $children_map[ $id ] as $child ) {
					$child_time = $get_thread_latest_time( $child );
					if ( $child_time > $latest ) {
						$latest = $child_time;
					}
				}
			}
			return $latest;
		};

		// Ordenar as conversas (raízes) pela atividade mais recente (descendente)
		usort(
			$roots,
			static function ( WP_Comment $a, WP_Comment $b ) use ( $get_thread_latest_time ): int {
				return $get_thread_latest_time( $b ) <=> $get_thread_latest_time( $a );
			}
		);

		// Ordenar respostas dentro de cada conversa em ordem cronológica (ascendente: pergunta -> resposta -> tréplica)
		foreach ( $children_map as $pid => &$list ) {
			usort(
				$list,
				static function ( WP_Comment $a, WP_Comment $b ): int {
					$ta = strtotime( $a->comment_date_gmt ?: $a->comment_date ) ?: 0;
					$tb = strtotime( $b->comment_date_gmt ?: $b->comment_date ) ?: 0;
					return $ta <=> $tb;
				}
			);
		}
		unset( $list );

		// 4. Achatar a árvore em lista sequencial guardando metadados de profundidade para o CSS/JS
		$ordered     = array();
		$thread_meta = array();

		$flatten = static function ( WP_Comment $node, int $depth, int $root_id ) use ( &$flatten, &$children_map, &$ordered, &$thread_meta ): void {
			$id       = (int) $node->comment_ID;
			$has_kids = ! empty( $children_map[ $id ] );

			$ordered[]           = $node;
			$thread_meta[ $id ] = array(
				'depth'    => min( $depth, 4 ),
				'root_id'  => $root_id,
				'has_kids' => $has_kids,
				'is_me'    => ( (int) $node->user_id > 0 && (int) $node->user_id === get_current_user_id() )
					|| ( stripos( (string) $node->comment_author_email, 'vinicius' ) !== false )
					|| ( strtolower( (string) $node->comment_author ) === 'viniciuscodes' ),
			);

			if ( $has_kids ) {
				foreach ( $children_map[ $id ] as $child ) {
					$flatten( $child, $depth + 1, $root_id );
				}
			}
		};

		foreach ( $roots as $root ) {
			$flatten( $root, 0, (int) $root->comment_ID );
		}

		$GLOBALS['vc_admin_comment_threads'] = $thread_meta;
		$reorganizando                       = false;

		return $ordered;
	},
	10,
	2
);

add_action(
	'admin_footer-edit-comments.php',
	static function () {
		$thread_meta = $GLOBALS['vc_admin_comment_threads'] ?? array();
		if ( empty( $thread_meta ) ) {
			return;
		}
		?>
		<script id="vc-admin-comments-threads-js">
		(function() {
			const meta = <?php echo wp_json_encode( $thread_meta ); ?>;
			Object.keys(meta).forEach(function(id) {
				const row = document.getElementById('comment-' + id);
				if (!row) return;

				const info = meta[id];
				if (info.depth === 0) {
					row.classList.add('vc-thread-root');
				} else {
					row.classList.add('vc-thread-reply', 'vc-depth-' + info.depth);
				}

				if (info.has_kids) {
					row.classList.add('vc-has-replies');
				}
				if (info.is_me) {
					row.classList.add('vc-comment-mine');
				}

				// Adiciona um selo claro de "↳ sua resposta" ou "↳ tréplica" no topo do comentário filho
				if (info.depth > 0) {
					const commentCell = row.querySelector('td.column-comment');
					if (commentCell && !commentCell.querySelector('.vc-thread-badge')) {
						const badge = document.createElement('div');
						badge.className = 'vc-thread-badge';
						if (info.is_me) {
							badge.textContent = info.depth === 1 ? '↳ sua resposta' : '↳ sua réplica';
						} else {
							badge.textContent = info.depth === 1 ? '↳ resposta' : '↳ tréplica';
						}

						// Se houver o parágrafo padrão "Em resposta a Fulano.", incorpora o nome no badge
						const submittedOn = commentCell.querySelector('.submitted-on');
						if (submittedOn) {
							const parentLink = submittedOn.querySelector('a');
							if (parentLink && parentLink.textContent) {
								badge.textContent += ' para ' + parentLink.textContent.trim();
								submittedOn.style.display = 'none';
							}
						}

						commentCell.insertBefore(badge, commentCell.firstChild);
					}
				}
			});
		})();
		</script>
		<?php
	}
);

/* -------------------------------------------------------------------------
   CONEXÕES HIPERTEXTUAIS AUTOMÁTICAS (LINKS CITADOS E BACKLINKS)
   ------------------------------------------------------------------------- */
require_once get_theme_file_path( 'inc/post-connections.php' );

/* -------------------------------------------------------------------------
   PRESERVAR ANIMAÇÃO DE ARQUIVOS GIF
   ------------------------------------------------------------------------- */
/**
 * Garante que imagens .gif no conteúdo usem o arquivo original (full),
 * pois os redimensionamentos intermediários do GD/ImageMagick do WordPress
 * descartam os frames da animação transformando o GIF numa imagem estática.
 */
add_filter(
	'the_content',
	static function ( string $content ): string {
		if ( false === stripos( $content, '.gif' ) ) {
			return $content;
		}

		// Substitui qualquer versão redimensionada (-[largura]x[altura].gif) pela URL do GIF original
		return preg_replace_callback(
			'/(<img\s+[^>]*?src=["\'])([^"\']+?)(?:-\d+x\d+)?(\.gif)(["\'][^>]*?>)/i',
			static function ( array $m ): string {
				$abertura  = $m[1];
				$base_url  = $m[2];
				$ext       = $m[3];
				$resto_tag = $m[4];

				$url_original = $base_url . $ext;

				// Remove o srcset para evitar que o navegador escolha uma miniatura estática
				$resto_tag = preg_replace( '/\s+srcset=["\'][^"\']*["\']/i', '', $resto_tag );
				$resto_tag = preg_replace( '/\s+sizes=["\'][^"\']*["\']/i', '', $resto_tag );

				return $abertura . $url_original . $resto_tag;
			},
			$content
		);
	},
	20
);

/**
 * Desativa a geração de srcset para imagens GIF na API de renderização do WordPress.
 */
add_filter(
	'wp_calculate_image_srcset',
	static function ( $sources, $size_array, $image_src ) {
		if ( is_string( $image_src ) && preg_match( '/\.gif(\?.*)?$/i', $image_src ) ) {
			return false;
		}
		return $sources;
	},
	10,
	3
);

/* -------------------------------------------------------------------------
   REDIRECIONAMENTOS DE ROTAS PODADAS (301 PERMANENTE)
   ------------------------------------------------------------------------- */
add_action(
	'template_redirect',
	static function () {
		$uri = (string) ( $_SERVER['REQUEST_URI'] ?? '' );

		// Redireciona categoria órfã /tema/54/ para /tema/weeknotes/
		if ( preg_match( '#^/tema/54(/.*)?$#i', $uri ) ) {
			wp_safe_redirect( home_url( '/tema/weeknotes/' ), 301 );
			exit;
		}

		// Redireciona notas, microblog ou vídeos para /posts/
		if ( preg_match( '#^/(notas|microblog|videos?)(/.*)?$#i', $uri ) ) {
			wp_safe_redirect( home_url( '/posts/' ), 301 );
			exit;
		}

		// Redireciona páginas de catálogo/acervo/changelog/manifesto extintas para /posts/
		if ( preg_match( '#^/(livros|filmes|acervo|changelog|manifesto)(/.*)?$#i', $uri ) ) {
			wp_safe_redirect( home_url( '/posts/' ), 301 );
			exit;
		}

		// Redireciona /canais/ ou /youtube/ para /blogroll/
		if ( preg_match( '#^/(canais|youtube)(/.*)?$#i', $uri ) ) {
			wp_safe_redirect( home_url( '/blogroll/' ), 301 );
			exit;
		}

		// Redireciona /musica/ para /
		if ( preg_match( '#^/musica(/.*)?$#i', $uri ) ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
);



add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('vc-substack-stage2', get_theme_file_uri('assets/substack-stage2.css'), array('vc-fontes'), vc_asset_ver( 'assets/substack-stage2.css' ));
}, 999);
