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

/*
 * NA PÁGINA DE UMA NOTA, O TÍTULO É O TEXTO DELA. A regra mora em
 * `vc_titulo_da_nota()`, e vale aqui e no `<title>`: os dois têm de dizer a mesma
 * coisa, senão a aba e a prévia de link divergem.
 */
if ( $vc_singular && defined( 'VC_NOTA' ) && VC_NOTA === get_post_type( $vc_id ) ) {
	$vc_titulo = vc_titulo_da_nota( (int) $vc_id );
} else {
	$vc_titulo = $vc_singular ? get_the_title( $vc_id ) : wp_get_document_title();
}
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
	<?php
	/*
	 * AS TAGS DE IMAGEM SÓ SAEM QUANDO HÁ IMAGEM.
	 *
	 * `$vc_imagem` vem vazia nas notas sem imagem própria (ver
	 * `vc_imagem_social()`). Imprimir `content=""` seria pior do que não imprimir:
	 * um Open Graph com `og:image` vazio é um cartão quebrado, e não um cartão sem
	 * imagem. O `twitter:card` acompanha, porque `summary_large_image` sem imagem
	 * rende um retângulo vazio.
	 */
	?>
	<?php if ( '' !== $vc_imagem ) : ?>
	<meta property="og:image" content="<?php echo esc_url( $vc_imagem ); ?>" />
	<meta property="og:image:alt" content="<?php echo esc_attr( $vc_titulo ); ?>" />
	<?php endif; ?>
	<meta property="og:locale" content="pt_BR" />

	<meta name="twitter:card" content="<?php echo '' !== $vc_imagem ? 'summary_large_image' : 'summary'; ?>" />
	<meta name="twitter:title" content="<?php echo esc_attr( $vc_titulo ); ?>" />
	<meta name="twitter:description" content="<?php echo esc_attr( $vc_descricao ); ?>" />
	<?php if ( '' !== $vc_imagem ) : ?>
	<meta name="twitter:image" content="<?php echo esc_url( $vc_imagem ); ?>" />
	<?php endif; ?>

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
	<link rel="icon" href="<?php echo esc_url( get_theme_file_uri( 'assets/icons/favicon.svg' ) ); ?>?v=2" type="image/svg+xml" />
	<meta name="theme-color" content="#faf9f5" media="(prefers-color-scheme: light)" />
	<meta name="theme-color" content="#16181a" media="(prefers-color-scheme: dark)" />

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
<header class="masthead">
	<?php
	/*
	 * A MARCA DO CABEÇALHO É O h-card DO SITE.
	 *
	 * É daqui que o Bridgy Fed tira o perfil no Fediverso: nome, foto e o endereço
	 * da home. A marca já era, sem saber, a coisa mais parecida com um h-card no site:
	 * uma foto, um link para a home e um nome.
	 *
	 * O h-card É O DIV, E NÃO O LINK, e isso foi medido, não escolhido. Com
	 * `h-card u-url` no próprio `<a>`, o parser do microformats2 devolve o h-card com a
	 * LISTA DE URL VAZIA, e um h-card sem `u-url` não é reconhecido como o do domínio.
	 * Com o `h-card` no div e o `u-url` no link de dentro, a URL sai certa. Testado com
	 * as duas formas antes de escolher.
	 *
	 * O `display: contents` no div é para ele não virar um terceiro item do `masthead`,
	 * que é `flex` com `space-between`: sem isso, as ferramentas sairiam do canto e
	 * iriam para o meio.
	 *
	 * O NOME VAI ESCONDIDO, e não é truque: o que se lê na tela é o arroba, que é como o
	 * site se chama em todo lugar. O `p-name` é para quem lê o HTML, e "@viniciuscodes"
	 * não é nome de gente. O `visually-hidden` mantém a marca igual e o dado correto.
	 */
	?>
	<div class="h-card masthead__quem">
		<a class="masthead__brand u-url" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img
				class="u-photo"
				src="<?php echo esc_url( vc_midia( 'vinicius-profile.webp' ) ); ?>"
				alt=""
				width="28"
				height="28"
				loading="eager"
				decoding="async"
			/>
			<span class="p-name visually-hidden"><?php echo esc_html( vc_nome_do_autor() ); ?></span>
			<span>@viniciuscodes</span>
		</a>
	</div>

	<div class="masthead__tools">
		<button
			class="theme-toggle"
			type="button"
			data-theme-toggle
			role="switch"
			aria-checked="false"
			aria-label="tema escuro"
		>
			<span class="theme-toggle__icon theme-toggle__icon--light">
				<?php echo vc_icone( 'sun', 16 ); ?>
			</span>
			<span class="theme-toggle__icon theme-toggle__icon--dark">
				<?php echo vc_icone( 'moon', 16 ); ?>
			</span>
		</button>

		<button
			class="sidebar-toggle"
			type="button"
			data-sidebar-toggle
			aria-controls="barra-lateral"
			aria-expanded="false"
			aria-label="abrir menu"
		>
			<?php echo vc_icone( 'menu', 18 ); ?>
		</button>
	</div>
</header>

<div class="sidebar-backdrop" data-sidebar-close aria-hidden="true"></div>

<div class="layout">
	<?php get_template_part( 'template-parts/sidebar' ); ?>

	<main class="content" id="conteudo">
		<div class="content__inner">
