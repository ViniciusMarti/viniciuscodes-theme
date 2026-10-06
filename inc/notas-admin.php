<?php
/**
 * O admin das notas: publicar sem passar pelo cerimonial de um texto.
 *
 * O QUE ESTE ARQUIVO RESOLVE. `supports` sem `title` já tira o campo de título
 * do editor de blocos — o componente `PostTitle` do Gutenberg está embrulhado
 * num `PostTypeSupportCheck supportKeys="title"`, então ele nem é montado.
 * O que sobra é o resto da liturgia: painéis, categorias, imagem destacada,
 * resumo, e uma tela de "novo post" com um botão de publicar que só está
 * disponível quando a pessoa quiser. Para uma nota de duas linhas, isso é
 * mais decisão do que a nota inteira.
 *
 * São três coisas, e as três convivem com o editor completo:
 *
 * 1. "Publicação rápida" — uma caixa de texto e um botão, no menu Notas.
 * 2. Um bloco no painel — a mesma caixa, sem nem precisar navegar.
 * 3. A lista de Notas enxuta — conteúdo, data, interações. Sem coluna de
 *    título, que nunca teria nada de útil para mostrar.
 *
 * @package ViniciusCodes
 */

defined( 'ABSPATH' ) || exit;

// O `nota` é definido em `cpt-notas.php`: um arquivo que depende de outro
// pede o outro, em vez de confiar na ordem em que o `functions.php` chamou.
require_once __DIR__ . '/cpt-notas.php';

/* -------------------------------------------------------------------------
 * 1. A tela de publicação rápida
 * ---------------------------------------------------------------------- */

/**
 * Registra "Publicação rápida" debaixo do menu Notas.
 */
function vc_notas_menu_rapido() {
	add_submenu_page(
		'edit.php?post_type=' . VC_NOTA,
		__( 'Publicação rápida', 'viniciuscodes' ),
		__( 'Publicação rápida', 'viniciuscodes' ),
		'edit_posts',
		'vc-nota-rapida',
		'vc_notas_tela_rapida'
	);
}
add_action( 'admin_menu', 'vc_notas_menu_rapido' );
/**
 * Carrega a folha e o script DA TELA, e só dela.
 *
 * O `hook` é montado como `notas_page_vc-nota-rapida`: o WordPress forma esse
 * nome juntando o `post_type` do menu pai com o `slug` da página. É o que faz o
 * CSS sair apenas aqui, em vez de pesar em todas as telas do painel.
 */
function vc_notas_recursos_rapidos( string $hook ) {
	if ( 'notas_page_vc-nota-rapida' !== $hook ) {
		return;
	}

	$base = get_template_directory();
	$uri  = get_template_directory_uri();

	wp_enqueue_style(
		'vc-nota-rapida',
		$uri . '/assets/nota-rapida.css',
		array(),
		(string) filemtime( $base . '/assets/nota-rapida.css' )
	);

	wp_enqueue_script(
		'vc-nota-rapida',
		$uri . '/assets/nota-rapida.js',
		array(),
		(string) filemtime( $base . '/assets/nota-rapida.js' ),
		true
	);
}
add_action( 'admin_enqueue_scripts', 'vc_notas_recursos_rapidos' );

/**
 * A caixa de status: um campo, os temas, e dois botões.
 *
 * Nada além disso. O que não está aqui é a decisão: quem quiser slug, imagem
 * destacada ou agendamento usa "abrir o editor completo", que continua a um
 * clique de distância — a tela rápida não substitui o editor, ela evita ter
 * que passar por ele.
 */
function vc_notas_tela_rapida() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Você não tem permissão para publicar notas.', 'viniciuscodes' ) );
	}
	?>
	<div class="wrap vc-nota-rapida">
		<h1 class="vc-nr__titulo"><?php esc_html_e( 'Publicação rápida', 'viniciuscodes' ); ?></h1>

		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="vc-nr__form">
			<input type="hidden" name="action" value="vc_nota_publicar" />
			<input type="hidden" name="vc_redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=' . VC_NOTA . '&page=vc-nota-rapida' ) ); ?>" />
			<?php wp_nonce_field( 'vc_nota_publicar' ); ?>

			<?php
			/*
			 * A CAIXA E A BARRA ANDAM JUNTAS, e o `data-vc-nr` é o que as liga: o
			 * script procura a barra pelo ancestral comum. Sem esse embrulho, dois
			 * campos na mesma tela exigiriam dois IDs e um mapa de qual botão mexe
			 * em qual caixa.
			 *
			 * O `<textarea>` NÃO TEM `required`, e continua sem: uma nota pode ser
			 * só uma imagem, e o `required` do navegador barraria o envio antes de
			 * a checagem do servidor opinar. A regra de verdade — "tem texto ou tem
			 * imagem" — mora no tratador, onde não depende do navegador.
			 */
			?>
			<div class="vc-nr" data-vc-nr>
				<div class="vc-nr__barra" role="toolbar" aria-label="<?php echo esc_attr__( 'Formatação', 'viniciuscodes' ); ?>">
					<button type="button" class="vc-nr__bt" data-vc-nr-acao="negrito" aria-label="<?php echo esc_attr__( 'Negrito', 'viniciuscodes' ); ?>"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 4h6.5a4 4 0 0 1 0 8H7z"/><path d="M7 12h7.5a4 4 0 0 1 0 8H7z"/></svg></button>
					<button type="button" class="vc-nr__bt" data-vc-nr-acao="italico" aria-label="<?php echo esc_attr__( 'Itálico', 'viniciuscodes' ); ?>"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 4h-8"/><path d="M13 20H5"/><path d="m15 4-6 16"/></svg></button>
					<button type="button" class="vc-nr__bt" data-vc-nr-acao="riscado" aria-label="<?php echo esc_attr__( 'Riscado', 'viniciuscodes' ); ?>"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 12h15"/><path d="M17 7.5A3.6 3.6 0 0 0 13.4 5h-2.2A3.2 3.2 0 0 0 10 11.2"/><path d="M7 16.5A3.6 3.6 0 0 0 10.6 19h2.2a3.2 3.2 0 0 0 1.2-6.2"/></svg></button>
					<button type="button" class="vc-nr__bt" data-vc-nr-acao="codigo" aria-label="<?php echo esc_attr__( 'Código', 'viniciuscodes' ); ?>"><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16 18 6-6-6-6"/><path d="m8 6-6 6 6 6"/></svg></button>
					<span class="vc-nr__vao"></span>
					<button type="button" class="vc-nr__bt" data-vc-nr-acao="link" aria-label="<?php echo esc_attr__( 'Link', 'viniciuscodes' ); ?>">
						<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg>
					</button>
					<button type="button" class="vc-nr__bt" data-vc-nr-acao="limpar" aria-label="<?php echo esc_attr__( 'Tirar a formatação', 'viniciuscodes' ); ?>">
						<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16"/><path d="M10 7 8 19"/><path d="m14 13 6 6"/><path d="m20 13-6 6"/></svg>
					</button>
				</div>

				<label class="screen-reader-text" for="vc-nota-conteudo"><?php esc_html_e( 'O que você quer dizer', 'viniciuscodes' ); ?></label>
				<textarea id="vc-nota-conteudo" name="vc_nota_conteudo" rows="6" class="vc-nr__caixa" placeholder="<?php echo esc_attr__( 'o que você quer dizer…', 'viniciuscodes' ); ?>"></textarea>

				<p class="vc-nr__dica" data-vc-nr-dica>
					<?php esc_html_e( 'Enter duas vezes separa parágrafos. Endereço colado vira link sozinho.', 'viniciuscodes' ); ?>
				</p>
			</div>

			<?php
			/*
			 * O CAMPO DE ARQUIVO NATIVO VAI ESCONDIDO, e não é capricho: no celular
			 * ele sai como uma caixa cinza com "Escolher arquivo / Nenhum arquivo
			 * escolhido", sem área de toque confortável e sem dizer o que aconteceu
			 * depois. Aqui quem aparece é a etiqueta, grande e inteira clicável, e o
			 * script escreve o nome do arquivo ao lado — a única confirmação que a
			 * pessoa tem antes de publicar.
			 */
			?>
			<div class="vc-nr__imagem" data-vc-nr-imagem>
				<input type="file" id="vc-nota-imagem" name="vc_nota_imagem" accept="image/*" class="vc-nr__arquivo" />
				<label for="vc-nota-imagem" class="vc-nr__escolher">
					<svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-4.5-4.5L7 20"/></svg>
					<span><?php esc_html_e( 'adicionar imagem', 'viniciuscodes' ); ?></span>
				</label>
				<span class="vc-nr__nome" data-vc-nr-nome></span>
			</div>

			<p class="vc-nr__nota">
				<?php esc_html_e( 'A imagem entra no corpo da nota, depois do texto. Pode ser só a imagem, sem texto. Uma por nota: mais do que isso pede o editor completo.', 'viniciuscodes' ); ?>
			</p>

			<?php
			/*
			 * A BARRA FICA FIXA NO RODAPÉ NO CELULAR, por CSS. Numa nota longa, o
			 * botão de publicar ficava a uma rolagem inteira de distância de onde a
			 * pessoa está olhando. Fixa, ela acompanha.
			 */
			?>
			<div class="vc-nr__acoes">
				<button type="submit" name="vc_nota_status" value="publish" class="vc-nr__publicar">
					<?php esc_html_e( 'publicar', 'viniciuscodes' ); ?>
				</button>
				<button type="submit" name="vc_nota_status" value="draft" class="vc-nr__rascunho">
					<?php esc_html_e( 'rascunho', 'viniciuscodes' ); ?>
				</button>
				<a class="vc-nr__completo" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . VC_NOTA ) ); ?>">
					<?php esc_html_e( 'editor completo', 'viniciuscodes' ); ?>
				</a>
			</div>
		</form>
	</div>
	<?php
}
/**
 * O corpo de uma nota como BLOCOS, e não como texto clássico.
 *
 * POR QUE ISTO EXISTE. Esta tela gravava o texto puro e um `<img>` solto. O site
 * mostrava a nota, mas o conteúdo não era o do padrão do tema — o editor abria a
 * nota como "bloco clássico", a imagem saía sem `.wp-block-image` (e portanto sem
 * as regras de bloco do tema) e o mesmo texto aparecia de dois jeitos na linha do
 * tempo e na página da nota, já que os dois leem o mesmo `the_content()`.
 *
 * Medido antes de escrever isto: das quatro notas do site, três estavam em bloco
 * e uma — a que nasceu aqui — estava em texto clássico.
 *
 * A FORMA NÃO É INVENTADA. O markup abaixo é o mesmo que o editor grava neste
 * site, copiado de um bloco de imagem que ele mesmo escreveu:
 *
 *     <!-- wp:image {"id":N,"sizeSlug":"large","linkDestination":"none"} -->
 *     <figure class="wp-block-image size-large"><img src="…" alt="" class="wp-image-N"/></figure>
 *     <!-- /wp:image -->
 *
 * Repare no que NÃO vai: `srcset`, `width`, `height` e `loading`. Eles não são
 * trabalho da gravação — quem os acrescenta é o `wp_filter_content_tags()`, na
 * hora de renderizar, para os três lugares de uma vez (linha do tempo, página da
 * nota e feed). Gravar o `srcset` aqui seria congelar no banco o que o WordPress
 * calcula a cada leitura.
 *
 * E a saída passa por `serialize_blocks( parse_blocks( … ) )` de propósito: é
 * assim que o texto sai na forma canônica do editor. Sem esse passo, o editor
 * abriria a nota dizendo que o bloco "contém conteúdo inesperado" — erro que não
 * aparece no site, só no painel.
 *
 * UMA LINHA EM BRANCO separa parágrafos; uma quebra de linha sozinha vira `<br>`
 * dentro do mesmo parágrafo. É a mesma leitura que o editor faz.
 *
 * @param string $texto  Texto digitado na caixa.
 * @param int    $imagem ID do anexo, ou 0.
 * @return string Conteúdo pronto para o `post_content`.
 */
/**
 * Converte os atalhos de escrita em HTML.
 *
 * A ORDEM DAS TROCAS É O QUE FAZ A FUNÇÃO FUNCIONAR. Cada regra é aplicada sobre
 * o resultado da anterior, então uma marcação pode estragar a seguinte se entrar
 * na hora errada:
 *
 * - CRASE VEM PRIMEIRO, e o conteúdo dela é protegido com um marcador. Sem isso,
 *   `**assim**` dentro de um trecho de código viraria negrito.
 * - LINK ANTES DE ÊNFASE, senão o `*` de um endereço com asterisco seria lido
 *   como itálico.
 * - NEGRITO ANTES DE ITÁLICO, porque `**` é dois asteriscos: se o itálico viesse
 *   primeiro, ele casaria com o primeiro par e sobraria um asterisco solto.
 * - ENDEREÇO SOLTO POR ÚLTIMO, porque `make_clickable()` não deve ter chance de
 *   entrar no meio de uma tag que acabou de ser montada.
 *
 * O TEXTO NÃO É ESCAPADO AQUI, e é de propósito: esta tela já aceitava HTML antes
 * desta mudança, e quem escreve aqui é quem publica. Quem não tem
 * `unfiltered_html` passa pelo kses do `wp_insert_post()` de qualquer forma. A
 * diferença é que agora existe um caminho curto para negrito e link, não que o
 * caminho longo tenha sido fechado.
 *
 * @param string $texto O que foi digitado.
 * @return string O mesmo texto, com os atalhos convertidos.
 */
function vc_nota_formatar( string $texto ) : string {
	$t = $texto;

	/* Crase: guarda o miolo para as outras regras não o alcançarem. */
	$guardados = array();

	$t = preg_replace_callback(
		'/`([^`]+)`/',
		static function ( array $m ) use ( &$guardados ) : string {
			$chave                = "\x1A" . count( $guardados ) . "\x1A";
			$guardados[ $chave ]  = '<code>' . $m[1] . '</code>';
			return $chave;
		},
		$t
	);

	/* [texto](endereço) */
	$t = preg_replace_callback(
		'/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/',
		static function ( array $m ) : string {
			return '<a href="' . esc_url( $m[2] ) . '">' . $m[1] . '</a>';
		},
		$t
	);

	/* **negrito** antes de *itálico*, pela razão explicada acima. */
	$t = preg_replace( '/\*\*(?!\s)([^*\n]+?)(?<!\s)\*\*/', '<strong>$1</strong>', $t );

	/* *itálico*, sem casar com o que sobrou de um negrito. */
	$t = preg_replace( '/(?<!\*)\*(?!\s)([^*\n]+?)(?<!\s)\*(?!\*)/', '<em>$1</em>', $t );

	/* ~~riscado~~ */
	$t = preg_replace( '/~~(?!\s)([^~\n]+?)(?<!\s)~~/', '<s>$1</s>', $t );

	/* Endereço solto vira link, como a tela sempre prometeu. */
	if ( function_exists( 'make_clickable' ) ) {
		$t = make_clickable( $t );
	}

	/* Devolve o código guardado. */
	if ( $guardados ) {
		$t = strtr( $t, $guardados );
	}

	return $t;
}
function vc_nota_em_blocos( string $texto, int $imagem = 0 ) : string {
	$blocos = '';

	$texto = trim( $texto );

	if ( '' !== $texto ) {
		foreach ( preg_split( '/\n\s*\n/', $texto ) as $paragrafo ) {
			$paragrafo = trim( (string) $paragrafo );

			if ( '' === $paragrafo ) {
				continue;
			}

			/*
			 * A FORMATAÇÃO VEM ANTES DO `<br>`: os atalhos são procurados no
			 * parágrafo como ele foi digitado. Depois da troca de quebra de linha,
			 * um `*` no fim de uma linha já seria adjacente a um `<br>` e as
			 * regras de ênfase poderiam casar através dele.
			 */
			$paragrafo = vc_nota_formatar( $paragrafo );

			$paragrafo = str_replace( array( "\r\n", "\r", "\n" ), '<br>', $paragrafo );

			$blocos .= "<!-- wp:paragraph -->\n<p>{$paragrafo}</p>\n<!-- /wp:paragraph -->\n";
		}
	}

	if ( $imagem > 0 ) {
		$blocos .= vc_nota_bloco_imagem( $imagem );
	}

	if ( '' === $blocos ) {
		return '';
	}

	return (string) serialize_blocks( parse_blocks( $blocos ) );
}

/**
 * O bloco de imagem de uma nota, na forma que o editor grava.
 *
 * O `alt` vem da biblioteca quando existe: é o que o editor faz ao inserir uma
 * imagem, e é a diferença entre uma imagem descrita e uma imagem muda para quem
 * usa leitor de tela.
 *
 * @param int $anexo ID do anexo.
 * @return string O bloco, ou string vazia se o anexo não tiver imagem.
 */
function vc_nota_bloco_imagem( int $anexo ) : string {
	$url = (string) wp_get_attachment_image_url( $anexo, 'large' );

	if ( '' === $url ) {
		return '';
	}

	$alt     = (string) get_post_meta( $anexo, '_wp_attachment_image_alt', true );
	$legenda = (string) wp_get_attachment_caption( $anexo );

	$figura = '<figure class="wp-block-image size-large">'
		. '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . (int) $anexo . '"/>';

	if ( '' !== $legenda ) {
		$figura .= '<figcaption class="wp-element-caption">' . esc_html( $legenda ) . '</figcaption>';
	}

	$figura .= '</figure>';

	return "<!-- wp:image {\"id\":{$anexo},\"sizeSlug\":\"large\",\"linkDestination\":\"none\"} -->\n"
		. $figura . "\n<!-- /wp:image -->\n";
}

/**
 * Grava o que veio da caixa de status.
 *
 * Vai por `admin_post_` e não por `wp_insert_post()` direto na renderização
 * porque publicar é escrita: precisa de POST, de nonce e de checagem de
 * permissão, e as três coisas têm lugar certo. Uma tela que grava ao ser
 * aberta transforma um F5 em duas notas.
 */
function vc_notas_publicar_rapida() {
	if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
		wp_die(
			esc_html__( 'Você não tem permissão para publicar notas.', 'viniciuscodes' ),
			esc_html__( 'Sem permissão', 'viniciuscodes' ),
			array( 'response' => 403 )
		);
	}

	check_admin_referer( 'vc_nota_publicar' );

	$vc_destino = admin_url( 'edit.php?post_type=' . VC_NOTA . '&page=vc-nota-rapida' );
	if ( ! empty( $_POST['vc_redirect'] ) ) {
		$vc_destino = wp_validate_redirect( wp_unslash( $_POST['vc_redirect'] ), $vc_destino );
	}

	$vc_conteudo = isset( $_POST['vc_nota_conteudo'] ) ? trim( (string) wp_unslash( $_POST['vc_nota_conteudo'] ) ) : '';

	/*
	 * TEXTO E IMAGEM SÃO OS DOIS OPCIONAIS, e pelo menos um tem de existir: uma
	 * nota pode ser só uma foto, do mesmo jeito que pode ser só uma frase. A
	 * checagem mora aqui, e não no `required` do formulário, porque o navegador não
	 * é quem decide o que entra no banco.
	 */
	$vc_tem_imagem = ! empty( $_FILES['vc_nota_imagem']['name'] );

	if ( '' === $vc_conteudo && ! $vc_tem_imagem ) {
		wp_safe_redirect( add_query_arg( 'vc_erro', 'vazio', $vc_destino ) );
		exit;
	}

	$vc_status = ( isset( $_POST['vc_nota_status'] ) && 'draft' === $_POST['vc_nota_status'] ) ? 'draft' : 'publish';

	/*
	 * A IMAGEM SOBE ANTES DE A NOTA EXISTIR.
	 *
	 * O endereço dela entra no CORPO da nota, e o corpo é escrito no mesmo
	 * `wp_insert_post()` que cria a nota — subir depois obrigaria a gravar e
	 * regravar o post só para acrescentar uma linha.
	 *
	 * AS TRÊS INCLUSÕES SÃO NECESSÁRIAS: `media_handle_upload()` mora no admin e
	 * não é carregada no front, então chamá-la sem isto derruba a página.
	 */
	$vc_imagem       = 0;
	$vc_falha_imagem = false;

	if ( $vc_tem_imagem ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$vc_anexo = media_handle_upload( 'vc_nota_imagem', 0 );

		if ( is_wp_error( $vc_anexo ) ) {
			$vc_falha_imagem = true;
		} else {
			$vc_imagem = (int) $vc_anexo;
		}
	}

	/*
	 * A IMAGEM ENTRA NO CORPO DA NOTA, E NÃO COMO CAPA.
	 *
	 * O tema desligou capas de propósito: `vc_imagem_de_destaque()` devolve string
	 * vazia, com o comentário "eliminação completa de capas de posts". Uma capa que
	 * ninguém desenha não apareceria na linha do tempo, nem no feed, nem na página
	 * da nota — os três lugares onde a foto de uma nota precisa estar. No corpo ela
	 * sai nos três de uma vez, porque é o `the_content()` que alimenta os três.
	 *
	 * QUEM MONTA A IMAGEM É O BLOCO, e não um `<img src>` escrito à mão: a figura
	 * sai de `vc_nota_bloco_imagem()`, na forma que o editor grava. Ela NÃO traz
	 * `srcset`, `loading`, largura nem altura, e nem deve trazer: essas quatro são
	 * trabalho do `wp_filter_content_tags()`, na LEITURA, e de uma vez para os três
	 * lugares que mostram a nota — linha do tempo, página e feed. Gravar o `srcset`
	 * aqui seria congelar no banco o que o WordPress recalcula a cada acesso.
	 *
	 * O TAMANHO `large` continua sendo a escolha: limita a 1024px de largura — a
	 * coluna do site tem 620, e mandar uma foto de celular inteira seria entregar
	 * 12 megapixels para caber em 620.
	 *
	 * E A IMAGEM COMO BLOCO TEM PREÇO, medido: o `core/image` traz o CSS dele
	 * (`wp-block-image-inline-css`, 9 KB) e o modal do lightbox para a página. É o
	 * mesmo preço que todo post com foto neste site já paga — é o padrão do tema,
	 * não um acréscimo desta tela.
	 *
	 * O `og:image` VEM DE GRAÇA: `vc_imagem_social()` procura o primeiro `<img>` do
	 * conteúdo ANTES de olhar a imagem destacada, então a nota compartilhada já sai
	 * com esta imagem.
	 *
	 * A IMAGEM VAI DEPOIS DO TEXTO, E A PRIMEIRA VERSÃO DESTA LINHA FAZIA O
	 * CONTRÁRIO. A foto entrava na frente (`... . $vc_conteudo`), e o resultado foi
	 * medido na linha do tempo: a imagem tomava a tela e o texto — que É a nota —
	 * começava abaixo dela. Numa lista que se lê varrendo de cima a baixo, quem
	 * ficava visível era a foto, não o que a pessoa escreveu.
	 *
	 * E A ORDEM VALE EM TODA PARTE, e é por isso que ela se decide aqui, na
	 * gravação, e não no CSS de uma tela: é o `the_content()` que alimenta a linha
	 * do tempo, a página da nota e o feed. Reordenar com `flex` na lista faria o
	 * navegador mostrar o texto em cima enquanto o HTML continua com a imagem na
	 * frente — o leitor de tela e o feed continuariam lendo na ordem antiga, e o
	 * site passaria a ter duas verdades sobre a mesma nota.
	 *
	 * O `og:image` NÃO DEPENDE DA POSIÇÃO, e foi conferido antes de mudar: a
	 * `vc_imagem_social()` varre o conteúdo inteiro atrás do primeiro `<img>`, e não
	 * só o começo dele. Mover a imagem para o fim não mexe na prévia de link.
	 */
	if ( $vc_imagem ) {
		/* Texto e imagem saem daqui já em blocos; ver `vc_nota_em_blocos()`. */
		$vc_conteudo = vc_nota_em_blocos( $vc_conteudo, $vc_imagem );
	}

	$vc_id = wp_insert_post(
		array(
			'post_type'      => VC_NOTA,
			'post_status'    => $vc_status,
			/*
			 * O CONTEÚDO NÃO PASSA POR `wp_kses_post()` AQUI, e é de propósito: o
			 * `wp_insert_post()` já aplica `content_save_pre`, que roda o kses para
			 * quem não tem `unfiltered_html` e deixa o HTML intacto para quem tem.
			 * Sanitizar duas vezes só acrescenta a chance de comer marcação válida.
			 *
			 * E O KSES NÃO COME OS BLOCOS — isto foi MEDIDO, não suposto:
			 * `wp_kses_post()` aplicado sobre o conteúdo montado devolve os
			 * comentários `<!-- wp:paragraph -->` e `<!-- wp:image -->` intactos.
			 * A dúvida que estava escrita aqui antes ("a marcação de blocos do
			 * Gutenberg é justamente o que se comeria") não se confirmou, e é por
			 * isso que o corpo da nota pode ser bloco.
			 *
			 * O QUE O KSES VÊ NA IMAGEM é pouca coisa: um `img` com `src`, `alt` e
			 * `class`, dentro de um `figure` com `class`. `srcset`, `loading`,
			 * largura e altura não estão no banco — quem os acrescenta é a leitura,
			 * depois do kses, e por isso não há o que sanear.
			 */
			'post_content'   => $vc_conteudo,
			'post_author'    => get_current_user_id(),
			/*
			 * `comment_status` ABERTO: a nota recebe comentário de quem lê aqui,
			 * além da resposta que chega do fediverso por Webmention. É o padrão do
			 * site, e está escrito aqui para quem lê esta tela não precisar ir
			 * atrás da regra — o painel de discussão do editor fecha caso a caso.
			 *
			 * `ping_status` fica como veio de propósito: fechá-lo é o caminho
			 * mais curto para atrapalhar a recepção de Webmention.
			 */
			'comment_status' => 'open',
			'ping_status'    => 'open',
		),
		true
	);

	if ( is_wp_error( $vc_id ) || ! $vc_id ) {
		wp_safe_redirect( add_query_arg( 'vc_erro', 'falhou', $vc_destino ) );
		exit;
	}

	/* Os temas saíram com a tela nova: ela não os envia mais. */

	/*
	 * A IMAGEM É AMARRADA À NOTA DEPOIS QUE ELA EXISTE: `media_handle_upload()`
	 * rodou antes, quando não havia a quem amarrar. O `post_parent` é o que faz a
	 * imagem aparecer em "Anexado a" na biblioteca, e a imagem destacada é o que o
	 * editor completo mostra como capa. Os dois servem a quem for mexer nesta nota
	 * depois — a nota publicada não depende de nenhum dos dois.
	 */
	if ( $vc_imagem ) {
		wp_update_post(
			array(
				'ID'          => $vc_imagem,
				'post_parent' => $vc_id,
			)
		);

		set_post_thumbnail( $vc_id, $vc_imagem );
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				/*
				 * A FALHA DA IMAGEM NÃO DESFAZ A NOTA, e o aviso diz isso: perder o
				 * texto porque uma foto de 8 MB não subiu seria trocar um problema
				 * pequeno por um grande. O estado é um só, "sem_imagem", tanto para
				 * rascunho quanto para publicada — a mensagem fala em "gravada" e
				 * serve aos dois.
				 */
				'vc_ok' => $vc_falha_imagem ? 'sem_imagem' : ( ( 'draft' === $vc_status ) ? 'rascunho' : 'publicada' ),
				'vc_id' => $vc_id,
			),
			$vc_destino
		)
	);
	exit;
}
add_action( 'admin_post_vc_nota_publicar', 'vc_notas_publicar_rapida' );

/**
 * O aviso de que deu certo (ou não).
 *
 * Fica em `admin_notices` e não dentro da tela porque a caixa de status
 * também vive no painel: publicar de lá precisa da mesma resposta, e um aviso
 * preso à tela de origem deixaria a ação do painel muda — o que faz a pessoa
 * publicar de novo por não saber se a primeira vez funcionou.
 */
function vc_notas_avisos() {
	$vc_ok   = isset( $_GET['vc_ok'] ) ? sanitize_key( wp_unslash( $_GET['vc_ok'] ) ) : '';
	$vc_erro = isset( $_GET['vc_erro'] ) ? sanitize_key( wp_unslash( $_GET['vc_erro'] ) ) : '';

	if ( '' === $vc_ok && '' === $vc_erro ) {
		return;
	}

	if ( 'sem_imagem' === $vc_ok ) {
		/*
		 * AVISO, E NÃO ERRO: a nota está gravada — o que falhou foi o arquivo. A
		 * mensagem diz o que dá para fazer agora, em vez de só anunciar o problema.
		 */
		$vc_classe = 'notice-warning';
		$vc_texto  = __( 'A nota foi gravada, mas a imagem não subiu: arquivo grande demais ou formato não aceito. Dá para acrescentar pelo editor completo.', 'viniciuscodes' );
	} elseif ( 'publicada' === $vc_ok ) {
		$vc_classe = 'notice-success';
		$vc_texto  = __( 'Nota publicada, ela já está em /notas/ e no feed.', 'viniciuscodes' );
	} elseif ( 'rascunho' === $vc_ok ) {
		$vc_classe = 'notice-info';
		$vc_texto  = __( 'Rascunho salvo.', 'viniciuscodes' );
	} elseif ( 'vazio' === $vc_erro ) {
		$vc_classe = 'notice-error';
		$vc_texto  = __( 'A nota estava vazia: não havia o que publicar.', 'viniciuscodes' );
	} else {
		$vc_classe = 'notice-error';
		$vc_texto  = __( 'Não deu para gravar a nota. Tente de novo pelo editor.', 'viniciuscodes' );
	}

	echo '<div class="notice ' . esc_attr( $vc_classe ) . ' is-dismissible"><p>' . esc_html( $vc_texto );

	$vc_id = isset( $_GET['vc_id'] ) ? absint( $_GET['vc_id'] ) : 0;

	if ( $vc_id ) {
		$vc_editar = get_edit_post_link( $vc_id );

		if ( $vc_editar ) {
			echo ' <a href="' . esc_url( $vc_editar ) . '">' . esc_html__( 'continuar editando', 'viniciuscodes' ) . '</a>';
		}
	}

	echo '</p></div>';
}
add_action( 'admin_notices', 'vc_notas_avisos' );

/* -------------------------------------------------------------------------
 * 2. A caixa de status no painel
 * ---------------------------------------------------------------------- */

/**
 * Um bloco de "nota rápida" no painel.
 *
 * O painel é a primeira tela do admin e é onde a vontade de escrever uma nota
 * aparece. O formulário é o mesmo da tela dedicada, com menos campos: quem
 * quiser marcar tema vai em "mais opções".
 */
function vc_notas_widget_painel() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	wp_add_dashboard_widget( 'vc_nota_status', __( 'Nota rápida', 'viniciuscodes' ), 'vc_notas_widget_painel_render' );
}
add_action( 'wp_dashboard_setup', 'vc_notas_widget_painel' );

/**
 * Desenha o bloco do painel.
 */
function vc_notas_widget_painel_render() {
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="vc_nota_publicar" />
		<input type="hidden" name="vc_redirect" value="<?php echo esc_url( admin_url( 'index.php' ) ); ?>" />
		<?php wp_nonce_field( 'vc_nota_publicar' ); ?>

		<p>
			<label class="screen-reader-text" for="vc-nota-painel"><?php esc_html_e( 'Nova nota', 'viniciuscodes' ); ?></label>
			<textarea id="vc-nota-painel" name="vc_nota_conteudo" rows="3" class="large-text" placeholder="<?php echo esc_attr__( 'o que você quer dizer…', 'viniciuscodes' ); ?>"></textarea>
		</p>

		<p class="vc-nota-painel__acoes">
			<button type="submit" name="vc_nota_status" value="publish" class="button button-primary">
				<?php esc_html_e( 'publicar', 'viniciuscodes' ); ?>
			</button>
			<a class="button button-link" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . VC_NOTA . '&page=vc-nota-rapida' ) ); ?>">
				<?php esc_html_e( 'mais opções', 'viniciuscodes' ); ?>
			</a>
		</p>
	</form>
	<?php
}

/* -------------------------------------------------------------------------
 * 3. A lista enxuta
 * ---------------------------------------------------------------------- */

/**
 * As colunas da lista de Notas: conteúdo, interações, data.
 *
 * A COLUNA DE CONTEÚDO entra no lugar da de título, que não existe. A ORDEM é
 * a que o olho segue ao conferir o que foi publicado hoje: o que eu escrevi,
 * se alguém respondeu, e quando saiu.
 *
 * A coluna de interações é a de comentários com outro nome, e o nome importa:
 * o ActivityPub e o Webmention gravam as respostas do Fediverso como
 * comentário nesta mesma tabela. Este número conta as duas conversas — a
 * daqui e a de fora —, que é o que quem publica uma nota quer saber.
 *
 * @param array $colunas As colunas registradas pelo núcleo.
 * @return array As colunas que interessam, na ordem que interessa.
 */
function vc_notas_colunas( $colunas ) {
	$vc_novas = array();

	if ( isset( $colunas['cb'] ) ) {
		$vc_novas['cb'] = $colunas['cb'];
	}

	$vc_novas['vc_nota_conteudo'] = __( 'Nota', 'viniciuscodes' );

	if ( isset( $colunas['comments'] ) ) {
		$vc_novas['comments'] = __( 'Interações', 'viniciuscodes' );
	}

	$vc_novas['date'] = __( 'Data', 'viniciuscodes' );

	return $vc_novas;
}
add_filter( 'manage_' . VC_NOTA . '_posts_columns', 'vc_notas_colunas' );

/**
 * Desenha a coluna de conteúdo.
 *
 * O texto vem em uma linha só, com os espaços normalizados: uma nota com
 * quebra de linha viraria três linhas na tabela e desalinharia a lista
 * inteira para dizer a mesma coisa. `wp_html_excerpt()` corta sem partir
 * entidade nem deixar tag aberta.
 *
 * @param string $coluna O identificador da coluna sendo desenhada.
 * @param int    $vc_id  O ID do post da linha.
 */
function vc_notas_coluna_conteudo( $coluna, $vc_id ) {
	if ( 'vc_nota_conteudo' !== $coluna ) {
		return;
	}

	$vc_nota = get_post( $vc_id );

	if ( ! $vc_nota ) {
		return;
	}

	$vc_editar = get_edit_post_link( $vc_id );
	$vc_numero = vc_nota_numero( $vc_id );
	$vc_texto  = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( $vc_nota->post_content ), true ) ) );

	if ( '' === $vc_texto ) {
		/*
		 * UMA NOTA PODE SER SÓ IMAGEM, e aí a coluna não pode dizer "(nota vazia)":
		 * o `wp_strip_all_tags()` acima come a `<img>` e o texto fica vazio mesmo
		 * com a nota cheia. A checagem é no conteúdo cru, logo depois do strip.
		 */
		$vc_texto = preg_match( '/<img/i', $vc_nota->post_content )
			? __( '(nota com imagem)', 'viniciuscodes' )
			: __( '(nota vazia)', 'viniciuscodes' );
	}

	echo '<strong>';
	if ( $vc_numero > 0 ) {
		echo '<span class="vc-nota-coluna__numero">#' . (int) $vc_numero . '</span> ';
	}
	echo '<a class="row-title" href="' . esc_url( (string) $vc_editar ) . '">'
		. esc_html( wp_html_excerpt( $vc_texto, 160, '…' ) )
		. '</a></strong>';

	if ( 'publish' === $vc_nota->post_status ) {
		echo '<br /><a class="vc-nota-coluna__ver" href="' . esc_url( get_permalink( $vc_id ) ) . '">'
			. esc_html__( 'ver no site', 'viniciuscodes' )
			. '</a>';
	}
}
add_action( 'manage_' . VC_NOTA . '_posts_custom_column', 'vc_notas_coluna_conteudo', 10, 2 );

/* -------------------------------------------------------------------------
 * 4. O título não existe — a garantia
 * ---------------------------------------------------------------------- */

/**
 * Esconde o campo de título, se alguém voltar a desenhá-lo.
 *
 * ISTO É REDUNDANTE, E CADA LINHA É UM SEGURO. Com `title` fora de `supports`,
 * o editor de blocos não monta o campo: `PostTitle` está embrulhado num
 * `PostTypeSupportCheck supportKeys="title"`. O que este CSS cobre é o resto —
 * o editor clássico (que desenha o campo quando o plugin Classic Editor está
 * ativo) e o dia em que o núcleo mudar essa decisão e o campo reaparecer.
 *
 * Um campo de título VAZIO em cima do texto é pior que um campo a mais: ele
 * convida a inventar um título para uma coisa que não tem, e o que for
 * digitado nele passa a aparecer no `<title>` da página.
 */
function vc_notas_esconder_campo_de_titulo() {
	$vc_tela = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $vc_tela || VC_NOTA !== $vc_tela->post_type ) {
		return;
	}
	?>
	<style id="vc-notas-editor">
		body.post-type-nota #titlediv,
		body.post-type-nota #titlewrap,
		body.post-type-nota .editor-post-title,
		body.post-type-nota .editor-post-title__block,
		body.post-type-nota .editor-post-title__input {
			display: none !important;
		}
	</style>
	<?php
}
add_action( 'admin_head-post.php', 'vc_notas_esconder_campo_de_titulo' );
add_action( 'admin_head-post-new.php', 'vc_notas_esconder_campo_de_titulo' );
