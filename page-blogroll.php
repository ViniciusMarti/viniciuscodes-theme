<?php
/**
 * Template Name: Blogroll
 *
 * A página `/blogroll/`: sites e blogs que eu acompanho pela internet.
 *
 * Cada linha exibe o favicon do site, o nome do blog e o domínio correspondente.
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
		'feed'    => 'https://alinevalek.com.br/feed/',
	),
	array(
		'nome'    => 'Baty',
		'url'     => 'https://baty.net/',
		'dominio' => 'baty.net',
		'feed'    => 'https://baty.net/index.xml',
	),
	array(
		'nome'    => 'blog do TH',
		'url'     => 'https://blogdoth.bearblog.dev/blog/',
		'dominio' => 'blogdoth.bearblog.dev',
		'feed'    => 'https://blogdoth.bearblog.dev/feed/',
	),
	array(
		'nome'    => 'BMRTT',
		'url'     => 'https://bamoretti.com/',
		'dominio' => 'bamoretti.com',
		'feed'    => 'https://www.bamoretti.com/feeds/posts/default',
	),
	array(
		'nome'    => 'Bruno Pulis',
		'url'     => 'https://brunopulis.com',
		'dominio' => 'brunopulis.com',
		'feed'    => 'https://brunopulis.com/feeds/blog/rss.xml',
	),
	array(
		'nome'    => 'Chris McCormick',
		'url'     => 'https://mccormick.cx/',
		'dominio' => 'mccormick.cx',
		'feed'    => 'https://mccormick.cx/@chris.rss',
	),
	array(
		'nome'    => 'Cosmoliko',
		'url'     => 'https://cosmoliko.com/',
		'dominio' => 'cosmoliko.com',
		'feed'    => 'https://cosmoliko.com/rss/',
	),
	array(
		'nome'    => 'daniel Santos',
		'url'     => 'https://danielsantos.org/',
		'dominio' => 'danielsantos.org',
		'feed'    => 'https://danielsantos.org/blog/feed.xml',
	),
	array(
		'nome'    => 'dennis Nunes',
		'url'     => 'https://blog.nunesdennis.me/',
		'dominio' => 'blog.nunesdennis.me',
		'feed'    => 'https://blog.nunesdennis.me/feed',
	),
	array(
		'nome'    => 'Gustavo Belo',
		'url'     => 'https://www.gustavobelo.com/',
		'dominio' => 'gustavobelo.com',
		'feed'    => 'https://www.gustavobelo.com/feed',
	),
	array(
		'nome'    => 'Gustavo Ribeiro',
		'url'     => 'https://gustavoribeiro.net/blog',
		'dominio' => 'gustavoribeiro.net',
		'feed'    => 'https://gustavoribeiro.net/blog/rss.xml',
	),
	array(
		'nome'    => 'Hey! I\'m With the Band',
		'url'     => 'https://www.heyimwiththeband.com.br/',
		'dominio' => 'heyimwiththeband.com.br',
		'feed'    => 'https://www.heyimwiththeband.com.br/feeds/posts/default',
	),
	array(
		'nome'    => 'Igor Medeiroz',
		'url'     => 'https://igormedeiroz.com/',
		'dominio' => 'igormedeiroz.com',
		'feed'    => 'https://www.igormedeiroz.com/feeds/posts/default',
	),
	array(
		'nome'    => 'Inventando Assunto',
		'url'     => 'https://www.inventandoassunto.com/',
		'dominio' => 'inventandoassunto.com',
		'feed'    => 'https://www.inventandoassunto.com/feeds/posts/default',
	),
	array(
		'nome'    => 'Irrelefante',
		'url'     => 'https://irrelefante.com.br/',
		'dominio' => 'irrelefante.com.br',
		'feed'    => 'https://irrelefante.com.br/feed.xml',
	),
	array(
		'nome'    => 'Maira Namba',
		'url'     => 'https://www.mairanamba.com/',
		'dominio' => 'mairanamba.com',
		'feed'    => 'https://www.mairanamba.com/feed/',
	),
	array(
		'nome'    => 'Manual do Usuário',
		'url'     => 'https://manualdousuario.net',
		'dominio' => 'manualdousuario.net',
		'feed'    => 'https://manualdousuario.net/feed/',
	),
	array(
		'nome'    => 'Marcos Ramon',
		'url'     => 'https://marcosramon.net/',
		'dominio' => 'marcosramon.net',
		'feed'    => 'https://marcosramon.net/feed.xml',
	),
	array(
		'nome'    => 'meu Tédio',
		'url'     => 'https://www.meutedio.com/',
		'dominio' => 'meutedio.com',
		'feed'    => 'https://www.meutedio.com/feeds/posts/default?alt=rss',
	),
	array(
		'nome'    => 'o sol na cabeça',
		'url'     => 'https://osolnacabeca.com.br/',
		'dominio' => 'osolnacabeca.com.br',
		'feed'    => 'https://osolnacabeca.com.br/feed/',
	),
	array(
		'nome'    => 'Papacapim',
		'url'     => 'https://www.papacapim.org/',
		'dominio' => 'papacapim.org',
		'feed'    => 'https://www.papacapim.org/feed/',
	),
	array(
		'nome'    => 'pedro.dalbo',
		'url'     => 'https://pedro.dalbo.me/',
		'dominio' => 'pedro.dalbo.me',
		'feed'    => 'https://pedro.dalbo.me/feed.xml',
	),
	array(
		'nome'    => 'prazer, Willian',
		'url'     => 'https://prazerwillian.blogspot.com/',
		'dominio' => 'prazerwillian.blogspot.com',
		'feed'    => 'https://prazerwillian.blogspot.com/feeds/posts/default?alt=rss',
	),
	array(
		'nome'    => 'rádio escafandro',
		'url'     => 'https://radioescafandro.com/',
		'dominio' => 'radioescafandro.com',
		'feed'    => 'https://radioescafandro.com/feed/podcast/',
	),
	array(
		'nome'    => 'Rua da Feira 🥬',
		'url'     => 'https://ruadafeira.varzea.recife.br/',
		'dominio' => 'ruadafeira.varzea.recife.br',
		'feed'    => 'https://ruadafeira.varzea.recife.br/rss/',
	),
	array(
		'nome'    => 'Sandy Uraz',
		'url'     => 'https://sandyuraz.com/',
		'dominio' => 'sandyuraz.com',
		'feed'    => 'https://sandyuraz.com/feed.xml',
	),
	array(
		'nome'    => 'Taddone',
		'url'     => 'https://www.taddone.it/',
		'dominio' => 'taddone.it',
		'feed'    => 'https://www.taddone.it/feed/',
	),
	array(
		'nome'    => 'tech Demo',
		'url'     => 'https://techdemo.bearblog.dev/',
		'dominio' => 'techdemo.bearblog.dev',
		'feed'    => 'https://techdemo.bearblog.dev/feed/',
	),
	array(
		'nome'    => 'th.blog.br',
		'url'     => 'https://th.blog.br/',
		'dominio' => 'th.blog.br',
		'feed'    => 'https://th.blog.br/feed/',
	),
	array(
		'nome'    => 'Winnie Lim',
		'url'     => 'https://winnielim.org/',
		'dominio' => 'winnielim.org',
		'feed'    => 'https://winnielim.org/feed/',
	),
	array(
		'nome'    => 'Zachary Kai',
		'url'     => 'https://zacharykai.net/',
		'dominio' => 'zacharykai.net',
		'feed'    => 'https://zacharykai.net/assets/rss.xml',
	),
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
			$vc_fav_dominio = rawurlencode( $vc_item['dominio'] );
			$vc_favicon     = "https://www.google.com/s2/favicons?domain={$vc_fav_dominio}&sz=32";
			?>
			<li class="blogroll-item">
				<a href="<?php echo esc_url( $vc_item['url'] ); ?>" class="blogroll-item__link" target="_blank" rel="noopener noreferrer">
					<img class="blogroll-item__favicon" src="<?php echo esc_url( $vc_favicon ); ?>" alt="" width="18" height="18" loading="lazy" decoding="async">
					<span class="blogroll-item__nome"><?php echo esc_html( $vc_item['nome'] ); ?></span>
					<span class="blogroll-item__dominio" aria-hidden="true"><?php echo esc_html( $vc_item['dominio'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</article>

<?php
get_footer();
