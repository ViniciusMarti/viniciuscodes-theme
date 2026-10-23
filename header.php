<?php
/**
 * O topo de toda página: o <head>, o cabeçalho do site e a abertura do layout.
 *
 * POR QUE ESTE ARQUIVO ABRE UM <div> QUE SÓ FECHA NO footer.php
 * -------------------------------------------------------------
 * No site original, o `BaseLayout.astro` monta a página assim:
 *
 *     <Header />
 *     <div class="layout">
 *       <Sidebar />
 *       <main class="content" id="conteudo"><div class="content__inner">
 *         ...conteúdo...
 *       </div></main>
 *     </div>
 *     <Footer />
 *
 * O `div.layout` envolve a barra lateral E o conteúdo — ele é o que faz a
 * coluna lateral existir. Em WordPress, o conteúdo de cada template entra
 * exatamente onde aquele `<slot />` entrava. Como o envoltório é um só, ele
 * tem de começar no header e terminar no footer: repetir o `<div>` dentro de
 * cada template seria duplicar a mesma estrutura em quatro arquivos, que é
 * justamente o que este trabalho veio eliminar.
 *
 * @package ViniciusCodes
 */

$vc_singular = is_singular();
$vc_id       = $vc_singular ? (int) get_queried_object_id() : 0;

/*
 * A descrição que vai para a meta tag e para as prévias de link.
 *
 * A conta mora em `vc_descricao()`, em `functions.php`, e não aqui: a mesma
 * frase alimenta a meta tag e o Open Graph, e ter a regra num lugar só é o que
 * impede as duas de divergirem. Foi o que aconteceu antes, quando `/posts/` —
 * que é uma página, e não um texto — saía sem descrição nenhuma.
 */
$vc_descricao = vc_descricao();

$vc_titulo = $vc_singular ? get_the_title( $vc_id ) : wp_get_document_title();
if ( false === strpos( $vc_titulo, '@viniciuscodes' ) ) {
	$vc_titulo = $vc_titulo . ' · @viniciuscodes';
}

$vc_imagem = vc_imagem_social( $vc_id );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<?php
	/*
	 * `wp_head()` traz o <title> e — o que importa para este trabalho — as folhas
	 * e os scripts enfileirados em `functions.php`. Nenhum <link> de CSS é escrito
	 * à mão aqui.
	 *
	 * O canônico NÃO vem do núcleo: ele só o imprime em páginas singulares, e a
	 * home e os arquivos de tema saíam sem nenhum. A remoção está em
	 * `functions.php` e a etiqueta é escrita logo abaixo, para toda página.
	 */
	wp_head();
	?>

	<meta name="description" content="<?php echo esc_attr( $vc_descricao ); ?>" />
	<link rel="canonical" href="<?php echo esc_url( vc_canonical() ); ?>" />

	<?php
	/*
	 * O feed em `/feed.xml`, que é o endereço do site — e não `/feed/`, que é onde
	 * o WordPress o publica. O `automatic-feed-links` foi desligado em
	 * `functions.php` justamente para o anúncio apontar para o endereço certo.
	 */
	?>
	<link rel="alternate" type="application/rss+xml" title="@viniciuscodes" href="<?php echo esc_url( home_url( '/feed.xml' ) ); ?>" />
	<link rel="pingback" href="<?php echo esc_url( home_url( '/xmlrpc.php' ) ); ?>" />

	<?php
	/*
	 * Aqui havia um `rel="preload"` do `normal-latin.woff2`. Ele saiu junto com a
	 * folha que declarava a fonte: com a tipografia vindo do sistema, pré-carregar
	 * 33 KB passou a ser baixar um arquivo que ninguém pede.
	 */
	?>

	<?php
	/*
	 * A manifestação do app. É servida por `functions.php` em
	 * `/manifest.webmanifest`, e não como arquivo: os ícones estão na biblioteca
	 * de mídia, cujo endereço muda de servidor para servidor.
	 */
	?>
	<link rel="manifest" href="<?php echo esc_url( home_url( '/manifest.webmanifest' ) ); ?>" />

	<meta property="og:type" content="<?php echo $vc_singular ? 'article' : 'website'; ?>" />
	<meta property="og:site_name" content="@viniciuscodes" />
	<meta property="og:title" content="<?php echo esc_attr( $vc_titulo ); ?>" />
	<meta property="og:description" content="<?php echo esc_attr( $vc_descricao ); ?>" />
	<meta property="og:url" content="<?php echo esc_url( $vc_singular ? get_permalink( $vc_id ) : home_url( add_query_arg( array() ) ) ); ?>" />
	<meta property="og:image" content="<?php echo esc_url( $vc_imagem ); ?>" />
	<meta property="og:image:alt" content="<?php echo esc_attr( $vc_titulo ); ?>" />
	<meta property="og:locale" content="pt_BR" />

	<meta name="twitter:card" content="summary_large_image" />
	<meta name="twitter:title" content="<?php echo esc_attr( $vc_titulo ); ?>" />
	<meta name="twitter:description" content="<?php echo esc_attr( $vc_descricao ); ?>" />
	<meta name="twitter:image" content="<?php echo esc_url( $vc_imagem ); ?>" />

	<?php
	/*
	 * O ícone da aba. O `?v=2` é o mesmo truque de cache do site original: o
	 * arquivo mora no tema, sem hash no nome, então trocar o ícone não chegaria a
	 * ninguém sem uma chave de cache nova.
	 *
	 * O `apple-touch-icon` NÃO é escrito aqui: ele vem do ícone do site do
	 * WordPress (Configurações → Geral), que o núcleo imprime sozinho. Escrever
	 * os dois gerava duas etiquetas iguais no `<head>`.
	 */
	?>
	<link rel="icon" href="<?php echo esc_url( get_theme_file_uri( 'assets/icons/favicon.svg' ) ); ?>?v=3" type="image/svg+xml" />
	<meta name="theme-color" content="#f8f6f0" media="(prefers-color-scheme: light)" />
	<meta name="theme-color" content="#141715" media="(prefers-color-scheme: dark)" />

	<?php
	/*
	 * A ÚNICA EXCEÇÃO À REGRA DE NÃO ESCREVER SCRIPT À MÃO.
	 *
	 * A escolha de tema claro/escuro fica guardada no navegador, e precisa ser
	 * aplicada ANTES da primeira pintura. Um script enfileirado é carregado
	 * depois do <head> e executado depois de a página já ter sido desenhada: o
	 * resultado é a página branca piscando antes de ficar escura, em toda visita.
	 *
	 * Ele é curto, não depende de nada e falha em silêncio quando o navegador
	 * bloqueia o armazenamento — que é o caso de janela anônima em alguns
	 * navegadores.
	 *
	 * Quando não há escolha guardada, quem manda é a preferência do sistema
	 * (`prefers-color-scheme`). Sem essa consulta, um leitor com o sistema em
	 * escuro abriria o site em claro na primeira visita — e teria de caçar o
	 * botão para corrigir o que o sistema já havia respondido.
	 *
	 * Note que o atributo é escrito SEMPRE, e não só quando existe escolha
	 * guardada: o valor resolvido precisa estar no documento antes da pintura,
	 * para o CSS poder decidir a paleta de uma vez.
	 */
	?>
	<script>
		try {
			var vcTema = localStorage.getItem('vco-theme');

			if (vcTema !== 'dark' && vcTema !== 'light') {
				vcTema = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
			}

			document.documentElement.dataset.theme = vcTema;
		} catch (e) {
			/* armazenamento indisponível: fica a paleta padrão */
		}
	</script>
	<script>try{if(localStorage.getItem('vc_sidebar_state')==='collapsed'){document.documentElement.setAttribute('data-sidebar','collapsed')}}catch(e){}</script>
</head>

<body <?php body_class(); ?>>
<a class="skip-link" href="#conteudo">pular para o conteúdo</a>

<?php
/*
 * O cabeçalho do site. Estrutura e classes do `Header.astro`, sem tirar nem pôr.
 *
 * Ele fica AQUI, e não numa parte de template, porque é usado uma única vez: uma
 * parte só para si mesma seria uma camada a mais sem nenhum reuso. As partes
 * existem para o que se repete — a lista de textos, o cartão do livro.
 *
 * A foto da marca tem `alt` vazio de propósito: o link ao lado já se chama
 * @viniciuscodes, e anunciar a descrição da foto antes do nome faria quem usa
 * leitor de tela ouvir a mesma informação duas vezes. `width` e `height` estão
 * no HTML para o cabeçalho não pular enquanto a imagem chega, e `loading="eager"`
 * porque ela está acima da dobra em todas as páginas.
 */
?>
<header class="masthead masthead--substack" style="padding: 12px 20px; border-bottom: 1px solid var(--borda, #eaeaea); background: var(--fundo, #fff); position: sticky; top: 0; z-index: 100;">
	<div class="masthead__top" style="max-width: 900px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; width: 100%;">
		<a class="masthead__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit;">
			<img src="<?php echo esc_url( home_url( '/wp-content/uploads/2026/10/favicon-circle-150x150.png' ) ); ?>" alt="" width="36" height="36" style="border-radius: 50%; object-fit: cover;" />
			<span style="font-weight: 700; font-size: var(--text-base); letter-spacing: -0.01em;">Vinicius Oliveira</span>
		</a>

		<nav class="masthead__nav" style="display: flex; align-items: center; gap: 14px; font-size: var(--text-sm);">
			<a href="<?php echo esc_url( home_url( '/posts/' ) ); ?>" style="color: var(--cor-meta, #757575); text-decoration: none;">posts</a>
			<a href="<?php echo esc_url( home_url( '/sobre/' ) ); ?>" style="color: var(--cor-meta, #757575); text-decoration: none;">sobre</a>
			<a href="<?php echo esc_url( home_url( '/viagens/' ) ); ?>" style="color: var(--cor-meta, #757575); text-decoration: none;">viagens</a>
			<a href="<?php echo esc_url( home_url( '/blogroll/' ) ); ?>" style="color: var(--cor-meta, #757575); text-decoration: none;">blogroll</a>
			<a href="<?php echo esc_url( home_url( '/contato/' ) ); ?>" style="color: var(--cor-meta, #757575); text-decoration: none;">contato</a>
			<a href="<?php echo esc_url( home_url( '/feed.xml' ) ); ?>" class="btn-rss-pill" style="background-color: var(--color-accent); color: #fff !important; padding: 5px 12px; border-radius: 20px; font-weight: 600; font-size: var(--text-xs); text-decoration: none;">rss</a>
			
			<button class="theme-toggle" type="button" data-theme-toggle role="switch" aria-checked="false" aria-label="tema escuro" style="background: none; border: none; cursor: pointer; padding: 4px; display: inline-flex; align-items: center; color: var(--cor-meta, #757575);">
				<span class="theme-toggle__icon theme-toggle__icon--light"><?php echo vc_icone( 'sun', 16 ); ?></span>
				<span class="theme-toggle__icon theme-toggle__icon--dark"><?php echo vc_icone( 'moon', 16 ); ?></span>
			</button>
		</nav>
	</div>
</header>

<div class="topics-bar" style="border-bottom: 1px solid var(--borda, #eaeaea); background: var(--fundo, #fff); position: relative; z-index: 90;">
	<div style="max-width: 900px; margin: 0 auto; padding: 8px 20px; display: flex; gap: 16px; align-items: center; justify-content: center; font-size: var(--text-sm); overflow: visible; flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none;">
		<?php
		// 1. Busca todas as categorias com posts calculados em tempo real
		$vc_todos_temas = get_categories( array(
			'taxonomy'   => 'category',
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => true,
		) );

		// 2. Separa os Top 5 e o Restante para o menu "Mais"
		$vc_top_5 = array_slice( $vc_todos_temas, 0, 5 );
		$vc_resto = array_slice( $vc_todos_temas, 5 );

		foreach ( $vc_top_5 as $vc_t ) :
			$vc_href = '/tema/' . $vc_t->slug . '/';
			$vc_icone_svg = vc_icone( vc_icone_do_tema( $vc_t->slug ), 15 );
		?>
			<a href="<?php echo esc_url( $vc_href ); ?>" style="display: inline-flex; align-items: center; gap: 6px; color: var(--cor-meta, #757575); text-decoration: none; font-weight: 500; white-space: nowrap;">
				<span style="display: inline-flex; align-items: center; opacity: 0.85;"><?php echo $vc_icone_svg; ?></span>
				<span><?php echo esc_html( mb_strtolower( (string) $vc_t->name, 'UTF-8' ) ); ?></span>
			</a>
		<?php endforeach; ?>

		<?php if ( ! empty( $vc_resto ) ) : ?>
			<!-- Dropdown Elegante "Mais Temas" -->
			<div class="topics-dropdown-wrap" style="position: relative; display: inline-block;">
				<button type="button" class="topics-dropdown-btn" id="topicsDropdownBtn" aria-expanded="false" style="display: inline-flex; align-items: center; gap: 4px; background: none; border: 1px solid var(--borda, #eaeaea); border-radius: 14px; padding: 3px 10px; font-size: var(--text-xs); color: var(--cor-meta, #757575); cursor: pointer; font-family: inherit;">
					<span>mais</span>
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.2s;"><path d="m6 9 6 6 6-6"/></svg>
				</button>

				<div class="topics-dropdown-menu" id="topicsDropdownMenu" style="display: none; position: absolute; top: calc(100% + 8px); left: 0; background: var(--fundo, #ffffff); border: 1px solid var(--borda, #eaeaea); border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding: 6px 0; min-width: 190px; z-index: 1000;">
					<?php foreach ( $vc_resto as $vc_r ) :
						$vc_r_href = '/tema/' . $vc_r->slug . '/';
						$vc_r_icone = vc_icone( vc_icone_do_tema( $vc_r->slug ), 14 );
					?>
						<a href="<?php echo esc_url( $vc_r_href ); ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 7px 14px; color: var(--cor-texto, #292929); text-decoration: none; font-size: var(--text-sm); transition: background 0.15s;">
							<span style="display: inline-flex; align-items: center; gap: 8px;">
								<span style="opacity: 0.8;"><?php echo $vc_r_icone; ?></span>
								<span><?php echo esc_html( mb_strtolower( (string) $vc_r->name, 'UTF-8' ) ); ?></span>
							</span>
							<span style="font-size: var(--text-xs); color: var(--cor-meta, #757575); background: rgba(127,127,127,0.1); border-radius: 10px; padding: 1px 6px;"><?php echo (int) $vc_r->count; ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var btn = document.getElementById('topicsDropdownBtn');
	var menu = document.getElementById('topicsDropdownMenu');
	if (!btn || !menu) return;

	btn.addEventListener('click', function(e) {
		e.stopPropagation();
		var isHidden = menu.style.display === 'none';
		menu.style.display = isHidden ? 'block' : 'none';
		btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
		var arrow = btn.querySelector('svg');
		if (arrow) arrow.style.transform = isHidden ? 'rotate(180deg)' : 'none';
	});

	document.addEventListener('click', function(e) {
		if (!menu.contains(e.target) && e.target !== btn) {
			menu.style.display = 'none';
			btn.setAttribute('aria-expanded', 'false');
			var arrow = btn.querySelector('svg');
			if (arrow) arrow.style.transform = 'none';
		}
	});
});
</script>

<div class="sidebar-backdrop" data-sidebar-close aria-hidden="true"></div>

<div class="layout">
	<?php get_template_part( 'template-parts/sidebar' ); ?>

	<main class="content" id="conteudo">
		<div class="content__inner">
