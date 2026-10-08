<?php
/**
 * Template Name: Biblioteca (livros, filmes e séries)
 *
 * A página `/acervo/`: tudo o que eu li e assisti, numa lista só.
 *
 * POR QUE AS QUATRO COISAS COUBERAM NUMA PÁGINA SÓ.
 *
 * Eram duas páginas com duas gramáticas. `/livros/` tinha 20 blocos de
 * imagem-e-texto escritos à mão, com capa, autor, nota, resumo e o link da
 * Amazon. `/cinema/` tinha 230 linhas de `Título | tipo | data`. Unificar não
 * podia custar nem o que a segunda não tinha nem o que a primeira tinha demais:
 * as capas, as notas e os links dos livros continuam aqui inteiros, e os filmes
 * ganharam o mesmo tratamento de capa.
 *
 * O QUE DECIDE TUDO É UMA LINHA POR OBRA, e não uma por sessão.
 *
 *     A Substância | filme | 12/10/2024
 *     O Mágico de Oz | teatro | 30/03/2025, 06/04/2025, 09/08/2025
 *     A Cabeça do Santo | livro | capa: a-cabeca-do-santo.jpg | autor: Socorro
 *       Acioli | nota: 3 | resumo: ... | amazon: https://... | status: lido
 *     Encanto | filme
 *
 * As duas primeiras posições são o título e o tipo. A terceira é a data — e é
 * ela que decide se a obra tem data ou se está na lista de vontades: não há uma
 * terceira possibilidade. Dali para a frente, cada campo é opcional e tem NOME
 * (`capa:`, `autor:`, `nota:`, `resumo:`, `amazon:`, `resenha:`, `status:`), em
 * vez de posição. A diferença importa: só os livros usam os campos do fim, e com
 * posição fixa quem quisesse acrescentar um autor a um filme teria de contar
 * barras verticais até acertar o lugar.
 *
 * MAIS DE UMA DATA NA MESMA OBRA É REASSISTIDO, e é assim que eu quero ver na
 * tela: uma capa só, com "6×" e todos os dias. Antes, "O Mágico de Oz" aparecia
 * sete vezes seguidas na lista, e sete linhas iguais não dizem o que aconteceu;
 * uma linha com seis datas diz.
 *
 * O `|` QUEBRA O CAMPO, e é a única regra que quem edita precisa lembrar: um
 * resumo não pode conter `|`. O separador foi escolhido porque nenhum título
 * deste acervo o usa e porque ele existe no teclado do celular.
 *
 * A ORDEM NO EDITOR NÃO IMPORTA, com uma exceção: os livros, que não têm data,
 * saem na ordem em que estão escritos — é a ordem de leitura, e é a única
 * informação de ordem que eles têm. Datas são reordenadas aqui.
 *
 * O QUE O TEMA JÁ TINHA é reaproveitado: `.section-title` para os títulos de
 * seção, `.prose` para a abertura, e os tokens de cor, de modo escuro e de
 * tipografia do `global.css`. O cartão, a grade e a capa-tipo são deste arquivo
 * e do `assets/acervo.css`.
 *
 * @package ViniciusCodes
 */

wp_enqueue_style(
	'vc-acervo',
	get_theme_file_uri( 'assets/acervo.css' ),
	array( 'vc-global' ),
	(string) filemtime( get_theme_file_path( 'assets/acervo.css' ) )
);

/*
 * OS TIPOS, E A ORDEM DELES NA PÁGINA.
 *
 * O rótulo é o que se lê na tela; a chave é o que se escreve no editor. `série`
 * com acento é o que está escrito na lista hoje, e continua valendo: a
 * normalização aceita as duas formas, e `remove_accents()` faz o resto.
 *
 * O `icone` é o nome do arquivo em `assets/icons/lucide/`, e existe por causa do
 * filtro: são quatro pílulas do mesmo tamanho, lado a lado, e o desenho deixa o
 * tipo ser reconhecido antes de a palavra ser lida. Um tipo sem ícone declarado
 * fica sem ícone — a pílula continua inteira, e nenhum nome é adivinhado.
 */
$vc_tipos = array(
	'livro'  => array( 'rotulo' => 'livros',  'singular' => 'livro',  'ordem' => 1, 'icone' => 'book' ),
	'filme'  => array( 'rotulo' => 'filmes',  'singular' => 'filme',  'ordem' => 2, 'icone' => 'film' ),
	'serie'  => array( 'rotulo' => 'séries',  'singular' => 'série',  'ordem' => 3, 'icone' => 'tv' ),
	'teatro' => array( 'rotulo' => 'teatro',  'singular' => 'teatro', 'ordem' => 4, 'icone' => 'drama' ),
);

$vc_normalizar_tipo = static function ( string $t ): string {
	$t = trim( remove_accents( mb_strtolower( $t, 'UTF-8' ) ) );

	switch ( $t ) {
		case 'filme':
		case 'filmes':
			return 'filme';
		case 'serie':
		case 'series':
			return 'serie';
		case 'teatro':
			return 'teatro';
		case 'livro':
		case 'livros':
			return 'livro';
	}

	/*
	 * Tipo desconhecido NÃO SOME. Se um dia entrar "documentário" ou "stand-up",
	 * ele vira uma seção própria com o nome que foi escrito — perder o item seria
	 * pior do que ter uma seção a mais, e adivinhar o tipo dele seria mentir.
	 */
	return $t;
};

/**
 * A chave que junta duas linhas na MESMA obra.
 *
 * Tira o que não identifica: caixa, acento, pontuação e os enfeites que o dono do
 * site usa para destacar peça de teatro (aspas), para marcar a sessão ("1ª Data")
 * e os emoji. Nada aqui decide o que é duplicata — isso foi feito uma vez, à mão,
 * na migração, e está no README. Isto é a rede de segurança para quando a mesma
 * obra for escrita de duas formas depois.
 */
$vc_chave = static function ( string $t ): string {
	$t = preg_replace( '/^assistir\s+/iu', '', trim( $t ) );
	$t = preg_replace( '/^[\s"\x{201C}\x{201D}]+/u', '', (string) $t );
	$t = preg_replace( '/[\s"\x{201C}\x{201D}]+$/u', '', (string) $t );
	$t = preg_replace( '/\s*\(\s*\d*\s*[\x{00AA}\x{00BA}aA]?\s*[Dd]ata\s*\d*\s*\)\s*/u', ' ', (string) $t );
	$t = preg_replace( '/[\x{2190}-\x{21FF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}\x{200D}]/u', '', (string) $t );
	$t = preg_replace( '/[\x{1F000}-\x{1FAFF}]/u', '', (string) $t );
	$t = remove_accents( mb_strtolower( (string) $t, 'UTF-8' ) );
	$t = preg_replace( '/[^\p{L}\p{N} ]+/u', ' ', $t );
	$t = preg_replace( '/\s+/u', ' ', (string) $t );

	return trim( (string) $t );
};

/**
 * Lê as linhas do conteúdo e devolve as obras, já agrupadas.
 *
 * Devolve uma lista de `array( titulo, tipo, datas, campos )`, onde `datas` é a
 * união de todas as datas escritas para aquela obra, em ordem, e `campos` guarda
 * os opcionais. A primeira linha escrita ganha o título e os campos; as outras
 * só contribuem com datas — é o caso de acrescentar uma revisão de um filme já
 * visto sem repetir capa e resumo.
 */
$vc_ler_obras = static function ( string $conteudo ) use ( $vc_normalizar_tipo, $vc_chave ) : array {
	if ( ! preg_match_all( '#<li\b[^>]*>(.*?)</li>#is', $conteudo, $vc_achados ) ) {
		return array();
	}

	$obras = array();

	foreach ( $vc_achados[1] as $vc_bruto ) {
		/*
		 * A decodificação vem ANTES do `esc_html` da saída: o editor grava `&` como
		 * `&amp;`, e sem desfazer isso um título com `&` apareceria escrito `&amp;`
		 * na tela.
		 */
		$vc_texto = html_entity_decode( wp_strip_all_tags( $vc_bruto ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$vc_texto = trim( preg_replace( '/\s+/u', ' ', $vc_texto ) );

		if ( '' === $vc_texto ) {
			continue;
		}

		$vc_partes = array_map( 'trim', explode( '|', $vc_texto ) );
		$vc_titulo = (string) array_shift( $vc_partes );

		if ( '' === $vc_titulo ) {
			continue;
		}

		/*
		 * O tipo é opcional e o campo seguinte pode ser um opcional com nome. O
		 * dois-pontos é o que separa os dois casos: `série` é tipo, `capa: x.jpg`
		 * é campo.
		 */
		$vc_tipo = '';
		if ( $vc_partes && false === strpos( $vc_partes[0], ':' ) ) {
			$vc_tipo = $vc_normalizar_tipo( (string) array_shift( $vc_partes ) );
		}

		$vc_datas = array();

		if ( $vc_partes && preg_match( '#^\s*\d{1,2}/\d{1,2}/\d{4}#', $vc_partes[0] ) ) {
			foreach ( explode( ',', (string) array_shift( $vc_partes ) ) as $vc_pedaco ) {
				if ( preg_match( '#^\s*(\d{1,2})/(\d{1,2})/(\d{4})\s*$#', $vc_pedaco, $vc_d ) ) {
					$vc_datas[] = sprintf( '%04d-%02d-%02d', (int) $vc_d[3], (int) $vc_d[2], (int) $vc_d[1] );
				}
			}
		}

		$vc_campos = array();

		foreach ( $vc_partes as $vc_parte ) {
			if ( false === strpos( $vc_parte, ':' ) ) {
				continue;
			}

			list( $vc_nome, $vc_valor ) = explode( ':', $vc_parte, 2 );

			$vc_nome  = trim( mb_strtolower( $vc_nome, 'UTF-8' ) );
			$vc_valor = trim( $vc_valor );

			if ( '' !== $vc_nome && '' !== $vc_valor && ! isset( $vc_campos[ $vc_nome ] ) ) {
				$vc_campos[ $vc_nome ] = $vc_valor;
			}
		}

		$vc_k = $vc_chave( $vc_titulo ) . '|' . $vc_tipo;

		if ( ! isset( $obras[ $vc_k ] ) ) {
			$obras[ $vc_k ] = array(
				'titulo' => $vc_titulo,
				'tipo'   => $vc_tipo,
				'datas'  => array(),
				'campos' => $vc_campos,
			);
		}

		foreach ( $vc_datas as $vc_dia ) {
			$obras[ $vc_k ]['datas'][ $vc_dia ] = true;
		}

		/*
		 * Um campo que só apareceu na segunda linha ainda vale: quem acrescentar
		 * uma revisão e depois colar o link da resenha não deve perder o link.
		 */
		foreach ( $vc_campos as $vc_nome => $vc_valor ) {
			if ( ! isset( $obras[ $vc_k ]['campos'][ $vc_nome ] ) ) {
				$obras[ $vc_k ]['campos'][ $vc_nome ] = $vc_valor;
			}
		}
	}

	$lista = array();

	foreach ( $obras as $vc_o ) {
		$vc_o['datas'] = array_keys( $vc_o['datas'] );
		sort( $vc_o['datas'] );
		$lista[] = $vc_o;
	}

	return $lista;
};

/*
 * A IMAGEM DE CAPA E A RESENHA, PROCURADAS UMA VEZ.
 *
 * Duas consultas por item — uma para achar o anexo pelo nome do arquivo e outra
 * para achar a resenha pelo caminho — seriam mais de quatrocentas consultas numa
 * página de 224 itens. Aqui as duas listas são montadas de uma vez, no começo, e
 * o resto é leitura de array. O mapa da mídia sai de `_wp_attached_file` porque
 * é lá que está o caminho real do arquivo: o nome sozinho não diz em que pasta
 * de ano e mês ele foi guardado.
 */
$vc_mapa_midia = static function (): array {
	static $vc_mapa = null;

	if ( null !== $vc_mapa ) {
		return $vc_mapa;
	}

	global $wpdb;

	$vc_mapa = array();

	$vc_linhas = $wpdb->get_results(
		"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file'"
	);

	foreach ( (array) $vc_linhas as $vc_l ) {
		$vc_base = strtolower( basename( (string) $vc_l->meta_value ) );

		$vc_mapa[ $vc_base ] = (int) $vc_l->post_id;

		$vc_sem_extensao = strtolower( (string) pathinfo( $vc_base, PATHINFO_FILENAME ) );

		if ( ! isset( $vc_mapa[ $vc_sem_extensao ] ) ) {
			$vc_mapa[ $vc_sem_extensao ] = (int) $vc_l->post_id;
		}
	}

	return $vc_mapa;
};

$vc_mapa_posts = static function (): array {
	static $vc_mapa = null;

	if ( null !== $vc_mapa ) {
		return $vc_mapa;
	}

	$vc_mapa = array();

	$vc_ids = get_posts(
		array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	foreach ( $vc_ids as $vc_id ) {
		$vc_mapa[ get_post_field( 'post_name', $vc_id ) ] = (int) $vc_id;
	}

	return $vc_mapa;
};

/**
 * O anexo da capa, a partir do que foi escrito no campo `capa:` — que pode ser o
 * nome do arquivo, o número do anexo ou um endereço inteiro.
 *
 * O CAMPO MANDA, E EXISTE PARA TODOS OS TIPOS. Sem campo, a capa só é procurada
 * pelo título quando a obra é um LIVRO. A restrição não é preciosismo: "O Mágico
 * de Oz" é um livro do L. Frank Baum e uma peça de teatro, e o acervo tem os
 * dois. O arquivo da capa do livro atendia pelo nome `o-magico-de-oz`, e o
 * acerto automático por título pendurou a capa do livro na peça — uma imagem
 * errada é pior do que imagem nenhuma, porque parece certa. Nos livros a
 * convenção se sustenta (toda capa que existe está lá, e o título é a chave
 * natural); em filme, série e peça, a capa entra quando o campo `capa:` disser
 * qual é.
 */
$vc_achar_capa = static function ( string $vc_titulo, string $vc_campo, string $vc_tipo ) use ( $vc_mapa_midia ) : int {
	$vc_mapa = $vc_mapa_midia();

	if ( '' !== $vc_campo ) {
		if ( ctype_digit( $vc_campo ) ) {
			return (int) $vc_campo;
		}

		$vc_nome = strtolower( basename( $vc_campo ) );
		$vc_sem  = strtolower( (string) pathinfo( $vc_nome, PATHINFO_FILENAME ) );

		foreach ( array( $vc_nome, $vc_sem ) as $vc_tentativa ) {
			if ( isset( $vc_mapa[ $vc_tentativa ] ) ) {
				return $vc_mapa[ $vc_tentativa ];
			}
		}

		return 0;
	}

	if ( 'livro' !== $vc_tipo ) {
		return 0;
	}

	$vc_slug = strtolower( sanitize_title( $vc_titulo ) );

	return $vc_mapa[ $vc_slug ] ?? 0;
};

/**
 * A resenha: o campo `resenha:` manda; sem ele, procura um texto publicado com o
 * nome da obra, ou com o nome e o sufixo `-resenha`. É o que faz o link aparecer
 * sozinho no dia em que a resenha for escrita.
 */
$vc_achar_resenha = static function ( string $vc_titulo, string $vc_campo ) use ( $vc_mapa_posts ) : string {
	if ( '' !== $vc_campo ) {
		return $vc_campo;
	}

	$vc_mapa = $vc_mapa_posts();
	$vc_slug = sanitize_title( $vc_titulo );

	foreach ( array( $vc_slug, $vc_slug . '-resenha' ) as $vc_tentativa ) {
		if ( isset( $vc_mapa[ $vc_tentativa ] ) ) {
			return (string) get_permalink( $vc_mapa[ $vc_tentativa ] );
		}
	}

	return '';
};

/** A abertura é o conteúdo SEM a lista e sem os comentários de bloco. */
$vc_abertura = static function ( string $conteudo ): string {
	$vc_sem_listas = preg_replace( '#<ul\b[^>]*>.*?</ul>#is', '', $conteudo );
	$vc_sem_listas = preg_replace( '#<!--\s*/?wp:[^>]*-->#i', '', (string) $vc_sem_listas );

	return trim( (string) $vc_sem_listas );
};

/** Cinco estrelas, cheias e vazias, a partir de uma nota de 1 a 5. */
$vc_estrelas = static function ( int $vc_n ): string {
	$vc_n = max( 0, min( 5, $vc_n ) );

	return str_repeat( "\xE2\x98\x85", $vc_n ) . str_repeat( "\xE2\x98\x86", 5 - $vc_n );
};

/** O rodapé de um título de livro: o link leva à resenha, ou à livraria. */
$vc_link_livro = static function ( string $vc_resenha, string $vc_amazon ): array {
	if ( '' !== $vc_resenha ) {
		return array( $vc_resenha, '' );
	}

	if ( '' !== $vc_amazon ) {
		return array( $vc_amazon, ' rel="sponsored nofollow noopener" target="_blank"' );
	}

	return array( '', '' );
};

get_header();

while ( have_posts() ) :
	the_post();

	$vc_conteudo = (string) get_the_content();
	$vc_obras    = $vc_ler_obras( $vc_conteudo );
	$vc_abre     = $vc_abertura( $vc_conteudo );

	/*
	 * TIPOS FORA DO AR.
	 *
	 * O TEATRO SAIU DA PÁGINA a pedido, e sai por AQUI, e não apagando as 56
	 * peças do conteúdo: a lista continua inteira no editor e volta a aparecer no
	 * dia em que 'teatro' sair deste array. Apagar o conteúdo seria uma decisão de
	 * dados tomada no lugar de uma decisão de tela.
	 *
	 * O CORTE É NA MONTAGEM DAS SEÇÕES, e é o único lugar que resolve os dois
	 * pontos de uma vez: a seção e a aba do filtro saem as duas daqui, porque as
	 * duas iteram `$vc_secoes`.
	 */
	$vc_fora_do_ar = array( 'teatro' );

	$vc_secoes = array();

	foreach ( $vc_obras as $vc_o ) {
		if ( in_array( $vc_o['tipo'], $vc_fora_do_ar, true ) ) {
			continue;
		}

		$vc_secoes[ $vc_o['tipo'] ][] = $vc_o;
	}

	/*
	 * A ORDEM DAS SEÇÕES: os quatro tipos conhecidos na ordem escrita no começo
	 * deste arquivo, e os desconhecidos no fim, na ordem em que apareceram.
	 */
	uksort(
		$vc_secoes,
		static function ( $a, $b ) use ( $vc_tipos ) {
			return ( $vc_tipos[ $a ]['ordem'] ?? 99 ) <=> ( $vc_tipos[ $b ]['ordem'] ?? 99 );
		}
	);
	/*
	 * OS DADOS ESTRUTURADOS DA BIBLIOTECA.
	 *
	 * ELES SAEM DAQUI, E NÃO DO `wp_head`, e o motivo é de ordem: o `head` roda
	 * antes de o template ler o conteúdo da página, e o formato de uma linha do
	 * acervo (`Título | tipo | campo: valor`) é lido por funções que existem
	 * neste arquivo. Repetir a leitura lá seria manter duas versões da mesma
	 * regra, e duas versões divergem.
	 *
	 * O QUE VALE A PENA DECLARAR AQUI É O LIVRO, por um motivo só: dos 224 itens,
	 * os livros são os únicos com NOTA. `Book` com `review` e `ratingValue` é o
	 * que o buscador transforma em estrela no resultado, e é o único conteúdo
	 * deste site com esse potencial.
	 *
	 * FILME, SÉRIE E PEÇA ENTRAM SEM NOTA, de propósito. Declarar `ratingValue`
	 * onde ninguém avaliou nada é marcação que não corresponde à página, e o
	 * buscador descarta o bloco inteiro quando encontra uma — inclusive os
	 * livros, que é o que presta. O tipo deles ainda é declarado, porque isso
	 * liga o título à obra certa.
	 */
	$vc_schema_itens = array();
	$vc_schema_pos   = 0;

	foreach ( $vc_secoes as $vc_itens_da_secao ) {
		foreach ( $vc_itens_da_secao as $vc_o ) {
			$vc_schema_pos++;
			$vc_campos_o = $vc_o['campos'];

			switch ( $vc_o['tipo'] ) {
				case 'livro':
					$vc_tipo_schema = 'Book';
					break;
				case 'filme':
					$vc_tipo_schema = 'Movie';
					break;
				case 'serie':
					$vc_tipo_schema = 'TVSeries';
					break;
				default:
					$vc_tipo_schema = 'CreativeWork';
			}

			$vc_obra = array(
				'@type' => $vc_tipo_schema,
				'name'  => (string) $vc_o['titulo'],
			);

			if ( '' !== (string) ( $vc_campos_o['autor'] ?? '' ) ) {
				$vc_obra['author'] = array(
					'@type' => 'Person',
					'name'  => (string) $vc_campos_o['autor'],
				);
			}

			$vc_id_capa  = $vc_achar_capa( (string) $vc_o['titulo'], (string) ( $vc_campos_o['capa'] ?? '' ), (string) $vc_o['tipo'] );
			$vc_url_capa = $vc_id_capa ? wp_get_attachment_image_url( $vc_id_capa, 'full' ) : '';

			if ( $vc_url_capa ) {
				$vc_obra['image'] = (string) $vc_url_capa;
			}

			/*
			 * O LINK DA AMAZON É A MESMA OBRA EM OUTRO LUGAR, e é isso que o
			 * `sameAs` diz. Não vai em `url`: `url` seria a página DESTA obra, e
			 * a página desta obra é esta lista.
			 */
			if ( '' !== (string) ( $vc_campos_o['amazon'] ?? '' ) ) {
				$vc_obra['sameAs'] = (string) $vc_campos_o['amazon'];
			}

			$vc_nota_livro = (int) ( $vc_campos_o['nota'] ?? 0 );

			if ( $vc_nota_livro > 0 ) {
				$vc_review = array(
					'@type'        => 'Review',
					'reviewRating' => array(
						'@type'       => 'Rating',
						'ratingValue' => $vc_nota_livro,
						'bestRating'  => 5,
						'worstRating' => 1,
					),
					'author'       => array( '@id' => home_url( '/' ) . '#autor' ),
				);

				if ( '' !== (string) ( $vc_campos_o['resumo'] ?? '' ) ) {
					$vc_review['reviewBody'] = (string) $vc_campos_o['resumo'];
				}

				$vc_url_resenha = $vc_achar_resenha( (string) $vc_o['titulo'], (string) ( $vc_campos_o['resenha'] ?? '' ) );

				if ( '' !== $vc_url_resenha ) {
					$vc_review['url'] = $vc_url_resenha;
				}

				$vc_obra['review'] = $vc_review;
			}

			$vc_schema_itens[] = array(
				'@type'    => 'ListItem',
				'position' => $vc_schema_pos,
				'item'     => $vc_obra,
			);
		}
	}

	if ( $vc_schema_itens ) {
		echo '<script type="application/ld+json">'
			. wp_json_encode(
				array(
					'@context'        => 'https://schema.org',
					'@type'           => 'ItemList',
					'@id'             => get_permalink() . '#biblioteca',
					'numberOfItems'   => count( $vc_schema_itens ),
					'itemListElement' => $vc_schema_itens,
				),
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
			)
			. '</script>' . "\n";
	}
	?>
	<article class="article article--acervo" data-page="acervo">
		<header class="article__header">
			<h1 class="article__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( '' !== $vc_abre ) : ?>
			<div class="prose acervo__abertura"><?php echo wp_kses_post( $vc_abre ); ?></div>
		<?php endif; ?>

		<?php if ( count( $vc_secoes ) > 1 ) : ?>
			<?php
			/*
			 * OS FILTROS SÃO SÓ OS TIPOS, E UM POR VEZ — como abas.
			 *
			 * Cada botão é um `radio` de verdade: clicar num tipo TROCA o tipo,
			 * nunca soma. Escolher "livros" e depois "filmes" mostra os filmes e
			 * tira os livros. É o comportamento de abas, e é o que se espera de um
			 * filtro por tipo: dois tipos marcados ao mesmo tempo não é um estado
			 * que alguém peça, é um estado em que se cai sem querer.
			 *
			 * A CONSEQUÊNCIA, dita por inteiro: como não existe aba "tudo",
			 * depois de escolher um tipo não há clique que devolva o acervo
			 * inteiro — `radio` não desmarca, e é por isso que a página abre com
			 * NENHUM marcado: esse é o estado em que tudo aparece. Recarregar
			 * volta a ele.
			 */
			?>
			<div class="acervo__filtros" role="radiogroup" aria-label="<?php esc_attr_e( 'filtrar por tipo', 'viniciuscodes' ); ?>">
				<?php foreach ( $vc_secoes as $vc_slug => $vc_itens ) : ?>
					<input class="acervo__radio" type="radio" name="acervo-tipo" id="acervo-<?php echo esc_attr( $vc_slug ); ?>">
					<label class="acervo__filtro" for="acervo-<?php echo esc_attr( $vc_slug ); ?>">
						<?php
						$vc_icone = (string) ( $vc_tipos[ $vc_slug ]['icone'] ?? '' );

						/*
						 * O ícone vem ANTES do rótulo e é decorativo: o `vc_icone()` sem
						 * rótulo já o marca `aria-hidden`, e o texto ao lado diz o nome —
						 * repetir seria ouvir "livros" duas vezes.
						 *
						 * Quando o tipo não declara ícone, ele simplesmente não entra: a
						 * pílula fica só com o nome, e nada é adivinhado no lugar dele.
						 */
						if ( '' !== $vc_icone ) :
							?>
							<span class="acervo__filtro-icone" aria-hidden="true"><?php echo vc_icone( $vc_icone, 15 ); ?></span>
							<?php
						endif;
						?>
						<?php echo esc_html( $vc_tipos[ $vc_slug ]['rotulo'] ?? $vc_slug ); ?>
						<span class="acervo__contagem"><?php echo (int) count( $vc_itens ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php
		foreach ( $vc_secoes as $vc_slug => $vc_itens ) :
			$vc_rotulo = $vc_tipos[ $vc_slug ]['rotulo'] ?? $vc_slug;
			?>
			<section class="acervo__secao" data-tipo="<?php echo esc_attr( $vc_slug ); ?>">
				<h2 class="section-title">
					<?php echo esc_html( $vc_rotulo ); ?>
					<span class="acervo__contagem"><?php echo (int) count( $vc_itens ); ?></span>
				</h2>

				<?php if ( 'livro' === $vc_slug ) : ?>

					<?php
					/*
					 * OS LIVROS NÃO TÊM DATA, e a ordem deles é a do editor — é a única
					 * informação de ordem que eles têm. Em vez de data, quem os separa em
					 * blocos é o `status:`.
					 *
					 * A ESTANTE NÃO É UM DIÁRIO. Em filme, série e teatro, a data
					 * responde tudo: ou já passou, ou está na vontade. Com livro não
					 * funciona assim — ter comprado e não ter lido ainda é um estado, e
					 * é o estado mais comum de quem tem estante. Por isso o livro ganha
					 * quatro rótulos e os outros tipos continuam com dois.
					 */
					$vc_por_status = array( 'lendo' => array(), 'quero' => array(), 'estante' => array(), 'lido' => array() );

					foreach ( $vc_itens as $vc_o ) {
						/*
						 * QUATRO ESTADOS, E NAO DOIS. O agrupamento anterior era uma
						 * comparacao de igualdade — `'lendo' === $status` — com tudo o
						 * que nao casasse caindo em "lidos". Bastava um livro sem
						 * status, ou com um status novo, para ele aparecer como lido.
						 *
						 * O ESTADO NEUTRO E "NA ESTANTE", nao "lido". Ter o livro e nao
						 * ter marcado nada sobre ele e o caso mais comum de quem esta
						 * montando a estante; tratar isso como leitura concluida e a
						 * unica leitura errada possivel.
						 *
						 * A COMPARACAO IGNORA ACENTO E CAIXA porque o campo e texto
						 * livre, preenchido no editor do celular. "Quero Ler", "quero
						 * ler" e "quero" sao a mesma intencao, e obrigar a forma exata
						 * seria criar um erro que nao aparece em lugar nenhum: o livro
						 * simplesmente cairia no balde errado, calado.
						 */
						$vc_s = strtolower( (string) ( $vc_o['campos']['status'] ?? '' ) );
						$vc_s = strtr( $vc_s, array( 'á' => 'a', 'ã' => 'a', 'â' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c' ) );
						$vc_s = trim( preg_replace( '/\s+/', ' ', $vc_s ) );

						if ( in_array( $vc_s, array( 'lendo', 'lendo agora', 'a ler', 'em leitura' ), true ) ) {
							$vc_grupo = 'lendo';
						} elseif ( in_array( $vc_s, array( 'quero ler', 'quero', 'a ler em breve', 'na lista', 'lista' ), true ) ) {
							$vc_grupo = 'quero';
						} elseif ( in_array( $vc_s, array( 'lido', 'li', 'lida', 'terminado', 'terminado' ), true ) ) {
							$vc_grupo = 'lido';
						} else {
							$vc_grupo = 'estante';
						}

						$vc_por_status[ $vc_grupo ][] = $vc_o;
					}

					/*
					 * A ORDEM DOS ROTULOS E A DA ESTANTE, e nao a do alfabeto: o que
					 * esta sendo lido agora vem primeiro porque e o que interessa a
					 * quem chega; o que ja foi lido fica por ultimo porque e o mais
					 * numeroso e o menos urgente.
					 */
					foreach ( array( 'lendo' => 'lendo no momento', 'quero' => 'quero ler', 'estante' => 'na estante', 'lido' => 'lidos' ) as $vc_chave_status => $vc_rotulo_status ) :
						if ( ! $vc_por_status[ $vc_chave_status ] ) {
							continue;
						}
						?>
						<h3 class="acervo__rotulo"><?php echo esc_html( $vc_rotulo_status ); ?></h3>

						<ul class="acervo__livros">
							<?php
							foreach ( $vc_por_status[ $vc_chave_status ] as $vc_o ) :
								$vc_id_capa  = $vc_achar_capa( $vc_o['titulo'], $vc_o['campos']['capa'] ?? '', $vc_o['tipo'] );
								$vc_resenha  = $vc_achar_resenha( $vc_o['titulo'], $vc_o['campos']['resenha'] ?? '' );
								$vc_amazon   = (string) ( $vc_o['campos']['amazon'] ?? '' );
								$vc_nota     = (int) ( $vc_o['campos']['nota'] ?? 0 );
								$vc_autor    = (string) ( $vc_o['campos']['autor'] ?? '' );
								$vc_resumo   = (string) ( $vc_o['campos']['resumo'] ?? '' );
								list( $vc_href, $vc_rel ) = $vc_link_livro( $vc_resenha, $vc_amazon );
								?>
								<li class="acervo__livro">
									<?php if ( $vc_id_capa ) : ?>
										<figure class="acervo__livro-capa">
											<?php if ( '' !== $vc_href ) : ?>
												<a href="<?php echo esc_url( $vc_href ); ?>"<?php echo $vc_rel; // phpcs:ignore WordPress.Security.EscapeOutput -- atributos fixos. ?>>
											<?php endif; ?>
											<?php
											echo wp_get_attachment_image(
												$vc_id_capa,
												'medium',
												false,
												array(
													'class'   => 'acervo__livro-img',
													'loading' => 'lazy',
													'alt'     => '',
												)
											);
											?>
											<?php if ( '' !== $vc_href ) : ?>
												</a>
											<?php endif; ?>
										</figure>
									<?php endif; ?>

									<div class="acervo__livro-texto">
										<h4 class="acervo__livro-titulo">
											<?php if ( '' !== $vc_href ) : ?>
												<a href="<?php echo esc_url( $vc_href ); ?>"<?php echo $vc_rel; // phpcs:ignore WordPress.Security.EscapeOutput -- atributos fixos. ?>>
											<?php endif; ?>
											<?php echo esc_html( $vc_o['titulo'] ); ?>
											<?php if ( '' !== $vc_href ) : ?>
												</a>
											<?php endif; ?>
										</h4>

										<?php if ( '' !== $vc_autor ) : ?>
											<p class="acervo__livro-autor"><?php echo esc_html( $vc_autor ); ?></p>
										<?php endif; ?>

										<?php if ( $vc_nota > 0 ) : ?>
											<p class="acervo__livro-nota">
												<span class="screen-reader-text">
													<?php
													/* translators: %d: nota de 1 a 5. */
													printf( esc_html__( 'nota %d de 5', 'viniciuscodes' ), $vc_nota );
													?>
												</span>
												<span aria-hidden="true"><?php echo esc_html( $vc_estrelas( $vc_nota ) ); ?></span>
											</p>
										<?php endif; ?>

										<?php if ( '' !== $vc_resumo ) : ?>
											<p class="acervo__livro-resumo"><?php echo esc_html( $vc_resumo ); ?></p>
										<?php endif; ?>

										<?php if ( '' !== $vc_amazon || '' !== $vc_resenha ) : ?>
											<p class="acervo__livro-acoes">
												<?php if ( '' !== $vc_amazon ) : ?>
													<a class="acervo__botao" href="<?php echo esc_url( $vc_amazon ); ?>" rel="sponsored nofollow noopener" target="_blank">
														<?php esc_html_e( 'ver na Amazon', 'viniciuscodes' ); ?> <span aria-hidden="true">↗</span>
													</a>
												<?php endif; ?>

												<?php if ( '' !== $vc_resenha ) : ?>
													<a class="acervo__botao" href="<?php echo esc_url( $vc_resenha ); ?>">
														<?php esc_html_e( 'ler a resenha', 'viniciuscodes' ); ?> <span aria-hidden="true">→</span>
													</a>
												<?php endif; ?>
											</p>
										<?php endif; ?>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
						<?php
					endforeach;

				else :

					/*
					 * FILMES, SÉRIES E TEATRO: por ano, do mais recente para o mais
					 * antigo, e a lista de vontades no fim. A obra entra no ano da
					 * data MAIS NOVA dela — um filme visto em 2024 e revisto em 2025
					 * é assunto de 2025.
					 */
					$vc_por_ano = array();

					foreach ( $vc_itens as $vc_o ) {
						if ( $vc_o['datas'] ) {
							$vc_por_ano[ substr( max( $vc_o['datas'] ), 0, 4 ) ][] = $vc_o;
						} else {
							$vc_por_ano['vontade'][] = $vc_o;
						}
					}

					krsort( $vc_por_ano );

					foreach ( $vc_por_ano as $vc_ano => $vc_do_ano ) :
						usort(
							$vc_do_ano,
							static function ( $a, $b ) {
								$da = $a['datas'] ? max( $a['datas'] ) : '';
								$db = $b['datas'] ? max( $b['datas'] ) : '';

								return strcmp( $db, $da ) ?: strcmp( $a['titulo'], $b['titulo'] );
							}
						);
						?>
						<h3 class="acervo__rotulo">
							<?php if ( 'vontade' === $vc_ano ) : ?>
								<?php esc_html_e( 'quero ver', 'viniciuscodes' ); ?>
							<?php else : ?>
								<?php echo esc_html( $vc_ano ); ?>
							<?php endif; ?>
							<span class="acervo__contagem"><?php echo (int) count( $vc_do_ano ); ?></span>
						</h3>

						<?php
						/*
						 * O TEATRO NÃO ENTRA NA GRADE, e o motivo é de conteúdo: no TMDb
						 * não existe catálogo de peça de teatro. Nas outras seções a placa é
						 * uma ESPERA — o retângulo fica no lugar da capa até o campo `capa:`
						 * existir. Na peça não há espera nenhuma: ela seria o desenho
						 * definitivo, e um retângulo que nunca vai ser pôster é um retângulo
						 * fingindo ser um.
						 *
						 * Então a peça sai como lista: a data (ou o selo de quantas vezes) em
						 * cima, o nome embaixo, no desenho da lista do próprio site. O que
						 * muda em relação à lista do tema é a densidade — ver `acervo.css`,
						 * onde as medidas estão justificadas.
						 */
						$vc_pecas = ( 'teatro' === $vc_slug );
						?>
						<ul class="<?php echo $vc_pecas ? 'acervo__pecas post-list' : 'acervo__grade'; ?>">
							<?php
							foreach ( $vc_do_ano as $vc_o ) :
								$vc_id_capa = $vc_achar_capa( $vc_o['titulo'], $vc_o['campos']['capa'] ?? '', $vc_o['tipo'] );
								$vc_resenha = $vc_achar_resenha( $vc_o['titulo'], $vc_o['campos']['resenha'] ?? '' );
								$vc_vezes   = count( $vc_o['datas'] );

								/*
								 * O TAMANHO DO TÍTULO DENTRO DA CAPA-TIPO vem do comprimento.
								 * "Parte" e "Bull" ficariam perdidos num corpo pensado para
								 * "Nos Bastidores de Stranger Things: The First Shadow", e o
								 * contrário também. O PHP sabe o comprimento do título; o CSS
								 * não sabe, e `clamp()` responde à tela e não ao texto.
								 */
								$vc_extenso = mb_strlen( $vc_o['titulo'], 'UTF-8' );
								$vc_tamanho = $vc_extenso > 34
									? ' acervo__placa--miudo'
									: ( $vc_extenso > 18 ? ' acervo__placa--medio' : '' );

								/*
								 * Dentro de um ano, "18/01" basta. Quando as datas de uma
								 * obra atravessam anos — "Mostra de Processo" tem 2024 e
								 * 2025 —, o ano volta ao lado do dia, senão a data ficaria
								 * mentindo sobre o ano do título acima dela.
								 */
								$vc_anos    = array_unique( array_map( static function ( $d ) {
									return substr( $d, 0, 4 );
								}, $vc_o['datas'] ) );
								$vc_com_ano = count( $vc_anos ) > 1;

								$vc_dias = array_map(
									static function ( $d ) use ( $vc_com_ano ) {
										return substr( $d, 8, 2 ) . '/' . substr( $d, 5, 2 ) . ( $vc_com_ano ? '/' . substr( $d, 0, 4 ) : '' );
									},
									$vc_o['datas']
								);
								?>
								<?php
								/*
								 * A LINHA DA PEÇA — e o `continue` abaixo é o que impede a carta de
								 * sair depois dela. O acervo imprime as obras num laço só, e as três
								 * seções COM capa compartilham o mesmo desenho de cartão. Aqui a
								 * peça é impressa e o laço pula para a próxima: o `continue` está
								 * no lugar de um `else` de setenta linhas em volta do cartão, que
								 * teria de reindentar tudo o que já funciona.
								 *
								 * O "quero ver" não se repete embaixo de cada peça: o rótulo do grupo
								 * já diz isso, e a linha só ganha a data quando ela existe.
								 */
								if ( $vc_pecas ) :

									$vc_tem_meta_peca = $vc_vezes > 1 || $vc_dias;
									?>
									<li class="post-item"<?php echo $vc_vezes > 1 ? ' data-revisto="' . (int) $vc_vezes . '"' : ''; ?>>
										<?php if ( $vc_tem_meta_peca ) : ?>
											<p class="post-item__meta">
												<?php if ( $vc_vezes > 1 ) : ?>
													<span class="acervo__vezes">
														<span class="screen-reader-text">
															<?php
															/* translators: %d: quantas vezes. */
															printf( esc_html__( 'assisti %d vezes', 'viniciuscodes' ), $vc_vezes );
															?>
														</span>
														<span aria-hidden="true"><?php echo (int) $vc_vezes; ?>×</span>
													</span>
												<?php endif; ?>

												<span class="acervo__datas"><?php echo esc_html( implode( ' · ', $vc_dias ) ); ?></span>
											</p>
										<?php endif; ?>

										<h4 class="post-item__title"><?php echo esc_html( $vc_o['titulo'] ); ?></h4>

										<?php
										/*
										 * A resenha, se existir. Hoje nenhuma peça tem uma, mas o campo
										 * existe para todos os tipos e a lista não pode ser o único lugar
										 * onde ele desaparece em silêncio. A `capa:` fica de fora de
										 * propósito: peça de teatro não tem imagem, por decisão do dono
										 * do site — e por isso ela é uma lista, e não uma carta.
										 */
										?>
										<?php if ( '' !== $vc_resenha ) : ?>
											<a class="acervo__resenha" href="<?php echo esc_url( $vc_resenha ); ?>">
												<?php esc_html_e( 'resenha', 'viniciuscodes' ); ?> <span aria-hidden="true">→</span>
											</a>
										<?php endif; ?>
									</li>
									<?php
									continue;
								endif;
								?>
								<li class="acervo__carta"<?php echo $vc_vezes > 1 ? ' data-revisto="' . (int) $vc_vezes . '"' : ''; ?>>
									<div class="acervo__capa">
										<?php if ( $vc_id_capa ) : ?>
											<?php
											echo wp_get_attachment_image(
												$vc_id_capa,
												'medium',
												false,
												array(
													'class'   => 'acervo__capa-img',
													'loading' => 'lazy',
													'alt'     => '',
												)
											);
											?>
										<?php else : ?>
											<?php
											/*
											 * A CAPA-TIPO: onde não há imagem, a capa é desenhada —
											 * a inicial grande e apagada ao fundo, o TÍTULO legível no
											 * meio e o tipo embaixo, dentro de uma moldura de linha
											 * fina. Duas coisas fazem isto ser uma capa e não uma
											 * caixa vazia: o título estar DENTRO dela, e o tamanho do
											 * título acompanhar o comprimento.
											 */
											?>
											<span class="acervo__placa<?php echo esc_attr( $vc_tamanho ); ?>">
												<span class="acervo__inicial" aria-hidden="true"><?php echo esc_html( mb_substr( $vc_o['titulo'], 0, 1, 'UTF-8' ) ); ?></span>
												<h4 class="acervo__placa-titulo"><?php echo esc_html( $vc_o['titulo'] ); ?></h4>
												<span class="acervo__placa-tipo" aria-hidden="true"><?php echo esc_html( $vc_tipos[ $vc_slug ]['singular'] ?? $vc_slug ); ?></span>
											</span>
										<?php endif; ?>
									</div>

									<?php if ( $vc_id_capa ) : ?>
										<h4 class="acervo__carta-titulo"><?php echo esc_html( $vc_o['titulo'] ); ?></h4>
									<?php endif; ?>

									<?php
									/*
									 * O "QUERO VER" NÃO SE REPETE EMBAIXO DE CADA CARTA do grupo
									 * "quero ver": o título do grupo já diz isso, e repetir seria
									 * 89 vezes a mesma palavra. Fica só a data, ou o selo, ou nada
									 * — e aí o parágrafo não é impresso, senão sobraria um vazio.
									 */
									$vc_tem_meta = $vc_vezes > 1 || $vc_dias;

									if ( $vc_tem_meta ) :
										?>
										<p class="acervo__meta">
											<?php if ( $vc_vezes > 1 ) : ?>
												<span class="acervo__vezes">
													<span class="screen-reader-text">
														<?php
														/* translators: %d: quantas vezes. */
														printf( esc_html__( 'assisti %d vezes', 'viniciuscodes' ), $vc_vezes );
														?>
													</span>
													<span aria-hidden="true"><?php echo (int) $vc_vezes; ?>×</span>
												</span>
											<?php endif; ?>

											<span class="acervo__datas"><?php echo esc_html( implode( ' · ', $vc_dias ) ); ?></span>
										</p>
									<?php endif; ?>

									<?php if ( '' !== $vc_resenha ) : ?>
										<a class="acervo__resenha" href="<?php echo esc_url( $vc_resenha ); ?>">
											<?php esc_html_e( 'resenha', 'viniciuscodes' ); ?> <span aria-hidden="true">→</span>
										</a>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
						<?php
					endforeach;

				endif;
				?>
			</section>
			<?php
		endforeach;
		?>

		<?php if ( ! $vc_obras ) : ?>
			<p class="empty"><?php esc_html_e( 'ainda não há nada por aqui.', 'viniciuscodes' ); ?></p>
		<?php endif; ?>
	</article>

	<?php
	/*
	 * O FILTRO É CSS, E NÃO JAVASCRIPT.
	 *
	 * Cada botão é um `radio` de verdade: funciona com o teclado, é anunciado
	 * como um grupo de opções pelo leitor de tela — e é UM POR VEZ, porque marcar
	 * um desmarca o outro. Marcar "livros" e depois "filmes" mostra os filmes e
	 * tira os livros: é o que "abas" quer dizer, e é o que este filtro é.
	 *
	 * UMA REGRA POR TIPO, e só isso: com o tipo marcado, as seções dos OUTROS
	 * tipos saem. Sem nada marcado — que é como a página abre — nenhuma regra
	 * casa, e a página mostra tudo. Isso também é o pior caso aceitável de um
	 * navegador antigo, sem `:has()`: nenhuma regra casa e tudo aparece.
	 *
	 * AS REGRAS SÃO ESCRITAS AQUI, e não no arquivo da folha, porque a lista de
	 * tipos é do conteúdo: se um dia entrar "documentário", ele vira filtro sem
	 * ninguém mexer no CSS.
	 */
	$vc_filtros = array();

	foreach ( array_keys( $vc_secoes ) as $vc_slug ) {
		$vc_filtros[] = sprintf(
			'.article--acervo:has(#acervo-%1$s:checked) .acervo__secao:not([data-tipo="%1$s"]){display:none}',
			$vc_slug
		);
	}

	if ( $vc_filtros ) {
		echo '<style id="vc-acervo-filtros">' . implode( '', $vc_filtros ) . '</style>';
	}
	?>
	<?php
endwhile;

get_footer();
