<?php
/**
 * Template Name: Blogroll
 *
 * A página `/blogroll/`: sites e blogs que eu acompanho pela internet.
 *
 * Cada linha exibe o favicon local do site, o nome do blog e o domínio correspondente.
 * O clique na linha abre o site em uma nova aba.
 *
 * @package ViniciusCodes
 */

get_header();

$vc_blogroll = array(
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

$vc_canais_youtube = array(
	array( 'nome' => 'Coisa de Nerd', 'handle' => '@coisadenerd', 'url' => 'https://www.youtube.com/@coisadenerd' ),
	array( 'nome' => 'Manual do Mundo', 'handle' => '@manualdomundo', 'url' => 'https://www.youtube.com/@manualdomundo' ),
	array( 'nome' => 'Tech Isma', 'handle' => '@techisma', 'url' => 'https://www.youtube.com/@techisma' ),
	array( 'nome' => 'Técnico Sincero', 'handle' => '@Tecnico.Sincero', 'url' => 'https://www.youtube.com/@Tecnico.Sincero' ),
	array( 'nome' => 'André Miranda', 'handle' => '@andremiranda.youtube', 'url' => 'https://www.youtube.com/@andremiranda.youtube' ),
	array( 'nome' => 'Isabela Boscov', 'handle' => '@IsabelaBoscov', 'url' => 'https://www.youtube.com/@IsabelaBoscov' ),
	array( 'nome' => 'PH Santos', 'handle' => '@phsantos', 'url' => 'https://www.youtube.com/@phsantos' ),
	array( 'nome' => 'REVELANDO', 'handle' => '@revelando_br', 'url' => 'https://www.youtube.com/@revelando_br' ),
	array( 'nome' => 'Japão Nosso De Cada Dia', 'handle' => '@japaonossodecadadia', 'url' => 'https://www.youtube.com/@japaonossodecadadia' ),
	array( 'nome' => 'Par de Malas', 'handle' => '@PardeMalas', 'url' => 'https://www.youtube.com/@PardeMalas' ),
	array( 'nome' => 'Vou na Janela', 'handle' => '@VounaJanela', 'url' => 'https://www.youtube.com/@VounaJanela' ),
	array( 'nome' => 'Vou sem volta', 'handle' => '@Vousemvolta', 'url' => 'https://www.youtube.com/@Vousemvolta' ),
	array( 'nome' => 'Eixo XYZ', 'handle' => '@eixoxyz', 'url' => 'https://www.youtube.com/@eixoxyz' ),
	array( 'nome' => 'Minha Pequena Revolução', 'handle' => '@PequenaRevolucao', 'url' => 'https://www.youtube.com/@PequenaRevolucao' ),
	array( 'nome' => 'Not Just Bikes', 'handle' => '@NotJustBikes', 'url' => 'https://www.youtube.com/@NotJustBikes' ),
	array( 'nome' => 'Nátaly Neri', 'handle' => '@NatalyNeri', 'url' => 'https://www.youtube.com/@NatalyNeri' ),
	array( 'nome' => 'Arbóreo Literário', 'handle' => '@ArboreoLiterario', 'url' => 'https://www.youtube.com/@ArboreoLiterario' ),
	array( 'nome' => 'Canal Nostalgia', 'handle' => '@nostalgia', 'url' => 'https://www.youtube.com/@nostalgia' ),
	array( 'nome' => 'Felipe Herrera', 'handle' => '@felipeherreraof', 'url' => 'https://www.youtube.com/@felipeherreraof' ),
	array( 'nome' => 'Jão', 'handle' => '@JAO', 'url' => 'https://www.youtube.com/@JAO' ),
	array( 'nome' => 'Cadê a chave?', 'handle' => '@cadeachave', 'url' => 'https://www.youtube.com/@cadeachave' ),
	array( 'nome' => 'Lincoln Lohan', 'handle' => '@olincolnlohan', 'url' => 'https://www.youtube.com/@olincolnlohan' ),
	array( 'nome' => 'Matheus Menucci', 'handle' => '@menuccimatheus', 'url' => 'https://www.youtube.com/@menuccimatheus' ),
	array( 'nome' => 'Tito Melo', 'handle' => '@oTitoMelo', 'url' => 'https://www.youtube.com/@oTitoMelo' ),
	array( 'nome' => 'CORTES - Leon e Nilce [Oficial]', 'handle' => '@cortes-leonenilceoficial4101', 'url' => 'https://www.youtube.com/@cortes-leonenilceoficial4101' ),
	array( 'nome' => 'Matando Matheus a Grito', 'handle' => '@matandomatheus', 'url' => 'https://www.youtube.com/@matandomatheus' ),
	array( 'nome' => 'Matheus e Guigo Show', 'handle' => '@matheuseguigoshow', 'url' => 'https://www.youtube.com/@matheuseguigoshow' ),
	array( 'nome' => 'Ramon e Victor', 'handle' => '@ramonevictor', 'url' => 'https://www.youtube.com/@ramonevictor' ),
	array( 'nome' => 'Almanaque SOS', 'handle' => '@AlmanaqueSOS', 'url' => 'https://www.youtube.com/@AlmanaqueSOS' ),
	array( 'nome' => 'Almanaque SOS Receitinhas', 'handle' => '@SOSReceitinhas', 'url' => 'https://www.youtube.com/@SOSReceitinhas' ),
	array( 'nome' => 'Que nem marceneiro', 'handle' => '@quenem_marceneiro', 'url' => 'https://www.youtube.com/@quenem_marceneiro' ),
);

?>
<article data-page="blogroll">
	<header class="term-header">
		<h1 class="term-header__name"><?php the_title(); ?></h1>
		<p class="term-header__desc">blogs e páginas pessoais que eu acompanho pela internet.</p>
	</header>

	<ul class="blogroll-list">
		<?php foreach ( $vc_blogroll as $vc_item ) : ?>
			<?php
			$vc_fav_arquivo = $vc_item['favicon'] ?? ( $vc_item['dominio'] . '.png' );
			$vc_favicon_src = get_theme_file_uri( "assets/favicons/{$vc_fav_arquivo}" );
			?>
			<li class="blogroll-item">
				<a href="<?php echo esc_url( $vc_item['url'] ); ?>" class="blogroll-item__link" target="_blank" rel="noopener noreferrer">
					<img class="blogroll-item__favicon" src="<?php echo esc_url( $vc_favicon_src ); ?>" alt="" width="18" height="18" loading="lazy" decoding="async">
					<span class="blogroll-item__nome"><?php echo esc_html( $vc_item['nome'] ); ?></span>
					<span class="blogroll-item__dominio" aria-hidden="true"><?php echo esc_html( $vc_item['dominio'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

	<header class="term-header" style="margin-top: 3.5rem;">
		<h2 class="term-header__name">canais no youtube</h2>
		<p class="term-header__desc">criadores, ensaístas e produções audiovisuais que acompanho no youtube.</p>
	</header>

	<ul class="blogroll-list">
		<?php foreach ( $vc_canais_youtube as $vc_canal ) : ?>
			<li class="blogroll-item">
				<a href="<?php echo esc_url( $vc_canal['url'] ); ?>" class="blogroll-item__link" target="_blank" rel="noopener noreferrer">
					<span class="blogroll-item__favicon" style="display:inline-flex; align-items:center; justify-content:center; color: #ff0000; width: 18px; height: 18px;">
						<?php echo vc_icone( 'youtube', 16 ); ?>
					</span>
					<span class="blogroll-item__nome"><?php echo esc_html( $vc_canal['nome'] ); ?></span>
					<span class="blogroll-item__dominio" aria-hidden="true"><?php echo esc_html( $vc_canal['handle'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

</article>

<?php
get_footer();
