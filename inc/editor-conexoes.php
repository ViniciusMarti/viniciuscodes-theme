<?php
/**
 * Assistente de Conexões Hipertextuais no Editor de Posts (Gutenberg / Classic).
 *
 * Exibe uma Meta Box lateral enquanto você escreve ou edita um post sugerindo:
 * 1. Posts antigos do blog relacionados (baseado em palavras-chave do título/conteúdo).
 * 2. Autores / blogs do seu Blogroll cujo nome ou temas aparecem no texto.
 * 3. Campo de busca rápida com botão para copiar markdown ou link HTML com 1 clique.
 *
 * @package ViniciusCodes
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/blogroll-data.php';

/**
 * Registra a meta box de conexões na tela de edição de posts.
 */
function vc_registrar_metabox_conexoes(): void {
	add_meta_box(
		'vc_conexoes_editor_box',
		__( '🔗 Conexões & Links Sugeridos', 'viniciuscodes' ),
		'vc_render_metabox_conexoes',
		'post',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'vc_registrar_metabox_conexoes' );

/**
 * Endpoint AJAX para buscar posts e blogroll com base no texto em tempo real.
 */
function vc_ajax_buscar_conexoes(): void {
	check_ajax_referer( 'vc_conexoes_nonce', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => 'Não autorizado.' ) );
	}

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$titulo  = isset( $_POST['titulo'] ) ? sanitize_text_field( wp_unslash( $_POST['titulo'] ) ) : '';
	$termo   = isset( $_POST['termo'] ) ? sanitize_text_field( wp_unslash( $_POST['termo'] ) ) : '';

	// Determina a lista de palavras para pesquisar
	$keywords = array();
	if ( ! empty( $termo ) ) {
		$keywords = array( $termo );
	} elseif ( ! empty( $titulo ) ) {
		// Remove stopwords simples em português
		$stopwords = array(
			'de', 'a', 'o', 'que', 'e', 'do', 'da', 'em', 'um', 'para', 'é', 'com', 'não',
			'uma', 'os', 'no', 'se', 'na', 'por', 'mais', 'as', 'dos', 'como', 'mas', 'foi',
			'ao', 'ele', 'das', 'tem', 'à', 'seu', 'sua', 'ou', 'ser', 'quando', 'muito', 'há',
			'nos', 'já', 'está', 'eu', 'também', 'só', 'pelo', 'pela', 'até', 'isso', 'ela',
			'entre', 'era', 'depois', 'sem', 'mesmo', 'aos', 'ter', 'seus', 'quem', 'nas', 'me',
			'esse', 'eles', 'estão', 'você', 'tinha', 'foram', 'essa', 'num', 'nem', 'suas',
			'meu', 'às', 'minha', 'têm', 'numa', 'pelos', 'elas', 'havia', 'seja', 'qual', 'será'
		);
		$palavras = preg_split( '/[\s,\.\?!:;\-\(\)]+/u', mb_strtolower( $titulo ) );
		foreach ( $palavras as $p ) {
			$p = trim( $p );
			if ( mb_strlen( $p ) >= 3 && ! in_array( $p, $stopwords, true ) ) {
				$keywords[] = $p;
			}
		}
	}

	// 1. Busca posts antigos relevantes
	$posts_sugeridos = array();
	$ids_vistos      = array( $post_id );

	// Se houver termo de busca explícito
	if ( ! empty( $termo ) ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				's'              => $termo,
				'post__not_in'   => $ids_vistos,
				'posts_per_page' => 6,
			)
		);
		foreach ( $query->posts as $p ) {
			$posts_sugeridos[] = array(
				'id'    => $p->ID,
				'title' => get_the_title( $p->ID ),
				'url'   => wp_make_link_relative( get_permalink( $p->ID ) ),
				'date'  => get_the_date( 'd/m/Y', $p->ID ),
			);
		}
	} else {
		// Busca pelas palavras-chave mais longas do título
		usort( $keywords, function( $a, $b ) {
			return mb_strlen( $b ) <=> mb_strlen( $a );
		} );

		$queries_feitas = 0;
		foreach ( array_slice( $keywords, 0, 3 ) as $kw ) {
			if ( $queries_feitas >= 3 || count( $posts_sugeridos ) >= 6 ) {
				break;
			}
			$query = new WP_Query(
				array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					's'              => $kw,
					'post__not_in'   => $ids_vistos,
					'posts_per_page' => 4,
				)
			);
			foreach ( $query->posts as $p ) {
				if ( ! in_array( $p->ID, $ids_vistos, true ) ) {
					$ids_vistos[]      = $p->ID;
					$posts_sugeridos[] = array(
						'id'    => $p->ID,
						'title' => get_the_title( $p->ID ),
						'url'   => wp_make_link_relative( get_permalink( $p->ID ) ),
						'date'  => get_the_date( 'd/m/Y', $p->ID ),
					);
				}
			}
			$queries_feitas++;
		}
	}

	// 2. Busca autores no Blogroll que combinem com termo ou título
	$blogroll_todos = vc_get_blogroll_items();
	$blogroll_sug   = array();

	$busca_texto = mb_strtolower( ! empty( $termo ) ? $termo : $titulo );

	foreach ( $blogroll_todos as $b ) {
		$nome_lower = mb_strtolower( $b['nome'] );
		$dom_lower  = mb_strtolower( $b['dominio'] );

		$match = false;
		if ( ! empty( $termo ) ) {
			if ( false !== mb_strpos( $nome_lower, $busca_texto ) || false !== mb_strpos( $dom_lower, $busca_texto ) ) {
				$match = true;
			}
		} else {
			// Verifica se alguma palavra-chave bate com nome do autor/blog
			foreach ( $keywords as $kw ) {
				if ( false !== mb_strpos( $nome_lower, $kw ) || false !== mb_strpos( $dom_lower, $kw ) ) {
					$match = true;
					break;
				}
			}
		}

		if ( $match ) {
			$fav_file = $b['favicon'] ?? ( $b['dominio'] . '.png' );
			$blogroll_sug[] = array(
				'nome'    => $b['nome'],
				'url'     => $b['url'],
				'dominio' => $b['dominio'],
				'favicon' => get_theme_file_uri( "assets/favicons/{$fav_file}" ),
			);
		}
	}

	wp_send_json_success(
		array(
			'posts'    => $posts_sugeridos,
			'blogroll' => $blogroll_sug,
		)
	);
}
add_action( 'wp_ajax_vc_buscar_conexoes', 'vc_ajax_buscar_conexoes' );

/**
 * Renderiza o corpo da Meta Box no editor de posts.
 *
 * @param WP_Post $post Post em edição.
 */
function vc_render_metabox_conexoes( $post ): void {
	wp_nonce_field( 'vc_conexoes_nonce', 'vc_conexoes_nonce_field' );
	?>
	<div id="vc-conexoes-app" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
		<style>
			#vc-conexoes-app {
				font-size: 13px;
				color: #374151;
			}
			.vc-cx-search-wrap {
				margin-bottom: 12px;
				display: flex;
				gap: 4px;
			}
			.vc-cx-input {
				width: 100%;
				padding: 5px 8px;
				font-size: 12px;
				border: 1px solid #d1d5db;
				border-radius: 4px;
			}
			.vc-cx-btn {
				background: #f3f4f6;
				border: 1px solid #d1d5db;
				border-radius: 4px;
				padding: 4px 8px;
				cursor: pointer;
				font-size: 11px;
				font-weight: 500;
				white-space: nowrap;
				transition: all 0.15s;
			}
			.vc-cx-btn:hover {
				background: #e5e7eb;
			}
			.vc-cx-section-title {
				font-size: 11px;
				font-weight: 600;
				text-transform: uppercase;
				letter-spacing: 0.05em;
				color: #6b7280;
				margin: 12px 0 6px 0;
				display: flex;
				align-items: center;
				justify-content: space-between;
			}
			.vc-cx-list {
				list-style: none;
				margin: 0;
				padding: 0;
				display: flex;
				flex-direction: column;
				gap: 6px;
			}
			.vc-cx-item {
				background: #f9fafb;
				border: 1px solid #e5e7eb;
				border-radius: 6px;
				padding: 6px 8px;
				display: flex;
				flex-direction: column;
				gap: 4px;
			}
			.vc-cx-item-header {
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 6px;
			}
			.vc-cx-item-title {
				font-weight: 500;
				color: #111827;
				text-decoration: none;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
				max-width: 190px;
			}
			.vc-cx-item-title:hover {
				color: #2563eb;
			}
			.vc-cx-actions {
				display: flex;
				gap: 4px;
				margin-top: 2px;
			}
			.vc-cx-copy-btn {
				background: #fff;
				border: 1px solid #e5e7eb;
				border-radius: 3px;
				font-size: 10px;
				padding: 2px 6px;
				color: #4b5563;
				cursor: pointer;
				line-height: 1.2;
			}
			.vc-cx-copy-btn:hover {
				background: #2563eb;
				color: #fff;
				border-color: #2563eb;
			}
			.vc-cx-meta-text {
				font-size: 10px;
				color: #9ca3af;
			}
			.vc-cx-blogroll-item {
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding: 5px 8px;
				background: #fdfdfd;
				border: 1px solid #e5e7eb;
				border-radius: 6px;
			}
			.vc-cx-blogroll-info {
				display: flex;
				align-items: center;
				gap: 6px;
				min-width: 0;
			}
			.vc-cx-fav {
				width: 14px;
				height: 14px;
				flex-shrink: 0;
			}
			.vc-cx-empty {
				font-size: 11px;
				color: #9ca3af;
				font-style: italic;
				margin: 4px 0;
			}
			.vc-cx-badge {
				font-size: 9px;
				padding: 1px 4px;
				border-radius: 3px;
				background: #e0e7ff;
				color: #3730a3;
			}
		</style>

		<div class="vc-cx-search-wrap">
			<input type="text" id="vc-cx-busca-input" class="vc-cx-input" placeholder="Buscar posts ou blogroll...">
			<button type="button" id="vc-cx-btn-buscar" class="vc-cx-btn">Buscar</button>
		</div>

		<!-- Posts Internos Sugeridos -->
		<div class="vc-cx-section-title">
			<span>Artigos do seu Blog</span>
			<span id="vc-cx-count-posts" class="vc-cx-badge">0</span>
		</div>
		<ul id="vc-cx-posts-list" class="vc-cx-list">
			<li class="vc-cx-empty">Analisando título do post...</li>
		</ul>

		<!-- Autores do Blogroll Sugeridos -->
		<div class="vc-cx-section-title" style="margin-top: 14px;">
			<span>Autores do Blogroll</span>
			<span id="vc-cx-count-blogroll" class="vc-cx-badge">0</span>
		</div>
		<ul id="vc-cx-blogroll-list" class="vc-cx-list">
			<li class="vc-cx-empty">Nenhum autor detectado no tema.</li>
		</ul>

		<div style="margin-top: 12px; padding-top: 8px; border-top: 1px dashed #e5e7eb; font-size: 10px; color: #9ca3af;">
			💡 <em>Clique em "Copiar Link" ou "Markdown" e cole diretamente no seu texto.</em>
		</div>
	</div>

	<script>
	(function() {
		const app = document.getElementById('vc-conexoes-app');
		if (!app) return;

		const postId = app.getAttribute('data-post-id');
		const inputBusca = document.getElementById('vc-cx-busca-input');
		const btnBuscar = document.getElementById('vc-cx-btn-buscar');
		const listaPosts = document.getElementById('vc-cx-posts-list');
		const listaBlogroll = document.getElementById('vc-cx-blogroll-list');
		const countPosts = document.getElementById('vc-cx-count-posts');
		const countBlogroll = document.getElementById('vc-cx-count-blogroll');
		const nonceInput = document.getElementById('vc_conexoes_nonce_field');

		function getPostTitle() {
			// Gutenberg
			if (window.wp && wp.data && wp.data.select && wp.data.select('core/editor')) {
				return wp.data.select('core/editor').getEditedPostAttribute('title') || '';
			}
			// Classic editor
			const titleInput = document.getElementById('title');
			return titleInput ? titleInput.value : '';
		}

		function copiarParaClipboard(texto, btn, textoOriginal) {
			navigator.clipboard.writeText(texto).then(() => {
				btn.textContent = 'Copiado!';
				setTimeout(() => { btn.textContent = textoOriginal; }, 1500);
			}).catch(() => {
				// Fallback antigo
				const ta = document.createElement('textarea');
				ta.value = texto;
				document.body.appendChild(ta);
				ta.select();
				document.execCommand('copy');
				document.body.removeChild(ta);
				btn.textContent = 'Copiado!';
				setTimeout(() => { btn.textContent = textoOriginal; }, 1500);
			});
		}

		function buscarConexoes(termoExplicit = '') {
			const titulo = getPostTitle();
			const data = new FormData();
			data.append('action', 'vc_buscar_conexoes');
			data.append('nonce', nonceInput ? nonceInput.value : '');
			data.append('post_id', postId);
			data.append('titulo', titulo);
			data.append('termo', termoExplicit);

			fetch(ajaxurl, {
				method: 'POST',
				body: data
			})
			.then(r => r.json())
			.then(res => {
				if (!res.success) return;
				renderResultados(res.data);
			})
			.catch(err => console.error('Erro ao buscar conexões:', err));
		}

		function renderResultados(data) {
			// Render Posts
			listaPosts.innerHTML = '';
			const posts = data.posts || [];
			countPosts.textContent = posts.length;

			if (posts.length === 0) {
				listaPosts.innerHTML = '<li class="vc-cx-empty">Nenhum post relacionado encontrado. Digite um termo na busca acima.</li>';
			} else {
				posts.forEach(p => {
					const li = document.createElement('li');
					li.className = 'vc-cx-item';
					li.innerHTML = `
						<div class="vc-cx-item-header">
							<a href="${p.url}" target="_blank" class="vc-cx-item-title" title="${p.title}">${p.title}</a>
							<span class="vc-cx-meta-text">${p.date}</span>
						</div>
						<div class="vc-cx-actions">
							<button type="button" class="vc-cx-copy-btn btn-copy-url" data-url="${p.url}">Copiar URL</button>
							<button type="button" class="vc-cx-copy-btn btn-copy-md" data-md="[${p.title}](${p.url})">Copiar Markdown</button>
							<button type="button" class="vc-cx-copy-btn btn-copy-html" data-html="<a href='${p.url}'>${p.title}</a>">Copiar HTML</button>
						</div>
					`;
					listaPosts.appendChild(li);
				});
			}

			// Render Blogroll
			listaBlogroll.innerHTML = '';
			const blogroll = data.blogroll || [];
			countBlogroll.textContent = blogroll.length;

			if (blogroll.length === 0) {
				listaBlogroll.innerHTML = '<li class="vc-cx-empty">Nenhum autor do blogroll identificado para esse tema.</li>';
			} else {
				blogroll.forEach(b => {
					const li = document.createElement('li');
					li.className = 'vc-cx-blogroll-item';
					li.innerHTML = `
						<div class="vc-cx-blogroll-info">
							<img src="${b.favicon}" class="vc-cx-fav" alt="" onerror="this.style.display='none'">
							<div>
								<div style="font-weight: 500; font-size: 12px; color: #111827;">${b.nome}</div>
								<div class="vc-cx-meta-text">${b.dominio}</div>
							</div>
						</div>
						<div class="vc-cx-actions">
							<button type="button" class="vc-cx-copy-btn btn-copy-md" data-md="[${b.nome}](${b.url})">Markdown</button>
							<button type="button" class="vc-cx-copy-btn btn-copy-url" data-url="${b.url}">URL</button>
						</div>
					`;
					listaBlogroll.appendChild(li);
				});
			}

			// Eventos de cópia
			document.querySelectorAll('.vc-cx-copy-btn').forEach(btn => {
				btn.onclick = function() {
					const url = this.getAttribute('data-url');
					const md = this.getAttribute('data-md');
					const html = this.getAttribute('data-html');
					const texto = md || html || url;
					copiarParaClipboard(texto, this, this.textContent);
				};
			});
		}

		// Disparo inicial
		setTimeout(() => { buscarConexoes(); }, 1200);

		// Evento de busca manual
		btnBuscar.addEventListener('click', () => {
			buscarConexoes(inputBusca.value.trim());
		});

		inputBusca.addEventListener('keypress', (e) => {
			if (e.key === 'Enter') {
				e.preventDefault();
				buscarConexoes(inputBusca.value.trim());
			}
		});

		// Monitora mudança no título do Gutenberg após digitação
		if (window.wp && wp.data && wp.data.subscribe) {
			let lastTitle = '';
			let timer = null;
			wp.data.subscribe(() => {
				const currentTitle = getPostTitle();
				if (currentTitle && currentTitle !== lastTitle && !inputBusca.value.trim()) {
					lastTitle = currentTitle;
					clearTimeout(timer);
					timer = setTimeout(() => {
						buscarConexoes();
					}, 2000);
				}
			});
		}
	})();
	</script>
	<?php
}
