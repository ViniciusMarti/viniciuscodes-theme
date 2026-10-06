<?php
/**
 * Template Name: Links citados (automático)
 *
 * Reúne todos os links externos citados nos textos, agrupados por site.
 *
 * ESTA É A ÚNICA PÁGINA DO SITE QUE NÃO PODERIA SER CONTEÚDO. Ela não tem texto
 * próprio: a lista sai dos textos publicados, e o que faz um link aparecer ali é
 * citar uma fonte no corpo de uma nota. Guardar isso como HTML pronto daria uma
 * página que envelhece sozinha — cada texto novo com fonte deixaria a lista
 * desatualizada em silêncio.
 *
 * A LEITURA É DO CONTEÚDO CRU, e não do que `the_content` devolve: interessa só
 * o atributo `href` de cada link, e passar o texto pelo filtro de conteúdo aqui
 * custaria converter blocos, renderizar ícones e formatar cada parágrafo de cada
 * texto para depois jogar quase tudo fora.
 *
 * Ficam de fora os links para o próprio site (não são fontes) e os que não têm
 * texto no link — um ícone solto ou uma imagem não dizem nada numa lista que
 * serve para o leitor escolher o que abrir.
 *
 * @package ViniciusCodes
 */

get_header();

$vc_textos = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$vc_dominios     = array();
$vc_com_fonte    = array();
$vc_total_refs   = 0;

foreach ( $vc_textos as $vc_texto ) {
	preg_match_all(
		'/<a\s[^>]*href="(https?:\/\/[^"]+)"[^>]*>(.*?)<\/a>/is',
		$vc_texto->post_content,
		$vc_achados,
		PREG_SET_ORDER
	);

	foreach ( $vc_achados as $vc_achado ) {
		$vc_url    = $vc_achado[1];
		$vc_rotulo = trim( wp_strip_all_tags( $vc_achado[2] ) );
		$vc_host   = (string) wp_parse_url( $vc_url, PHP_URL_HOST );

		if ( '' === $vc_rotulo || '' === $vc_host ) {
			continue;
		}

		if ( false !== strpos( $vc_host, 'viniciuscodes.com.br' ) ) {
			continue;
		}

		$vc_dominios[ $vc_host ][] = array(
			'url'    => $vc_url,
			'rotulo' => $vc_rotulo,
			'nota'   => $vc_texto,
		);

		$vc_com_fonte[ $vc_texto->ID ] = true;
		$vc_total_refs++;
	}
}

// Mais citados primeiro: os sites que voltam são os que o blog realmente usa.
uksort(
	$vc_dominios,
	static function ( $a, $b ) use ( $vc_dominios ) {
		$dif = count( $vc_dominios[ $b ] ) <=> count( $vc_dominios[ $a ] );
		return 0 !== $dif ? $dif : strcoll( $a, $b );
	}
);
?>
<article>
	<header class="article__header">
		<h1 class="article__title"><?php the_title(); ?></h1>
	</header>

	<p class="lede">
		Todo link externo citado nos posts, reunido automaticamente. Não há cadastro manual: basta
		citar uma fonte em um post para ele aparecer aqui.
	</p>

	<ul class="stats">
		<li><strong><?php echo (int) $vc_total_refs; ?></strong> referências mapeadas pelo blog</li>
		<li><strong><?php echo count( $vc_dominios ); ?></strong> sites diferentes</li>
		<li><strong><?php echo count( $vc_com_fonte ); ?></strong> posts citando fontes</li>
	</ul>

	<div class="groups">
		<?php foreach ( $vc_dominios as $vc_host => $vc_refs ) : ?>
			<section class="group">
				<h2 class="group__heading">
					<span class="group__domain"><?php echo esc_html( $vc_host ); ?></span>
					<span class="group__count"><?php echo count( $vc_refs ); ?></span>
				</h2>

				<ul class="refs">
					<?php foreach ( $vc_refs as $vc_ref ) : ?>
						<li class="ref">
							<a class="ref__link" href="<?php echo esc_url( $vc_ref['url'] ); ?>" rel="noopener noreferrer">
								<span class="ref__label"><?php echo esc_html( $vc_ref['rotulo'] ); ?></span>
								<?php echo vc_icone( 'external-link', 14, 'ref__icon' ); ?>
							</a>
							<p class="ref__source">
								em <a href="<?php echo esc_url( get_permalink( $vc_ref['nota'] ) ); ?>"><?php echo esc_html( get_the_title( $vc_ref['nota'] ) ); ?></a>
							</p>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endforeach; ?>
	</div>
</article>

<?php
get_footer();
