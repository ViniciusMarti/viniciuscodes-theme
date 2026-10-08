<?php
/**
 * Fonte única de dados do Blogroll para o tema ViniciusCodes.
 * Usado na página /blogroll/ e no assistente de conexões do editor.
 *
 * @package ViniciusCodes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retorna todos os itens do Blogroll ordenados.
 *
 * @return array<int, array{nome: string, url: string, dominio: string, favicon: string}>
 */
function vc_get_blogroll_items(): array {
	return array(
		array(
			'nome'    => 'aline Valek',
			'url'     => 'https://alinevalek.com.br/',
			'dominio' => 'alinevalek.com.br',
			'favicon' => 'alinevalek.com.br.png',
		),
		array(
			'nome'    => 'Baty',
			'url'     => 'https://baty.net/',
			'dominio' => 'baty.net',
			'favicon' => 'baty.net.png',
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
			'nome'    => 'Chris McCormick',
			'url'     => 'https://mccormick.cx/',
			'dominio' => 'mccormick.cx',
			'favicon' => 'mccormick.cx.jpg',
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
			'nome'    => 'dennis Nunes',
			'url'     => 'https://blog.nunesdennis.me/',
			'dominio' => 'blog.nunesdennis.me',
			'favicon' => 'blog.nunesdennis.me.jpg',
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
			'nome'    => "Hey! I'm With the Band",
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
			'nome'    => 'Sandy Uraz',
			'url'     => 'https://sandyuraz.com/',
			'dominio' => 'sandyuraz.com',
			'favicon' => 'sandyuraz.com.ico',
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
			'nome'    => 'Winnie Lim',
			'url'     => 'https://winnielim.org/',
			'dominio' => 'winnielim.org',
			'favicon' => 'winnielim.org.png',
		),
		array(
			'nome'    => 'Zachary Kai',
			'url'     => 'https://zacharykai.net/',
			'dominio' => 'zacharykai.net',
			'favicon' => 'zacharykai.net.ico',
		),
	);
}
