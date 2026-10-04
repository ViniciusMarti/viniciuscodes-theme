<?php
/**
 * Notas: o post curto, sem título, que mora entre o texto e o tweet.
 *
 * POR QUE UM POST TYPE, E NÃO UM POST FORMAT.
 *
 * Um formato de post (`aside`, `status`) muda a apresentação de um post que
 * continua sendo um post: ele cai na mesma lista, no mesmo feed, no mesmo
 * arquivo — e o `/posts/` deste site existe para textos longos. Quem chega ali
 * está escolhendo o que ler; uma nota de duas linhas no meio de sete textos
 * muda a natureza da lista sem que ninguém tenha pedido.
 *
 * O post type separa as duas coisas onde elas são de fato diferentes: no
 * banco, no menu do admin, na URL (`/notas/`) e no feed. O que ele NÃO separa
 * é a aparência: as notas reusam `.post-list`, `.post-item` e `.prose`,
 * porque é o mesmo site.
 *
 * SEM TÍTULO — E ISSO É O PONTO.
 *
 * `supports` não declara `title` de propósito. O WordPress exige um título
 * para gravar, e o RSS precisa de um; quem escreve uma nota, não. O título é
 * gerado em `wp_insert_post_data` (abaixo) e NÃO APARECE NO CORPO DO SITE: na
 * linha do tempo e na página da nota, quem identifica é a data.
 *
 * O QUE SOBRA DELE, E POR QUE ISSO ESTÁ CERTO. Medido no site depois de subir:
 * o título aparece em quatro lugares, e os quatro são metadados — `<title>`,
 * `og:title`, `og:image:alt` e `twitter:title`. Um `<title>` não pode ser
 * vazio, e o texto da nota já vai em `og:description`, que é o campo que
 * importa numa prévia de rede social. Nenhum dos quatro é lido por parser de
 * microformato: o `h-entry` continua sem `p-name`, e é isso que impede a data
 * de ser lida como o título da nota.
 *
 * Isso também é o que faz o plugin ActivityPub federar a nota como `Note`.
 * `Transformer\Post::get_type()` responde `Note` quando o post type não
 * declara suporte a `title` (ou quando o título está vazio), e `get_name()`
 * devolve `null` para `Note` — nenhum `name` inventado no JSON que sai para o
 * Fediverso. É a diferença entre uma nota federada e um "artigo" de 200
 * caracteres com data no título.
 *
 * @package ViniciusCodes
 */

defined( 'ABSPATH' ) || exit;

/**
 * O slug do post type.
 *
 * Uma constante, e não a string solta, porque ele aparece no registro, nas
 * regras de rewrite, nas colunas do admin e nas consultas de feed. No dia em
 * que mudar, muda em um lugar.
 */
const VC_NOTA = 'nota';

/**
 * Versão da estrutura de URLs das notas.
 *
 * Subir este número força um flush das regras de rewrite na próxima ida ao
 * admin. É o que evita o 404 de `/notas/` sem ninguém precisar lembrar de
 * abrir "Configurações → Links permanentes" depois de subir os arquivos.
 */
const VC_NOTA_VERSAO = '1';

/**
 * A opção que guarda o PRÓXIMO número de nota a ser usado.
 *
 * O número é gravado, e não contado na hora, por um motivo concreto: contar
 * quantas notas existem antes mudaria o número de TODAS as notas antigas no dia
 * em que alguém publicasse uma nota com data retroativa. Identificador que se
 * move não é identificador. Com o contador, `#1` é a primeira nota e continua
 * sendo `#1` para sempre.
 *
 * O efeito colateral é honesto e conhecido: apagar uma nota deixa um buraco na
 * sequência (`#1`, `#3`), como o número de uma issue.
 */
const VC_NOTA_CONTADOR = 'vc_notas_contador';

/** A meta onde o número de cada nota fica guardado. */
const VC_NOTA_META_NUMERO = '_vc_nota_numero';

/**
 * Registra o post type.
 *
 * Roda em `init` 9: o núcleo registra `category` e `post_tag` em `init` 0,
 * então `taxonomies` já encontra as duas para amarrar. (O tema chama a
 * categoria de "tema" na URL — ver o comentário do `archive.php` do tema.)
 */
function vc_notas_registrar() {
	register_post_type(
		VC_NOTA,
		array(
			'labels'                => array(
				'name'               => __( 'Notas', 'viniciuscodes' ),
				'singular_name'      => __( 'Nota', 'viniciuscodes' ),
				'menu_name'          => __( 'Notas', 'viniciuscodes' ),
				'add_new'            => __( 'Nova nota', 'viniciuscodes' ),
				'add_new_item'       => __( 'Nova nota', 'viniciuscodes' ),
				'edit_item'          => __( 'Editar nota', 'viniciuscodes' ),
				'new_item'           => __( 'Nova nota', 'viniciuscodes' ),
				'view_item'          => __( 'Ver nota', 'viniciuscodes' ),
				'view_items'         => __( 'Ver notas', 'viniciuscodes' ),
				'search_items'       => __( 'Buscar notas', 'viniciuscodes' ),
				'not_found'          => __( 'Nenhuma nota encontrada.', 'viniciuscodes' ),
				'not_found_in_trash' => __( 'Nenhuma nota na lixeira.', 'viniciuscodes' ),
				'all_items'          => __( 'Todas as notas', 'viniciuscodes' ),
				'archives'           => __( 'Arquivo de notas', 'viniciuscodes' ),
				'item_published'     => __( 'Nota publicada.', 'viniciuscodes' ),
				'item_updated'       => __( 'Nota atualizada.', 'viniciuscodes' ),
				'items_list'         => __( 'Lista de notas', 'viniciuscodes' ),
			),
			'description'           => __( 'Posts curtos, sem título, feitos para a linha do tempo e para o Fediverso.', 'viniciuscodes' ),

			// Público na URL e na REST. `show_in_rest` é o que mantém o
			// editor de blocos e um `POST /wp/v2/notas` funcionando.
			'public'                => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_nav_menus'     => false,
			'show_in_admin_bar'     => true,
			'show_in_rest'          => true,
			'rest_base'             => 'notas',
			'rest_controller_class' => 'WP_REST_Posts_Controller',

			'menu_position'         => 6,
			'menu_icon'             => 'dashicons-format-status',
			'hierarchical'          => false,

			// `/notas/` para a linha do tempo e `/notas/<slug>/` para cada
			// nota. `with_front => false` tira o prefixo do blog da URL.
			'has_archive'           => 'notas',
			'rewrite'               => array(
				'slug'       => 'notas',
				'with_front' => false,
			),
			'query_var'             => true,

			'exclude_from_search'   => false,
			'can_export'            => true,
			'delete_with_user'      => false,
			'capability_type'       => 'post',
			'map_meta_cap'          => true,

			/*
			 * `title` NÃO está nesta lista, e é o coração do recurso: ver o
			 * comentário no topo do arquivo. E `comments` TAMBÉM NÃO ESTÁ: nota
			 * não recebe comentário — sem o suporte, o editor nem desenha a caixa
			 * de discussão. `revisions` está porque uma nota errada é uma nota
			 * que precisa voltar atrás, e o custo é uma linha por edição.
			 */
			'supports'              => array( 'editor', 'author', 'revisions' ),

			// A nota entra na categoria (que o site chama de tema) e na
			// etiqueta. Sem isto, `/tema/marketing/` ficaria só com os textos.
			'taxonomies'            => array( 'category', 'post_tag' ),
		)
	);
}
add_action( 'init', 'vc_notas_registrar', 9 );

/**
 * Amarra uma taxonomia própria de tema, se ela existir.
 *
 * O tema chama a CATEGORIA de "tema" na URL (`/tema/marketing/`) — é o que o
 * comentário do `archive.php` registra —, e é por isso que `category` está no
 * registro acima. Este laço cobre o outro caso, o de uma taxonomia própria
 * chamada literalmente `tema` ou `temas`: se ela existir, a nota entra nela
 * também; se não existir, não acontece nada. Custar uma consulta a menos não
 * era o problema aqui — o problema era o recurso quebrar no dia em que a
 * taxonomia mudasse de nome.
 */
function vc_notas_amarrar_taxonomias() {
	foreach ( array( 'tema', 'temas' ) as $vc_taxonomia ) {
		if ( taxonomy_exists( $vc_taxonomia ) ) {
			register_taxonomy_for_object_type( $vc_taxonomia, VC_NOTA );
		}
	}
}
add_action( 'init', 'vc_notas_amarrar_taxonomias', 11 );

/**
 * Gera o título interno de uma nota.
 *
 * O WordPress não grava post sem título. `wp_insert_post_data` é o último
 * ponto antes do INSERT e o único onde o título pode ser preenchido sem que
 * ninguém veja o campo.
 *
 * QUANDO ELE NÃO GERA. Só quando os DOIS títulos estão vazios: o que veio do
 * formulário e o que já está gravado. Sem a segunda checagem, toda edição
 * reescreveria o título e, com ele, o slug — uma nota publicada ontem mudaria
 * de endereço porque alguém corrigiu uma vírgula hoje. Título que já existe
 * (digitado à mão, importado, gerado por este mesmo filtro) manda.
 *
 * A DATA É A DO POST, NÃO A DE AGORA. Um rascunho salvo hoje e publicado
 * semana que vem precisa do título da publicação. `mysql2date()` formata a
 * data local do post sem conversão de fuso; o caminho mais óbvio,
 * `strtotime()` + `date_i18n()`, somaria o offset do site duas vezes e
 * publicaria a nota três horas no futuro.
 *
 * @param array $dados   Os dados já sanitizados que vão para o banco.
 * @param array $postarr O post como veio do formulário.
 * @return array Os dados, com título e slug preenchidos quando faltavam.
 */
function vc_notas_titulo_automatico( $dados, $postarr ) {
	if ( VC_NOTA !== ( $dados['post_type'] ?? '' ) ) {
		return $dados;
	}

	$vc_id = isset( $postarr['ID'] ) ? (int) $postarr['ID'] : 0;

	if ( $vc_id && '' !== trim( (string) get_post_field( 'post_title', $vc_id ) ) ) {
		return $dados;
	}

	if ( '' !== trim( (string) ( $dados['post_title'] ?? '' ) ) ) {
		return $dados;
	}

	$vc_quando = ! empty( $dados['post_date'] ) ? (string) $dados['post_date'] : current_time( 'mysql' );

	$dados['post_title'] = sprintf(
		/* translators: %s: data e hora da nota, ex.: "4 de outubro de 2026, 14:30". */
		__( 'Nota — %s', 'viniciuscodes' ),
		mysql2date( 'j \d\e F \d\e Y, H:i', $vc_quando )
	);

	/*
	 * O slug não pode nascer do título: ele tem acento, espaço, vírgula e um
	 * travessão. Uma nota é identificada pela data, então o endereço também é
	 * — curto, ordenável e sem nada para escapar:
	 * /notas/nota-20261004-1430/
	 */
	if ( empty( $dados['post_name'] ) ) {
		$dados['post_name'] = 'nota-' . mysql2date( 'Ymd-Hi', $vc_quando );
	}

	return $dados;
}
add_filter( 'wp_insert_post_data', 'vc_notas_titulo_automatico', 10, 2 );

/**
 * Fediverso: ActivityPub e Webmention.
 *
 * Nenhum dos dois plugins descobre um post type sozinho. Cada um declara
 * suporte por tipo, com `add_post_type_support( $post_type, $feature )` — é o
 * caminho que a própria documentação de integração do ActivityPub recomenda,
 * e é do núcleo, então funciona com os dois plugins desligados: a flag fica
 * guardada e passa a valer no dia em que o plugin for ativado.
 */
function vc_notas_fediverso() {
	add_post_type_support( VC_NOTA, 'activitypub' );
	add_post_type_support( VC_NOTA, 'webmentions' );
}
add_action( 'init', 'vc_notas_fediverso', 12 );

/**
 * Mantém a caixinha do painel de acordo com o que o site faz.
 *
 * A tela de configuração do ActivityPub (e a do Webmention) desenha as
 * caixinhas a partir da opção `*_support_post_types`. Sem este filtro, a
 * caixinha "Notas" apareceria DESMARCADA enquanto as notas federavam de
 * verdade: a tela diria uma coisa e o site faria outra, que é a pior versão
 * de um recurso invisível.
 *
 * O EFEITO COLATERAL É REAL E ESTÁ AQUI ESCRITO: com o filtro, desmarcar a
 * caixinha no painel não desliga a federação das notas. Para desligar, remova
 * os dois `add_filter` abaixo — a partir daí a caixinha volta a mandar
 * sozinha.
 *
 * @param mixed $lista A lista de post types guardada na opção.
 * @return mixed A lista, com `nota` dentro quando ela é um array.
 */
function vc_notas_incluir_no_fediverso( $lista ) {
	if ( ! is_array( $lista ) ) {
		return $lista;
	}

	if ( ! in_array( VC_NOTA, $lista, true ) ) {
		$lista[] = VC_NOTA;
	}

	return $lista;
}
add_filter( 'option_activitypub_support_post_types', 'vc_notas_incluir_no_fediverso' );
add_filter( 'option_webmention_support_post_types', 'vc_notas_incluir_no_fediverso' );

/**
 * Dá um número à nota — uma vez só, na primeira vez que ela é publicada.
 *
 * O NÚMERO É UM IDENTIFICADOR, então ele é gravado e nunca recalculado: ver o
 * comentário do `VC_NOTA_CONTADOR`. A consequência de gravar é que a ordem de
 * publicação é a ordem das chamadas — uma nota publicada hoje com data de dois
 * meses atrás leva o número de hoje. É o comportamento certo para um
 * identificador e o errado para uma data, e é de propósito: a data já está na
 * nota, e o número não precisa concordar com ela.
 *
 * POR QUE `save_post_` E NÃO `transition_post_status`: rascunho não gasta
 * número da fila. Quando o rascunho vira publicado, o `save_post` roda de novo
 * e o número é dado ali; a checagem da meta garante que a segunda gravação não
 * dê um número novo.
 *
 * @param int $post_id O post que acabou de ser gravado.
 */
function vc_notas_numerar( $post_id ) {
	$post_id = (int) $post_id;

	if ( VC_NOTA !== get_post_type( $post_id ) ) {
		return;
	}

	if ( 'publish' !== get_post_status( $post_id ) ) {
		return;
	}

	if ( '' !== (string) get_post_meta( $post_id, VC_NOTA_META_NUMERO, true ) ) {
		return;
	}

	$vc_numero = max( 1, (int) get_option( VC_NOTA_CONTADOR, 1 ) );

	/*
	 * O contador é incrementado ANTES de o número ir para o post. Se algo
	 * falhar no meio, o resultado é um número furado — visível e inofensivo —,
	 * e nunca duas notas com o mesmo número, que é o erro que estraga o
	 * identificador.
	 */
	update_option( VC_NOTA_CONTADOR, $vc_numero + 1 );
	update_post_meta( $post_id, VC_NOTA_META_NUMERO, $vc_numero );
}
add_action( 'save_post_' . VC_NOTA, 'vc_notas_numerar' );

/**
 * O número de uma nota, ou 0 quando ela ainda não tem.
 *
 * Zero é resposta legítima: nota gravada antes deste recurso existir, ou por um
 * importador que escreveu direto na tabela. Quem chama decide o que fazer com o
 * zero — os templates, por ora, simplesmente não imprimem o número. Uma nota
 * antiga ganha o número na primeira vez que for salva de novo.
 *
 * @param int|WP_Post|null $vc_post O post (ou o ID), ou null para o atual.
 * @return int O número, ou 0.
 */
function vc_nota_numero( $vc_post = null ) {
	$vc_post = get_post( $vc_post );

	if ( ! $vc_post ) {
		return 0;
	}

	return (int) get_post_meta( $vc_post->ID, VC_NOTA_META_NUMERO, true );
}

/**
 * Nota nasce com os comentários fechados.
 *
 * `supports` sem `comments` esconde a caixa no editor, mas não impede um
 * `wp_insert_post()` de gravar `comment_status = 'open'` — que é o padrão do
 * site. Este filtro fecha na porta de entrada, então nem o editor, nem a REST
 * API, nem um script criam uma nota aberta a comentário.
 *
 * O `ping_status` fica de fora DE PROPÓSITO: fechá-lo é o caminho mais curto
 * para atrapalhar a recepção de Webmention, que é um recurso do site.
 * Comentário local e webmention são coisas diferentes, e só o primeiro foi
 * desligado.
 *
 * @param array $dados   Os dados que vão para o banco.
 * @param array $postarr O post como veio do formulário.
 * @return array Os dados, com o comentário fechado.
 */
function vc_notas_fechar_comentarios( $dados, $postarr ) {
	if ( VC_NOTA !== ( $dados['post_type'] ?? '' ) ) {
		return $dados;
	}

	$dados['comment_status'] = 'closed';

	return $dados;
}
add_filter( 'wp_insert_post_data', 'vc_notas_fechar_comentarios', 20, 2 );

/**
 * E que continue fechada — inclusive nas notas que já existiam.
 *
 * O filtro acima só age na gravação, então uma nota publicada antes desta regra
 * continuaria com `comment_status = 'open'` no banco e o formulário de
 * comentário voltaria a aparecer. `comments_open` é o que o tema consulta para
 * desenhar (ou não) a caixa, e é aqui que a regra vale para todas.
 *
 * Uma consequência que vale saber: com os comentários fechados, o
 * `comments_template()` do tema só desenha a lista se JÁ existirem comentários.
 * É exatamente o que se quer das respostas do Fediverso — ActivityPub e
 * Webmention as gravam como comentário, então elas continuam aparecendo. O que
 * deixou de existir é o formulário para escrever uma nova.
 *
 * @param bool $aberto  Se os comentários estão abertos.
 * @param int  $post_id O post em questão.
 * @return bool Falso para nota, o valor original para todo o resto.
 */
function vc_notas_comentarios_fechados( $aberto, $post_id ) {
	return ( VC_NOTA === get_post_type( $post_id ) ) ? false : $aberto;
}
add_filter( 'comments_open', 'vc_notas_comentarios_fechados', 10, 2 );

/**
 * Flush de rewrite, uma vez por versão.
 *
 * `register_post_type()` não pode chamar `flush_rewrite_rules()` por conta
 * própria: o `init` roda em toda requisição e o flush é caro. Sem o flush, as
 * regras de `/notas/` não existem e o endereço devolve 404 até alguém abrir
 * "Configurações → Links permanentes" e salvar — o passo que ninguém lembra.
 *
 * Aqui ele acontece uma única vez: na primeira ida ao admin depois que a
 * versão muda. `false` é o flush leve, que só descarta as regras guardadas e
 * deixa o WordPress regerá-las; não escreve no `.htaccess`, então funciona em
 * hospedagem onde o arquivo é somente leitura.
 */
function vc_notas_flush_pendente() {
	if ( ! is_admin() || get_option( 'vc_notas_rewrite_versao' ) === VC_NOTA_VERSAO ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'vc_notas_rewrite_versao', VC_NOTA_VERSAO );
}
add_action( 'init', 'vc_notas_flush_pendente', 20 );
