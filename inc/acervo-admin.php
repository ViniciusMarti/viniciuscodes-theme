<?php
/**
 * A tela do acervo no painel: livros, filmes, séries e teatro.
 *
 * AQUI O ACERVO É EDITADO COMO ELE JÁ EXISTE, e não como seria mais elegante.
 *
 * A lista mora no texto da página `/acervo/`, uma obra por linha:
 *
 *     Título | tipo | datas | capa: arquivo.jpg | autor: ... | nota: 4 | ...
 *
 * Quem lê esse texto é o `page-templates/acervo.php`, que agrupa por tipo, ordena
 * por ano, funde as datas repetidas e desenha as capas. A alternativa "certa" no
 * WordPress seria um tipo de conteúdo com os campos em metadados e a lista nativa
 * do painel — mais elegante por dentro, mas obrigaria a migrar 219 obras E a
 * reescrever a página pública inteira, mexendo no que acabou de ser medido e
 * conferido. O que esta tela faz é dar formulário ao formato que já existe.
 *
 * A GARANTIA É OUTRA, E É VERIFICÁVEL: abrir uma obra e salvar sem mudar nada tem
 * de deixar o texto BYTE A BYTE igual. É por isso que o miolo daqui é feito de
 * funções puras — recebem o texto e devolvem o texto, sem tocar no banco — e é
 * por isso que dá para provar, obra por obra, que ler e escrever de volta devolve
 * exatamente a linha que estava lá.
 *
 * AS DUAS ARMADILHAS QUE ESTA TELA EVITA, e que já custaram caro neste projeto:
 *
 *   1. `wp_update_post` passa o conteúdo pelo kses quando não há usuário com
 *      `unfiltered_html`. E o kses COME COMENTÁRIO DE BLOCO — que é o formato
 *      deste texto (`<!-- wp:list-item -->`). Sem o `wp_set_current_user`, a
 *      primeira gravação apagaria 219 marcadores de bloco em silêncio.
 *   2. E como ele falha em silêncio, o que ficou no banco é lido de volta e
 *      comparado com o que foi mandado. Diferente é erro, não aviso — e a volta
 *      é automática, porque o texto anterior fica guardado antes de gravar.
 *
 * A PÁGINA CONTINUA SENDO UMA PÁGINA. Ela segue no editor normal, para quem
 * preferir mexer à mão; esta tela é um formulário sobre o mesmo texto, não um
 * dono novo dele.
 *
 * @package ViniciusCodes
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
   OS TIPOS E OS CAMPOS
   ------------------------------------------------------------------------- */

/**
 * Os tipos de obra, na ordem em que o acervo os apresenta.
 *
 * A chave é o que vai ESCRITO no texto (é o que o template compara) e o rótulo é
 * só o que aparece na tela — separar os dois é o que permite escrever "séries"
 * no cabeçalho sem que isso vire um valor novo no arquivo.
 *
 * @return array<string, string>
 */
function vc_acervo_tipos(): array {
	return array(
		'livro'  => 'livros',
		'filme'  => 'filmes',
		'série'  => 'séries',
		'teatro' => 'teatro',
	);
}

/**
 * Os campos, na ordem em que são escritos na linha.
 *
 * A ORDEM É A DO TEXTO, e não uma escolha nova: o que sai daqui tem de sair igual
 * ao que estava lá. `so_livro` é o que a tela usa para não pedir autor nem nota
 * numa peça de teatro — mas um campo que JÁ TEM conteúdo nunca é escondido, em
 * tipo nenhum: esconder seria perder o que alguém escreveu.
 *
 * @return array<string, array{rotulo: string, so_livro: bool}>
 */
function vc_acervo_campos(): array {
	return array(
		'capa'    => array(
			'rotulo'   => 'imagem de capa',
			'so_livro' => false,
		),
		'autor'   => array(
			'rotulo'   => 'autor',
			'so_livro' => true,
		),
		'nota'    => array(
			'rotulo'   => 'nota (de 1 a 5)',
			'so_livro' => true,
		),
		'resumo'  => array(
			'rotulo'   => 'resumo',
			'so_livro' => true,
		),
		'amazon'  => array(
			'rotulo'   => 'link de compra',
			'so_livro' => false,
		),
		'resenha' => array(
			'rotulo'   => 'link da resenha',
			'so_livro' => false,
		),
		'status'  => array(
			'rotulo'   => 'estado da leitura',
			'so_livro' => true,
		),
		'pagina'  => array(
			'rotulo'   => 'página atual (progresso)',
			'so_livro' => true,
		),
		'paginas' => array(
			'rotulo'   => 'total de páginas',
			'so_livro' => true,
		),
	);
}

/**
 * Prepara um valor para ir ao texto da página, e o traz de volta.
 *
 * O texto guarda `&amp;`, não `&` — é HTML, e o endereço da Amazon tem
 * `&amp;linkCode=...` escrito assim. Se isso não fosse tratado, o formulário
 * mostraria `&amp;amp;` (feio) e uma gravação sem edição mudaria o valor (pior).
 * Então: o que se lê é decodificado, o que se grava é codificado, e as duas
 * funções são uma dupla — uma sem a outra não fecha.
 *
 * O APÓSTROFO ENTRA NA MESMA CONTA, e ele foi um erro medido: o próprio editor
 * do WordPress grava `That &#039;90s Show` no acervo. Sem tratar o `'`, a ida e
 * volta devolvia `That '90s Show` — um byte de diferença em uma obra das 219,
 * que a conferência achou e o olho não acharia. É por isso que `&#039;` está
 * aqui: não é preciosismo, é o formato que o editor usa.
 *
 * @param string $valor O valor.
 * @return string
 */
function vc_acervo_esc( string $valor ): string {
	return str_replace(
		array( '&', '<', '>', '"', "'" ),
		array( '&amp;', '&lt;', '&gt;', '&quot;', '&#039;' ),
		$valor
	);
}

/**
 * O caminho de volta de `vc_acervo_esc()`.
 *
 * @param string $valor O valor guardado no texto.
 * @return string
 */
function vc_acervo_dec( string $valor ): string {
	return html_entity_decode( $valor, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

/* -------------------------------------------------------------------------
   1. A PÁGINA DO ACERVO
   ------------------------------------------------------------------------- */

/**
 * O ID da página que guarda o acervo.
 *
 * É procurada pelo TEMPLATE, e não pelo nome: o acervo é a página que usa
 * `page-templates/acervo.php`, e isso continua verdade mesmo que o endereço dela
 * mude um dia. Procurar por `post_name = 'acervo'` amarraria esta tela a um
 * endereço, que é justamente a parte que costuma mudar.
 *
 * @return int Zero quando não há página com esse template.
 */
function vc_acervo_pagina( string $tipo = 'livro' ): int {
	static $cache = array();

	if ( isset( $cache[ $tipo ] ) ) {
		return (int) $cache[ $tipo ];
	}

	$slug     = ( 'filme' === $tipo ) ? 'filmes' : 'livros';
	$template = ( 'filme' === $tipo ) ? 'page-templates/filmes.php' : 'page-templates/livros.php';

	/*
	 * Pelo template da página correspondente.
	 */
	$achadas = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $template,
		)
	);

	if ( $achadas ) {
		$cache[ $tipo ] = (int) $achadas[0];

		return $cache[ $tipo ];
	}

	/*
	 * Pelo endereço correspondente (/livros/ ou /filmes/).
	 */
	$pagina = get_page_by_path( $slug );

	if ( $pagina ) {
		$cache[ $tipo ] = (int) $pagina->ID;

		return $cache[ $tipo ];
	}

	/*
	 * Fallback para a página unificada anterior (/acervo/), caso ainda não existam páginas separadas.
	 */
	$legado = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-templates/acervo.php',
		)
	);

	if ( $legado ) {
		$cache[ $tipo ] = (int) $legado[0];

		return $cache[ $tipo ];
	}

	$pagina_legado = get_page_by_path( 'acervo' );

	if ( $pagina_legado ) {
		$cache[ $tipo ] = (int) $pagina_legado->ID;

		return $cache[ $tipo ];
	}

	$cache[ $tipo ] = 0;

	return 0;
}

/* -------------------------------------------------------------------------
   2. LER O TEXTO
   ------------------------------------------------------------------------- */

/**
 * As obras que estão escritas no texto, com a posição de cada linha.
 *
 * A POSIÇÃO É O PONTO PRINCIPAL. Cada obra guarda onde começa e onde termina a
 * sua linha, para que gravar seja trocar AQUELA linha e mais nada — e não
 * reescrever o arquivo inteiro. Reescrever tudo significaria reescrever as 219
 * obras toda vez que uma capa mudasse, e é aí que um acento se perde.
 *
 * Uma linha se lê assim: o primeiro pedaço é o título, o segundo é o tipo, e do
 * terceiro em diante ou é a data ou é um campo `nome: valor`. Quem separa os dois
 * casos são os dois-pontos — um campo tem, uma data não tem.
 *
 * @param string $html O texto da página.
 * @return array<int, array{inicio: int, fim: int, texto: string, titulo: string, tipo: string, datas: array<int, string>, campos: array<string, string>}>
 */
function vc_acervo_ler( string $html ): array {
	$obras  = array();
	$padrao = '~<!-- wp:list-item --><li>(.*?)</li><!-- /wp:list-item -->~s';

	if ( ! preg_match_all( $padrao, $html, $achados, PREG_OFFSET_CAPTURE ) ) {
		return $obras;
	}

	foreach ( $achados[0] as $i => $bruto ) {
		$texto  = $achados[1][ $i ][0];
		$partes = array_map( 'trim', explode( '|', $texto ) );

		$titulo = vc_acervo_dec( (string) array_shift( $partes ) );
		$tipo   = trim( (string) array_shift( $partes ) );

		$datas  = array();
		$campos = array();

		foreach ( $partes as $parte ) {
			if ( '' === $parte ) {
				continue;
			}

			/* Campo: uma palavra curta em minúsculas, dois-pontos, espaço. */
			if ( preg_match( '~^([a-z]{3,10}):\s*(.*)$~u', $parte, $m ) ) {
				$campos[ $m[1] ] = vc_acervo_dec( $m[2] );
				continue;
			}

			/* Data, ou lista de datas separadas por vírgula. */
			if ( preg_match( '~^\d{2}/\d{2}/\d{4}(\s*,\s*\d{2}/\d{2}/\d{4})*$~', $parte ) ) {
				$datas = array_values( array_filter( array_map( 'trim', explode( ',', $parte ) ) ) );
			}
		}

		$obras[] = array(
			'inicio' => (int) $bruto[1],
			'fim'    => (int) $bruto[1] + strlen( $bruto[0] ),
			'texto'  => $texto,
			'titulo' => $titulo,
			'tipo'   => $tipo,
			'datas'  => $datas,
			'campos' => $campos,
		);
	}

	return $obras;
}

/* -------------------------------------------------------------------------
   3. ESCREVER UMA LINHA
   ------------------------------------------------------------------------- */

/**
 * A linha de uma obra, do jeito que o texto da página a guarda.
 *
 *   - o título e o tipo vêm primeiro, e o tipo é um valor FECHADO (livro, filme,
 *     série, teatro), porque é por ele que a página agrupa;
 *   - as datas vêm antes dos campos, vírgula e espaço entre elas; sem data, a
 *     obra entra no "quero ver" — é assim que a página lê;
 *   - depois, os campos na ordem em que já estavam. Campo vazio não é escrito:
 *     `nota:` numa peça de teatro seria informação falsa;
 *   - e sobra sempre um ` | ` no fim quando não há nada depois do tipo, que é o
 *     que o texto já faz nas obras sem data e sem campo.
 *
 * @param array{titulo: string, tipo: string, datas: array<int, string>, campos: array<string, string>} $obra A obra.
 * @return string
 */
function vc_acervo_montar( array $obra ): string {
	$partes = array();

	if ( ! empty( $obra['datas'] ) ) {
		$partes[] = implode( ', ', $obra['datas'] );
	}

	foreach ( (array) $obra['campos'] as $nome => $valor ) {
		$valor = trim( (string) $valor );

		if ( '' !== $valor ) {
			$partes[] = $nome . ': ' . vc_acervo_esc( $valor );
		}
	}

	return '<!-- wp:list-item --><li>'
		. vc_acervo_esc( trim( (string) $obra['titulo'] ) )
		. ' | ' . trim( (string) $obra['tipo'] )
		. ' | ' . implode( ' | ', $partes )
		. '</li><!-- /wp:list-item -->';
}

/* -------------------------------------------------------------------------
   4. APLICAR UMA MUDANÇA (função pura: texto entra, texto sai)
   ------------------------------------------------------------------------- */

/**
 * Devolve o texto do acervo com uma obra trocada, acrescentada ou removida.
 *
 * NÃO TOCA NO BANCO, de propósito. É isso que permite conferir, antes de gravar,
 * que uma ida e volta sem edição deixa o texto idêntico — e que permite testar
 * "acrescentar" e "remover" sobre uma cópia, sem arriscar a página.
 *
 * A remoção é resolvida ANTES de qualquer validação de conteúdo: quem remove manda
 * só o índice, e exigir um nome de uma obra que está saindo seria recusar a
 * remoção por um motivo que não existe.
 *
 * @param string               $html O texto atual.
 * @param array<string, mixed> $acao `indice` (-1 para obra nova), `obra` e/ou `remover`.
 * @return array{html: string, erro: string}
 */
function vc_acervo_aplicar( string $html, array $acao ): array {
	$obras  = vc_acervo_ler( $html );
	$indice = isset( $acao['indice'] ) ? (int) $acao['indice'] : -1;

	/* 4a. Remover: a linha sai inteira, com o fim de linha que vem depois dela,
	       para não deixar uma linha vazia no lugar. */
	if ( ! empty( $acao['remover'] ) ) {
		if ( ! isset( $obras[ $indice ] ) ) {
			return array(
				'html' => $html,
				'erro' => 'Essa obra não está mais no texto. Recarregue a tela e tente de novo.',
			);
		}

		$inicio = $obras[ $indice ]['inicio'];
		$fim    = $obras[ $indice ]['fim'];

		if ( "\n" === substr( $html, $fim, 1 ) ) {
			$fim++;
		}

		return array(
			'html' => substr( $html, 0, $inicio ) . substr( $html, $fim ),
			'erro' => '',
		);
	}

	$dados  = isset( $acao['obra'] ) && is_array( $acao['obra'] ) ? $acao['obra'] : array();
	$titulo = isset( $dados['titulo'] ) ? trim( (string) $dados['titulo'] ) : '';
	$tipo   = isset( $dados['tipo'] ) ? trim( (string) $dados['tipo'] ) : '';

	if ( '' === $titulo ) {
		return array(
			'html' => $html,
			'erro' => 'A obra precisa de um nome.',
		);
	}

	if ( ! isset( vc_acervo_tipos()[ $tipo ] ) ) {
		return array(
			'html' => $html,
			'erro' => 'Escolha um tipo: livros, filmes, séries ou teatro.',
		);
	}

	$datas = array();

	foreach ( (array) ( $dados['datas'] ?? array() ) as $data ) {
		$data = trim( (string) $data );

		if ( '' === $data ) {
			continue;
		}

		if ( ! preg_match( '~^(\d{2})/(\d{2})/(\d{4})$~', $data, $pedacos ) || ! checkdate( (int) $pedacos[2], (int) $pedacos[1], (int) $pedacos[3] ) ) {
			return array(
				'html' => $html,
				'erro' => sprintf( 'A data "%s" não existe. Escreva dia/mês/ano, como 31/12/2026.', $data ),
			);
		}

		if ( ! in_array( $data, $datas, true ) ) {
			$datas[] = $data;
		}
	}

	$novo = vc_acervo_montar(
		array(
			'titulo' => $titulo,
			'tipo'   => $tipo,
			'datas'  => $datas,
			'campos' => isset( $dados['campos'] ) && is_array( $dados['campos'] ) ? $dados['campos'] : array(),
		)
	);

	/* 4b. Obra nova: entra no fim da lista, antes do fecho dela. */
	if ( -1 === $indice ) {
		$fecha = strrpos( $html, '</ul>' );

		if ( false === $fecha ) {
			return array(
				'html' => $html,
				'erro' => 'Não achei o fim da lista no texto da página.',
			);
		}

		return array(
			'html' => substr( $html, 0, $fecha ) . $novo . "\n" . substr( $html, $fecha ),
			'erro' => '',
		);
	}

	/* 4c. Editar: troca a linha pela nova, no mesmo lugar. */
	if ( ! isset( $obras[ $indice ] ) ) {
		return array(
			'html' => $html,
			'erro' => 'Essa obra não está mais no texto. Recarregue a tela e tente de novo.',
		);
	}

	return array(
		'html' => substr( $html, 0, $obras[ $indice ]['inicio'] ) . $novo . substr( $html, $obras[ $indice ]['fim'] ),
		'erro' => '',
	);
}

/* -------------------------------------------------------------------------
   5. GRAVAR
   ------------------------------------------------------------------------- */

/**
 * Grava o texto na página do acervo, confere o que ficou lá, e desfaz se não for.
 *
 * O texto anterior é guardado numa opção ANTES de gravar: se o WordPress devolver
 * algo diferente do que foi mandado, a volta não depende de ninguém ter um backup
 * em arquivo. E se a volta também for necessária, é feita na hora.
 *
 * @param string $html O texto novo.
 * @return array{ok: bool, erro: string}
 */
function vc_acervo_gravar( string $html, int $id = 0 ): array {
	if ( ! $id ) {
		$id = vc_acervo_pagina();
	}

	if ( ! $id ) {
		return array(
			'ok'   => false,
			'erro' => 'Não achei a página do acervo.',
		);
	}

	if ( ! current_user_can( 'edit_post', $id ) ) {
		return array(
			'ok'   => false,
			'erro' => 'Você não tem permissão para editar esta página.',
		);
	}

	$antes = (string) get_post_field( 'post_content', $id );

	update_option(
		'vc_acervo_antes',
		array(
			'quando' => current_time( 'mysql' ),
			'html'   => $antes,
		),
		false
	);

	wp_set_current_user( 1 );

	$r = wp_update_post(
		array(
			'ID'           => $id,
			'post_content' => wp_slash( $html ),
		),
		true
	);

	if ( is_wp_error( $r ) ) {
		return array(
			'ok'   => false,
			'erro' => $r->get_error_message(),
		);
	}

	$depois = (string) get_post_field( 'post_content', $id );

	if ( $depois !== $html ) {
		wp_update_post(
			array(
				'ID'           => $id,
				'post_content' => wp_slash( $antes ),
			)
		);

		return array(
			'ok'   => false,
			'erro' => 'O WordPress alterou o texto ao gravar (provavelmente um comentário de bloco perdido). Desfiz a gravação — confira e tente de novo.',
		);
	}

	return array(
		'ok'   => true,
		'erro' => '',
	);
}

/* -------------------------------------------------------------------------
   6. A MINIATURA DA CAPA
   ------------------------------------------------------------------------- */

/**
 * O mapa da biblioteca de mídia, uma vez por página.
 *
 * A mesma ideia do template do acervo: o que se escreve no campo `capa:` é o NOME
 * do arquivo, e o mapa traduz nome para anexo. Montado de uma vez, e não a cada
 * linha da lista — com 219 obras, uma consulta por linha seria uma consulta por
 * linha de verdade.
 *
 * @return array<string, int>
 */
function vc_acervo_mapa_midia(): array {
	static $mapa = null;

	if ( null !== $mapa ) {
		return $mapa;
	}

	$mapa = array();

	$ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	foreach ( (array) $ids as $midia ) {
		$mapa[ (string) get_post_field( 'post_name', $midia ) ] = (int) $midia;
	}

	return $mapa;
}

/**
 * O endereço da miniatura da capa, do que estiver escrito no campo.
 *
 * Aceita o que o template aceita, e na mesma ordem: o número do anexo, um
 * endereço inteiro, o nome do arquivo ou o nome sem a extensão. Assim a prévia
 * desta tela e a imagem da página pública nunca discordam.
 *
 * @param string $campo O que está escrito no campo `capa:`.
 * @return string Vazio quando não há imagem.
 */
function vc_acervo_miniatura( string $campo ): string {
	$campo = trim( $campo );

	if ( '' === $campo ) {
		return '';
	}

	if ( ctype_digit( $campo ) ) {
		return (string) wp_get_attachment_image_url( (int) $campo, 'thumbnail' );
	}

	if ( preg_match( '~^https?://~i', $campo ) ) {
		return $campo;
	}

	$nome = strtolower( basename( $campo ) );
	$sem  = strtolower( (string) pathinfo( $nome, PATHINFO_FILENAME ) );
	$mapa = vc_acervo_mapa_midia();

	foreach ( array( $nome, $sem ) as $tentativa ) {
		if ( isset( $mapa[ $tentativa ] ) ) {
			return (string) wp_get_attachment_image_url( $mapa[ $tentativa ], 'thumbnail' );
		}
	}

	return '';
}

/* -------------------------------------------------------------------------
   7. O MENU
   ------------------------------------------------------------------------- */

/**
 * "Acervo" no menu do painel.
 *
 * Menu próprio, e não um item debaixo de Páginas: são 219 obras, é a maior parte
 * do que se escreve neste site, e um endereço próprio é o que faz a tela ser
 * encontrada em vez de procurada.
 */
function vc_acervo_menu() {
	add_menu_page(
		'Acervo',
		'Acervo',
		'edit_pages',
		'vc-acervo',
		'vc_acervo_tela_livros',
		'dashicons-book-alt',
		21
	);

	add_submenu_page(
		'vc-acervo',
		'Livros',
		'Livros',
		'edit_pages',
		'vc-acervo',
		'vc_acervo_tela_livros'
	);

	add_submenu_page(
		'vc-acervo',
		'Filmes',
		'Filmes',
		'edit_pages',
		'vc-acervo-filmes',
		'vc_acervo_tela_filmes'
	);
}
add_action( 'admin_menu', 'vc_acervo_menu' );

/**
 * A biblioteca de mídia, só nesta tela.
 *
 * `wp_enqueue_media()` é o que dá o seletor de imagens do painel — abas, busca,
 * a grade. Sem ele o botão de escolher a capa não teria o que abrir.
 */
function vc_acervo_media() {
	$tela = get_current_screen();

	if ( $tela && in_array( $tela->id, array( 'toplevel_page_vc-acervo', 'acervo_page_vc-acervo-filmes' ), true ) ) {
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'vc_acervo_media' );

/* -------------------------------------------------------------------------
   8. A TELA
   ------------------------------------------------------------------------- */

/**
 * Um aviso, no formato de aviso do próprio painel.
 *
 * @param string $tipo  `success` ou `error`.
 * @param string $texto O que dizer.
 */
function vc_acervo_aviso( string $tipo, string $texto ): void {
	printf(
		'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
		esc_attr( $tipo ),
		esc_html( $texto )
	);
}

/**
 * O endereço desta tela, com os parâmetros que ela entende.
 *
 * @param array<string, string|int> $extra Parâmetros.
 * @param string                    $tipo  'livro' ou 'filme'.
 * @return string
 */
function vc_acervo_url( array $extra = array(), string $tipo = 'livro' ): string {
	$pagina = ( 'filme' === $tipo ) ? 'vc-acervo-filmes' : 'vc-acervo';

	return add_query_arg(
		array_merge( array( 'page' => $pagina ), $extra ),
		admin_url( 'admin.php' )
	);
}

/**
 * O formulário de uma obra.
 *
 * @param array{titulo: string, tipo: string, datas: array<int, string>, campos: array<string, string>} $obra       A obra.
 * @param int                                                                                          $indice     -1 para obra nova.
 * @param string                                                                                       $tipo_secao 'livro' ou 'filme'.
 */
function vc_acervo_formulario( array $obra, int $indice, string $tipo_secao = 'livro' ): void {
	$livro = ( 'livro' === $obra['tipo'] );

	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'vc_acervo_' . $indice ); ?>
		<input type="hidden" name="action" value="vc_acervo_salvar">
		<input type="hidden" name="indice" value="<?php echo (int) $indice; ?>">
		<input type="hidden" name="tipo_secao" value="<?php echo esc_attr( $tipo_secao ); ?>">

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="vc-titulo">nome</label></th>
				<td>
					<input type="text" id="vc-titulo" name="titulo" class="regular-text" required
						value="<?php echo esc_attr( $obra['titulo'] ); ?>">
					<p class="description">Como a obra aparece no acervo.</p>
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="vc-tipo">tipo</label></th>
				<td>
					<select id="vc-tipo" name="tipo">
						<?php foreach ( vc_acervo_tipos() as $valor => $rotulo ) : ?>
							<option value="<?php echo esc_attr( $valor ); ?>" <?php selected( $obra['tipo'], $valor ); ?>>
								<?php echo esc_html( $rotulo ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">É por ele que a página agrupa e ordena.</p>
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="vc-datas">datas</label></th>
				<td>
					<input type="text" id="vc-datas" name="datas" class="regular-text"
						value="<?php echo esc_attr( implode( ', ', $obra['datas'] ) ); ?>">
					<p class="description">
						<code>31/12/2026</code>; mais de uma, separadas por vírgula. Assistir de novo é
						acrescentar a data aqui. <strong>Em branco, a obra entra no "quero ver".</strong>
					</p>
				</td>
			</tr>

			<?php foreach ( vc_acervo_campos() as $nome => $info ) : ?>
				<?php
				$valor  = (string) ( $obra['campos'][ $nome ] ?? '' );
				$id     = 'vc-campo-' . $nome;
				$esconder = $info['so_livro'] && ! $livro && '' === $valor;
				?>
				<tr class="vc-linha-campo<?php echo $info['so_livro'] ? ' vc-campo-livro' : ''; ?><?php echo $esconder ? ' vc-campo-escondido' : ''; ?>">
					<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $info['rotulo'] ); ?></label></th>
					<td>
						<?php if ( 'resumo' === $nome ) : ?>
							<textarea id="<?php echo esc_attr( $id ); ?>" name="campos[<?php echo esc_attr( $nome ); ?>]" rows="4" class="large-text"><?php echo esc_textarea( $valor ); ?></textarea>

						<?php elseif ( 'nota' === $nome ) : ?>
							<select id="<?php echo esc_attr( $id ); ?>" name="campos[<?php echo esc_attr( $nome ); ?>]">
								<option value="">sem nota</option>
								<?php foreach ( array( 1, 2, 3, 4, 5 ) as $estrela ) : ?>
									<option value="<?php echo (int) $estrela; ?>" <?php selected( $valor, (string) $estrela ); ?>>
										<?php echo esc_html( str_repeat( '★', $estrela ) ); ?>
									</option>
								<?php endforeach; ?>
							</select>

						<?php elseif ( 'status' === $nome ) : ?>
							<select id="<?php echo esc_attr( $id ); ?>" name="campos[<?php echo esc_attr( $nome ); ?>]">
								<?php
								$estados = array(
									''           => '—',
									'lendo'      => 'lendo',
									'lido'       => 'lido',
									'abandonado' => 'abandonado',
								);
								?>
								<?php foreach ( $estados as $chave => $rotulo ) : ?>
									<option value="<?php echo esc_attr( $chave ); ?>" <?php selected( $valor, $chave ); ?>>
										<?php echo esc_html( $rotulo ); ?>
									</option>
								<?php endforeach; ?>
							</select>

						<?php else : ?>
							<input type="text" id="<?php echo esc_attr( $id ); ?>" name="campos[<?php echo esc_attr( $nome ); ?>]"
								class="regular-text" value="<?php echo esc_attr( $valor ); ?>">
						<?php endif; ?>

						<?php if ( 'capa' === $nome ) : ?>
							<?php $miniatura = vc_acervo_miniatura( $valor ); ?>
							<p class="vc-capa">
								<button type="button" class="button vc-capa-escolher">escolher da biblioteca</button>
								<span class="vc-capa-previa">
									<?php if ( '' !== $miniatura ) : ?>
										<img src="<?php echo esc_url( $miniatura ); ?>" alt="">
									<?php endif; ?>
								</span>
							</p>
							<p class="description">
								O nome do arquivo na biblioteca de mídia — ou o número do anexo, ou um
								endereço inteiro. Em branco, o livro procura a capa sozinho pelo título.
							</p>

						<?php elseif ( 'resenha' === $nome ) : ?>
							<p class="description">
								O endereço da resenha, quando ela existe. Em branco, o site procura
								sozinho pelo nome da obra.
							</p>

						<?php elseif ( 'amazon' === $nome ) : ?>
							<p class="description">O endereço onde a obra pode ser comprada.</p>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary">
				<?php echo -1 === $indice ? 'acrescentar' : 'salvar'; ?>
			</button>
			<a class="button" href="<?php echo esc_url( vc_acervo_url( array(), $tipo_secao ) ); ?>">cancelar</a>
		</p>
	</form>
	<?php
}

/**
 * A tela: a lista de obras do tipo selecionado, o formulário e os avisos.
 *
 * @param string $tipo_ativo 'livro' ou 'filme'.
 */
function vc_acervo_tela( string $tipo_ativo = 'livro' ) {
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( esc_html__( 'Você não tem permissão para editar o acervo.', 'viniciuscodes' ) );
	}

	$id = vc_acervo_pagina( $tipo_ativo );

	if ( ! $id ) {
		vc_acervo_aviso( 'error', sprintf( 'Não achei a página de %s no WordPress.', 'filme' === $tipo_ativo ? 'filmes' : 'livros' ) );
		return;
	}

	$html  = (string) get_post_field( 'post_content', $id );
	$obras = vc_acervo_ler( $html );

	$arq          = isset( $_GET['vc-aviso'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['vc-aviso'] ) ) : '';
	$erro         = isset( $_GET['vc-erro'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['vc-erro'] ) ) : '';
	$abrir        = isset( $_GET['obra'] ) ? (int) $_GET['obra'] : null;
	$busca        = isset( $_GET['busca'] ) ? trim( sanitize_text_field( wp_unslash( (string) $_GET['busca'] ) ) ) : '';
	$titulo_secao = ( 'filme' === $tipo_ativo ) ? 'Filmes' : 'Livros';
	$slug_pagina  = ( 'filme' === $tipo_ativo ) ? 'vc-acervo-filmes' : 'vc-acervo';
	?>
	<div class="wrap vc-acervo">
		<h1 class="wp-heading-inline"><?php echo esc_html( $titulo_secao ); ?></h1>
		<a class="page-title-action" href="<?php echo esc_url( vc_acervo_url( array( 'obra' => -1 ), $tipo_ativo ) ); ?>">acrescentar <?php echo esc_html( 'filme' === $tipo_ativo ? 'filme' : 'livro' ); ?></a>
		<a class="page-title-action" href="<?php echo esc_url( (string) get_permalink( $id ) ); ?>" target="_blank" rel="noopener">ver página pública</a>
		<hr class="wp-header-end">

		<?php
		if ( '' !== $arq ) {
			vc_acervo_aviso( 'success', $arq );
		}

		if ( '' !== $erro ) {
			vc_acervo_aviso( 'error', $erro );
		}
		?>
		<p class="vc-conta">
			<?php echo esc_html( count( $obras ) . ' ' . ( 'filme' === $tipo_ativo ? 'filmes cadastrados.' : 'livros cadastrados.' ) ); ?>
			Vive no texto da <a href="<?php echo esc_url( (string) get_edit_post_link( $id ) ); ?>">página <?php echo (int) $id; ?></a>,
			uma obra por linha.
		</p>

		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="vc-busca">
			<input type="hidden" name="page" value="<?php echo esc_attr( $slug_pagina ); ?>">
			<label class="screen-reader-text" for="vc-busca">buscar</label>
			<input type="search" id="vc-busca" name="busca" value="<?php echo esc_attr( $busca ); ?>" placeholder="buscar pelo nome">
			<button type="submit" class="button">buscar</button>
			<?php if ( '' !== $busca ) : ?>
				<a class="button" href="<?php echo esc_url( vc_acervo_url( array(), $tipo_ativo ) ); ?>">limpar</a>
			<?php endif; ?>
		</form>

		<?php if ( -1 === $abrir ) : ?>
			<div class="vc-painel">
				<h2>acrescentar <?php echo esc_html( 'filme' === $tipo_ativo ? 'filme' : 'livro' ); ?></h2>
				<?php
				vc_acervo_formulario(
					array(
						'titulo' => '',
						'tipo'   => $tipo_ativo,
						'datas'  => array(),
						'campos' => array(),
					),
					-1,
					$tipo_ativo
				);
				?>
			</div>
		<?php endif; ?>

		<?php if ( null !== $abrir && $abrir >= 0 && isset( $obras[ $abrir ] ) ) : ?>
			<div class="vc-painel">
				<h2>editando: <?php echo esc_html( $obras[ $abrir ]['titulo'] ); ?></h2>
				<?php vc_acervo_formulario( $obras[ $abrir ], $abrir, $tipo_ativo ); ?>
			</div>
		<?php endif; ?>

		<?php
		$grupos = array( $tipo_ativo => ( 'filme' === $tipo_ativo ? 'filmes' : 'livros' ) );

		foreach ( $grupos as $tipo => $rotulo ) :
			$lista = array();

			foreach ( $obras as $i => $o ) {
				if ( '' !== $o['tipo'] && $o['tipo'] !== $tipo ) {
					continue;
				}

				if ( '' !== $busca && false === mb_stripos( $o['titulo'], $busca ) ) {
					continue;
				}

				$lista[ $i ] = $o;
			}
			?>
			<?php if ( $lista ) : ?>
				<table class="wp-list-table widefat fixed striped vc-tabela">
					<thead>
						<tr>
							<td class="vc-col-capa">capa</td>
							<td>nome</td>
							<td class="vc-col-datas"><?php echo 'filme' === $tipo_ativo ? 'datas' : 'autor'; ?></td>
							<td class="vc-col-campos">detalhes</td>
							<td class="vc-col-acoes">ações</td>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $lista as $i => $o ) : ?>
						<tr>
							<td class="vc-col-capa">
								<?php $miniatura = vc_acervo_miniatura( (string) ( $o['campos']['capa'] ?? '' ) ); ?>
								<?php if ( '' !== $miniatura ) : ?>
									<img src="<?php echo esc_url( $miniatura ); ?>" alt="" width="40" height="60">
								<?php else : ?>
									<span class="vc-sem-capa" aria-hidden="true">—</span>
								<?php endif; ?>
							</td>
							<td>
								<strong>
									<a href="<?php echo esc_url( vc_acervo_url( array( 'obra' => $i ), $tipo_ativo ) ); ?>"><?php echo esc_html( $o['titulo'] ); ?></a>
								</strong>
							</td>
							<td class="vc-col-datas">
								<?php if ( 'filme' === $tipo_ativo ) : ?>
									<?php if ( $o['datas'] ) : ?>
										<?php echo esc_html( implode( ', ', $o['datas'] ) ); ?>
										<?php if ( count( $o['datas'] ) > 1 ) : ?>
											<span class="vc-vezes"><?php echo (int) count( $o['datas'] ); ?>×</span>
										<?php endif; ?>
									<?php else : ?>
										<span class="vc-quero">quero ver</span>
									<?php endif; ?>
								<?php else : ?>
									<?php echo esc_html( $o['campos']['autor'] ?? '—' ); ?>
								<?php endif; ?>
							</td>
							<td class="vc-col-campos">
								<?php
								$escritos = array();
								foreach ( $o['campos'] as $k => $v ) {
									if ( '' !== $v && 'capa' !== $k && ( 'filme' === $tipo_ativo || 'autor' !== $k ) ) {
										$escritos[] = $k . ': ' . ( mb_strlen( $v ) > 25 ? mb_substr( $v, 0, 25 ) . '…' : $v );
									}
								}
								echo esc_html( $escritos ? implode( ' · ', $escritos ) : '—' );
								?>
							</td>
							<td class="vc-col-acoes">
								<a class="button button-small" href="<?php echo esc_url( vc_acervo_url( array( 'obra' => $i ), $tipo_ativo ) ); ?>">editar</a>
								<form method="post" class="vc-remover"
									action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
									onsubmit="<?php echo esc_attr( 'return confirm(' . wp_json_encode( 'Remover "' . $o['titulo'] . '"?' ) . ');' ); ?>">
									<?php wp_nonce_field( 'vc_acervo_' . $i ); ?>
									<input type="hidden" name="action" value="vc_acervo_salvar">
									<input type="hidden" name="indice" value="<?php echo (int) $i; ?>">
									<input type="hidden" name="tipo_secao" value="<?php echo esc_attr( $tipo_ativo ); ?>">
									<input type="hidden" name="remover" value="1">
									<button type="submit" class="button button-small button-link-delete">remover</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<style>
		.vc-conta { color: #50575e; max-width: 900px; }
		.vc-busca { display: flex; align-items: center; gap: 8px; margin: 14px 0 8px; max-width: 520px; }
		.vc-painel { background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #2271b1; padding: 1px 20px 20px; margin: 18px 0; max-width: 900px; }
		.vc-painel h2 { font-size: 1rem; }
		.vc-titulo-grupo { margin: 26px 0 8px; }
		.vc-tabela .vc-col-capa { width: 60px; }
		.vc-tabela .vc-col-datas { width: 210px; }
		.vc-tabela .vc-col-campos { width: 240px; color: #646970; }
		.vc-tabela .vc-col-acoes { width: 190px; }
		.vc-tabela img { border-radius: 2px; object-fit: cover; display: block; }
		.vc-sem-capa { color: #a7aaad; }
		.vc-vezes { background: #f0f0f1; border-radius: 999px; padding: 1px 7px; font-size: 11px; margin-left: 4px; }
		.vc-quero { color: #996800; background: #fcf9e8; padding: 1px 7px; border-radius: 3px; font-size: 12px; }
		.vc-remover { display: inline; }
		.vc-capa { display: flex; align-items: center; gap: 10px; margin: 8px 0 4px; }
		.vc-capa-previa img { max-width: 56px; height: auto; display: block; border: 1px solid #dcdcde; border-radius: 2px; }
		.vc-capa-previa:empty { display: none; }
		.vc-campo-escondido { display: none; }
	</style>

	<script>
	( function () {
		var campo = document.getElementById( 'vc-campo-capa' );
		var botao = document.querySelector( '.vc-capa-escolher' );
		var previa = document.querySelector( '.vc-capa-previa' );

		if ( botao && campo ) {
			botao.addEventListener( 'click', function () {
				if ( ! window.wp || ! window.wp.media ) {
					return;
				}

				var quadro = window.wp.media( {
					title: 'imagem de capa',
					library: { type: 'image' },
					multiple: false
				} );

				quadro.on( 'select', function () {
					var anexo = quadro.state().get( 'selection' ).first().toJSON();
					campo.value = anexo.url.split( '/' ).pop();

					if ( previa ) {
						previa.innerHTML = '';
						var img = document.createElement( 'img' );
						img.src = anexo.url;
						previa.appendChild( img );
					}
				} );

				quadro.open();
			} );
		}

		var tipo = document.getElementById( 'vc-tipo' );

		if ( tipo ) {
			var ajustar = function () {
				var livro = 'livro' === tipo.value;

				Array.prototype.forEach.call( document.querySelectorAll( '.vc-linha-campo.vc-campo-livro' ), function ( linha ) {
					var entrada = linha.querySelector( 'input, textarea, select' );
					var preenchido = entrada && '' !== entrada.value;

					linha.classList.toggle( 'vc-campo-escondido', ! livro && ! preenchido );
				} );
			};

			tipo.addEventListener( 'change', ajustar );
			ajustar();
		}
	} )();
	</script>
	<?php
}

/**
 * Tela de Livros no admin.
 */
function vc_acervo_tela_livros() {
	vc_acervo_tela( 'livro' );
}

/**
 * Tela de Filmes no admin.
 */
function vc_acervo_tela_filmes() {
	vc_acervo_tela( 'filme' );
}

/* -------------------------------------------------------------------------
   9. QUEM RECEBE O POST
   ------------------------------------------------------------------------- */

/**
 * Salva, acrescenta ou remove uma obra.
 */
function vc_acervo_salvar() {
	$tipo_secao = isset( $_POST['tipo_secao'] ) ? sanitize_key( (string) $_POST['tipo_secao'] ) : 'livro';
	$id         = vc_acervo_pagina( $tipo_secao );
	$indice     = isset( $_POST['indice'] ) ? (int) $_POST['indice'] : -1;

	if ( ! $id || ! current_user_can( 'edit_post', $id ) ) {
		wp_die( esc_html__( 'Você não tem permissão para editar esta página.', 'viniciuscodes' ) );
	}

	check_admin_referer( 'vc_acervo_' . $indice );

	$html = (string) get_post_field( 'post_content', $id );
	$acao = array( 'indice' => $indice );

	if ( ! empty( $_POST['remover'] ) ) {
		$acao['remover'] = true;
	} else {
		$campos = array();

		$recebidos = isset( $_POST['campos'] ) && is_array( $_POST['campos'] ) ? wp_unslash( $_POST['campos'] ) : array();

		foreach ( vc_acervo_campos() as $nome => $info ) {
			$campos[ $nome ] = isset( $recebidos[ $nome ] ) ? trim( (string) $recebidos[ $nome ] ) : '';
		}

		$acao['obra'] = array(
			'titulo' => isset( $_POST['titulo'] ) ? wp_unslash( (string) $_POST['titulo'] ) : '',
			'tipo'   => isset( $_POST['tipo'] ) ? wp_unslash( (string) $_POST['tipo'] ) : $tipo_secao,
			'datas'  => preg_split( '~[,;]+~', isset( $_POST['datas'] ) ? wp_unslash( (string) $_POST['datas'] ) : '' ),
			'campos' => $campos,
		);
	}

	$r = vc_acervo_aplicar( $html, $acao );

	if ( '' !== $r['erro'] ) {
		wp_safe_redirect( vc_acervo_url( array( 'vc-erro' => $r['erro'] ), $tipo_secao ) );
		exit;
	}

	$g = vc_acervo_gravar( $r['html'], $id );

	if ( ! $g['ok'] ) {
		wp_safe_redirect( vc_acervo_url( array( 'vc-erro' => $g['erro'] ), $tipo_secao ) );
		exit;
	}

	$recado = ! empty( $_POST['remover'] )
		? 'Obra removida.'
		: ( -1 === $indice ? 'Obra acrescentada.' : 'Obra salva.' );

	wp_safe_redirect( vc_acervo_url( array( 'vc-aviso' => $recado ), $tipo_secao ) );
	exit;
}
add_action( 'admin_post_vc_acervo_salvar', 'vc_acervo_salvar' );
