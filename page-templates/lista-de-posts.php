<?php
/**
 * Template Name: Lista de todos os textos
 *
 * A página `/posts/` do site: todos os textos publicados, sem recorte.
 *
 * Ela existe para o link "posts" da barra lateral não morrer. O endereço é o
 * mesmo do site antigo, e por isso vale manter mesmo com a home já listando os
 * textos: quem tem `/posts/` guardado continua chegando no lugar certo.
 *
 * A lista sai de uma consulta própria, com todos os textos, e não do Loop
 * padrão — o Loop de uma página devolve a própria página. `no_found_rows` diz
 * ao banco para não contar o total, porque aqui ninguém vai paginar.
 *
 * A BARRA DE FILTRO POR TEMA NÃO EXISTE MAIS AQUI, nem o rótulo de ordem acima
 * do título: os dois saíram a pedido do dono. O que a página faz agora é uma
 * coisa só — listar todos os textos, da nota mais nova para a mais antiga.
 *
 * A LISTA SAI INTEIRA, SEM PAGINAÇÃO NO SERVIDOR, e é isso que faz a paginação
 * daqui ser honesta: as dez linhas por página que o WordPress fazia recortavam a
 * lista antes de ela chegar ao navegador, e qualquer conta feita no cliente
 * enxergaria só a fatia da página atual, sem saber. A lista sai toda e o
 * `assets/filtros.js`, carregado só nesta página, esconde e mostra as fatias de
 * dez. Sem script também está certo: aí a lista inteira fica visível, em ordem,
 * que é a mesma escolha do site original. Quem chega em `/posts/page/2/` é
 * mandado de volta para `/posts/`, porque a página numerada deixou de existir.
 *
 * O cabeçalho é o mesmo `term-header` do arquivo de um tema, e não o
 * `article__header` de uma página: esta página é uma lista, e o site a trata
 * como tal. Sem o rótulo de cima, o `<h1>` fica sozinho, e é ele quem diz o
 * assunto.
 *
 * A folha do filtro (`assets/filtros.css`) continua sendo carregada só aqui, em
 * `functions.php`: é dela a regra que esconde as linhas fora da página
 * (`.post-list .post-item[hidden]`), sem a qual a paginação não funcionaria. As
 * regras dos botões ficaram sem uso, guardadas para o caso de a barra voltar.
 *
 * @package ViniciusCodes
 */

get_header();

/*
 * Dez por página, com a contagem vinda do próprio WordPress.
 *
 * O endereço da página 2 é `/posts/page/2/`. A regra de reescrita está no
 * mu-plugin porque `/posts/` é uma PÁGINA, e o WordPress sozinho só pagina
 * arquivo — sem a regra, o endereço cairia no 404.
 */
$vc_pagina = max( 1, (int) get_query_var( 'paged' ) );

$vc_textos = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'paged'          => $vc_pagina,
	)
);
?>
<article>
	<header class="term-header">
		<h1 class="term-header__name"><?php the_title(); ?></h1>
	</header>

	<div data-lista>
		<?php
		$vc_original         = $GLOBALS['wp_query'];
		$GLOBALS['wp_query'] = $vc_textos; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		/*
		 * A lista sai com resumo de 2-3 linhas igual ao feed da home.
		 */
		get_template_part(
			'template-parts/lista-de-textos',
			null,
			array(
				'vazio'          => 'nenhum post publicado ainda.',
				'mostrar_resumo' => true,
			)
		);

		$GLOBALS['wp_query'] = $vc_original; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		wp_reset_postdata();
		?>
	</div>

	<div class="feed-paginacao-wrap">
		<?php if ( $vc_textos->max_num_pages > 1 ) : ?>
			<div class="feed-paginacao">
				<?php vc_paginacao( (int) $vc_textos->max_num_pages, $vc_pagina, home_url( '/posts/page/%#%/' ) ); ?>
			</div>
		<?php endif; ?>
	</div>
</article>

<?php
get_footer();
