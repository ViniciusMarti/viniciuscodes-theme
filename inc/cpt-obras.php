<?php
/**
 * O acervo como tipo de post: livro, filme, série e peça.
 *
 * POR QUE SAIU DO CONTEUDO DA PAGINA. O acervo nasceu como uma lista dentro do
 * conteudo de `/biblioteca/`, num `core/list` com 218 itens, cada um escrito à mão
 * no formato `Título | tipo | data | campo: valor`. Isso tinha duas virtudes — não
 * precisava de estrutura nenhuma e o editor de blocos já existia — e um limite que
 * apareceu quando o acervo cresceu: TUDO QUE SE QUISESSE SABER SOBRE ELE exigia
 * ler o texto inteiro e interpretar.
 *
 * Filtrar por nota, contar quantos filmes de 2024, listar o que está na estante e
 * não foi lido, procurar por autor: nada disso era consulta, era parsing. E parsing
 * dentro de um template significa que cada pergunta nova vira código novo.
 *
 * O QUE MUDA. Cada obra vira um post, e cada pedaço dela vira um campo que o banco
 * sabe consultar:
 *
 *   tipo    → taxonomia `obra_tipo`   (livro, filme, série, teatro)
 *   estado  → taxonomia `obra_status` (o que a estante diz sobre ela)
 *   capa    → imagem destacada, que é o lugar nativo disso
 *   o resto → metadados
 *
 * POR QUE TAXONOMIA PARA TIPO E ESTADO, E NAO METADADO. Taxonomia tem contagem,
 * arquivo próprio e consulta indexada; metadado precisa de `meta_query`, que é
 * mais lento e não dá contagem de graça. O estado ser taxonomia também permite que
 * ele cresça por tipo — `lido` só faz sentido para livro, `assistido` para filme —
 * sem que ninguém precise validar nada.
 *
 * O QUE NAO VIRA TAXONOMIA. Autor, nota e resumo são valores únicos por obra; uma
 * taxonomia de autor existiria para agrupar, e agrupar autor não é uma pergunta
 * que a página faz. Se um dia for, é uma linha.
 *
 * @package ViniciusCodes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registra o tipo de post e as duas taxonomias.
 */
function vc_obras_registrar() {
	register_post_type(
		'obra',
		array(
			'labels'       => array(
				'name'          => __( 'Acervo', 'viniciuscodes' ),
				'singular_name' => __( 'Obra', 'viniciuscodes' ),
				'add_new_item'  => __( 'Acrescentar obra', 'viniciuscodes' ),
				'edit_item'     => __( 'Editar obra', 'viniciuscodes' ),
				'search_items'  => __( 'Procurar no acervo', 'viniciuscodes' ),
				'not_found'     => __( 'Nada encontrado', 'viniciuscodes' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-book-alt',
			'menu_position' => 21,
			'supports'     => array( 'title', 'thumbnail', 'excerpt', 'editor', 'custom-fields' ),
			/*
			 * SEM ENDERECO PROPRIO PARA A OBRA, E A RAZAO E UM CONFLITO MEDIDO.
			 *
			 * Com `rewrite => 'acervo'`, a pagina `/livros/` passou a responder 301
			 * para `/acervo/`. O redirecionamento canonico do WordPress decide que a
			 * pagina e o endereco do tipo de post sao a mesma coisa, e manda para o
			 * segundo. Nao ha aviso, nao ha erro: a pagina simplesmente nao abre.
			 *
			 * A OBRA NAO PRECISA DE PAGINA PROPRIA. O catalogo e a grade com filtros;
			 * clicar num livro abre a Amazon, nao uma pagina interna. Criar endereco
			 * para cada uma das 218 obras so para ter um endereco seria criar 218
			 * paginas que ninguem pediu e que ainda disputariam entre si no buscador.
			 *
			 * O `query_var` CONTINUA LIGADO: sem ele, a consulta por tipo para de
			 * funcionar. O que sai e so o endereco bonito.
			 */
			'rewrite'      => false,
		)
	);

	register_taxonomy(
		'obra_tipo',
		'obra',
		array(
			'labels'            => array(
				'name'          => __( 'Tipos', 'viniciuscodes' ),
				'singular_name' => __( 'Tipo', 'viniciuscodes' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => false,
			'rewrite'           => array( 'slug' => 'obras/tipo' ),
		)
	);

	/*
	 * O GENERO E UMA DIMENSAO PROPRIA, e nao um pedaco do tipo. "Fantasia & Sci-Fi"
	 * corta os quatro tipos; "livro" corta os quatro generos. Sao perguntas
	 * diferentes, e por isso sao duas taxonomias.
	 *
	 * OS VALORES VEM DO NOTION e sao os que ja estao em uso. Criar uma lista nova,
	 * mais bonita ou mais curta, obrigaria a remapear 115 livros para ganhar nada.
	 */
	register_taxonomy(
		'obra_genero',
		'obra',
		array(
			'labels'            => array(
				'name'          => __( 'Gêneros', 'viniciuscodes' ),
				'singular_name' => __( 'Gênero', 'viniciuscodes' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => false,
			'rewrite'           => array( 'slug' => 'obras/genero' ),
		)
	);

	register_taxonomy(
		'obra_status',
		'obra',
		array(
			'labels'            => array(
				'name'          => __( 'Estados', 'viniciuscodes' ),
				'singular_name' => __( 'Estado', 'viniciuscodes' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => false,
			'rewrite'           => array( 'slug' => 'obras/estado' ),
		)
	);
}
add_action( 'init', 'vc_obras_registrar' );

/**
 * Os metadados, com o tipo de dado declarado.
 *
 * `show_in_rest` importa: sem ele os campos não aparecem no editor de blocos e a
 * obra fica só com título e capa. `single` diz que é um valor só, e não uma lista.
 *
 * O `obra_datas` é o único que guarda lista — uma obra pode ter sido vista várias
 * vezes, e o formato é o mesmo do texto antigo: datas ISO, separadas por vírgula.
 */
function vc_obras_metadados() {
	$campos = array(
		'obra_autor'    => 'string',
		'obra_nota'     => 'integer',
		'obra_resumo'   => 'string',
		'obra_amazon'   => 'string',
		'obra_resenha'  => 'string',
		'obra_datas'    => 'string',
	);

	foreach ( $campos as $nome => $tipo ) {
		register_post_meta(
			'obra',
			$nome,
			array(
				'type'          => $tipo,
				'single'        => true,
				'show_in_rest'  => true,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'vc_obras_metadados' );

/**
 * Os estados que existem, e a que tipo cada um pertence.
 *
 * A LISTA É EXPLÍCITA E PRÓPRIA, e não deduzida dos posts: um estado sem
 * nenhuma obra ainda precisa aparecer no filtro, senão a pessoa não descobre que
 * ele existe. `quero ler` numa estante vazia é uma pergunta legítima.
 *
 * @return array Chave => rótulo.
 */
function vc_obras_estados() {
	return array(
		'lendo'      => 'lendo agora',
		'quero-ler'  => 'quero ler',
		'na-estante' => 'na estante',
		'lido'       => 'lido',
		'abandonado' => 'abandonado',
		'quero-ver'  => 'quero ver',
		'assistido'  => 'assistido',
	);
}

/**
 * Os tipos, na ordem em que aparecem na página.
 *
 * @return array Chave => rótulo.
 */
function vc_obras_tipos() {
	return array(
		'livro'  => 'livros',
		'filme'  => 'filmes',
		'serie'  => 'séries',
		'teatro' => 'teatro',
	);
}

/**
 * Garante que os termos das duas taxonomias existam.
 *
 * Roda uma vez, no `init`, e não a cada carregamento: se o termo já existe, a
 * função sai antes de tocar no banco. É o que faz o filtro abrir com as opções
 * certas mesmo antes de qualquer obra ser cadastrada.
 */
function vc_obras_semear_termos() {
	if ( get_option( 'vc_obras_termos_ok' ) ) {
		return;
	}

	foreach ( vc_obras_tipos() as $chave => $rotulo ) {
		if ( ! term_exists( $chave, 'obra_tipo' ) ) {
			wp_insert_term( $rotulo, 'obra_tipo', array( 'slug' => $chave ) );
		}
	}

	foreach ( vc_obras_estados() as $chave => $rotulo ) {
		if ( ! term_exists( $chave, 'obra_status' ) ) {
			wp_insert_term( $rotulo, 'obra_status', array( 'slug' => $chave ) );
		}
	}

	foreach ( vc_obras_generos() as $rotulo ) {
		$slug = sanitize_title( $rotulo );
		if ( ! term_exists( $slug, 'obra_genero' ) ) {
			wp_insert_term( $rotulo, 'obra_genero', array( 'slug' => $slug ) );
		}
	}

	update_option( 'vc_obras_termos_ok', 1 );
}
add_action( 'admin_init', 'vc_obras_semear_termos' );
add_action( 'init', 'vc_obras_semear_termos' );

/**
 * As paginas do catalogo nao se redirecionam.
 *
 * O DEFEITO, medido: `/livros/` respondia 301 para `/acervo/`, e `/acervo/`
 * respondia 301 para `/biblioteca/`. A pagina existia, a consulta do WordPress a
 * encontrava, e mesmo assim o visitante nunca chegava nela — um ciclo de
 * redirecionamento entre uma pagina real e o endereco de uma taxonomia.
 *
 * A CAUSA E O `redirect_canonical`, que roda depois de a consulta resolver e
 * decide se o endereco pedido e o "certo". Quando existe um termo ou uma base de
 * taxonomia com o mesmo nome de uma pagina, ele pode preferir o segundo — e nao ha
 * erro, nao ha aviso, so um 301.
 *
 * A PAGINA E O ENDERECO CERTO, e por isso o filtro e seguro: `/livros/` e o
 * endereco canonico da pagina `livros`, e nao ha outro lugar para onde mandar
 * quem chega nela. O filtro so age nessas quatro paginas; o resto do site continua
 * com o comportamento normal do WordPress.
 *
 * @param string|false $redirecionar URL de destino, ou false.
 * @return string|false
 */
function vc_catalogo_sem_redirect( $redirecionar, $pedido = '' ) {
	/*
	 * DECIDE PELO CAMINHO, E NAO PELO CONTEXTO. A versao anterior perguntava
	 * `is_page()`, e no instante do redirecionamento esse contexto ainda nao estava
	 * montado — o filtro era chamado, a resposta era "nao e uma dessas paginas", e
	 * o 301 seguia. O caminho pedido nao depende de nada estar pronto.
	 */
	$caminho = trim( (string) wp_parse_url( $pedido, PHP_URL_PATH ), '/' );

	if ( in_array( $caminho, array( 'livros', 'filmes', 'series', 'teatro' ), true ) ) {
		return false;
	}

	if ( is_page( array( 'livros', 'filmes', 'series', 'teatro' ) ) ) {
		return false;
	}

	return $redirecionar;
}
add_filter( 'redirect_canonical', 'vc_catalogo_sem_redirect', 10, 2 );

/**
 * Os quatro enderecos do acervo antigo levam para a aba certa da biblioteca.
 *
 * ISTO EXISTE PORQUE A OPTION NAO E CONFIavel. As regras de redirecionamento moram
 * numa option que o proprio mu-plugin reescreve sozinho — e ele ja sobrescreveu
 * estas quatro uma vez, mandando-as para `/posts/` sem aviso. Um endereco que
 * estava indexado nao pode depender de quem escreveu na option por ultimo.
 *
 * A LISTA E FECHADA E O CAMINHO E EXATO. Nao ha prefixo, nao ha curinga, nao ha
 * expressao regular: sao quatro caminhos conhecidos, comparados um a um. Qualquer
 * outro endereco do site passa direto.
 */
function vc_atalho_biblioteca() {
	$caminho = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	$caminho = strtolower( $caminho );

	$atalhos = array(
		'livros' => 'livro',
		'filmes' => 'filme',
		'series' => 'serie',
		'teatro' => 'teatro',
	);

	if ( ! isset( $atalhos[ $caminho ] ) ) {
		return;
	}

	wp_safe_redirect( home_url( '/biblioteca/?tipo=' . $atalhos[ $caminho ] ), 301 );
	exit;
}
add_action( 'template_redirect', 'vc_atalho_biblioteca', 1 );

/**
 * Os generos em uso, como o Notion os nomeia.
 *
 * A LISTA E FECHADA E VEM DE FORA, e isso e de proposito: ela descreve um acervo
 * que ja existe, com 115 livros classificados. Um genero novo entra aqui quando
 * aparecer um livro que nao caiba em nenhum — nao antes.
 *
 * @return array Rotulos.
 */
function vc_obras_generos() {
	return array(
		'thriller e suspense',
		'ficção contemporânea e lgbtqia+',
		'fantasia e sci-fi',
		'clássicos e filosofia',
		'neurodivergência e psicologia',
		'engenharia de dados e negócios',
	);
}

/**
 * Mantem `obra_ultima_data` em dia a cada salvamento.
 *
 * O CAMPO E DERIVADO, E DERIVADO NAO SE DIGITA. Ele existe porque a ordenacao por
 * data precisa de um valor unico por obra, e `obra_datas` e uma lista. Como ele
 * nao se preenche sozinho, toda obra criada fora da migracao nasce sem ele — e
 * como a ordenacao por `meta_key` faz juncao interna, nascer sem ele significa
 * nao aparecer na lista. Ja aconteceu duas vezes: com as 109 obras sem data e com
 * os 97 livros da importacao do Notion.
 *
 * ESTE GANCHO E A CORRECAO DEFINITIVA. A partir daqui, salvar uma obra recalcula o
 * campo. Nao resolve o que ja esta no banco — isso foi feito acima — mas resolve
 * tudo o que vier depois, inclusive as proximas importacoes.
 *
 * @param int $post_id ID da obra.
 */
function vc_obras_atualizar_data( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'obra' !== get_post_type( $post_id ) ) {
		return;
	}

	$datas = array_filter( explode( ',', (string) get_post_meta( $post_id, 'obra_datas', true ) ) );

	if ( $datas ) {
		sort( $datas );
		update_post_meta( $post_id, 'obra_ultima_data', end( $datas ) );
	} else {
		update_post_meta( $post_id, 'obra_ultima_data', '' );
	}
}
add_action( 'save_post_obra', 'vc_obras_atualizar_data' );
