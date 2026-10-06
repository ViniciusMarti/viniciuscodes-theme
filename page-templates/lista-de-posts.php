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

/*
 * TODOS os textos, sem paginação do servidor.
 *
 * O endereço da página 2 era `/posts/page/2/`. Ele não existe mais: a paginação
 * é do cliente agora, junto do filtro, porque filtrar sobre uma fatia paginada
 * mostraria menos do que existe. A regra de reescrita continua no mu-plugin, e
 * por isso o endereço antigo ainda responde — a saída abaixo o devolve para cá.
 */
$vc_textos = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	)
);

/*
 * Página numerada não existe mais: volta para a lista, e não devolve 200 com uma
 * tela repetida. `/posts/page/99/` responde porque a regra de reescrita casa
 * qualquer número, então sem esta saída ele seria um endereço infinito de
 * conteúdo repetido, bom para ninguém.
 */
if ( $vc_pagina > 1 ) {
	wp_safe_redirect( home_url( '/posts/' ), 301 );
	exit;
}

/*
 * AQUI HAVIA A BARRA DE FILTRO POR TEMA — os botões com ícone, o "todas" e a
 * contagem em `role="status"` — e, acima dela, o rótulo "do mais novo para o
 * mais antigo". Os dois saíram a pedido do dono.
 *
 * Com a barra foi embora a consulta de temas que só existia para alimentá-la:
 * `vc_temas_ordenados()` continua em `functions.php`, usado pela home e pela
 * barra lateral, e os temas vazios continuam sendo assunto de lá. A ordem da
 * lista não mudou: a consulta acima continua vindo da nota mais nova para a
 * mais antiga.
 *
 * O que fica nesta página é a lista inteira e a paginação — e é por isso que a
 * folha do filtro continua sendo carregada (`functions.php`): é dela a regra
 * que esconde as linhas fora da página.
 */
?>
<article>
	<header class="term-header">
		<h1 class="term-header__name"><?php the_title(); ?></h1>

		<?php
		/*
		 * A DESCRIÇÃO DE UMA LINHA, IGUAL À DA PÁGINA DE NOTAS.
		 *
		 * A `/notas/` diz o que uma nota é em uma frase, e a lista de textos não
		 * dizia nada: o `<h1>` sozinho diz o assunto e não diz o que aquilo tudo é.
		 * Como as duas páginas são a mesma coisa em escalas diferentes, a frase
		 * segue a mesma forma, e a diferença entre as duas fica dita: uma é o que
		 * cabe numa nota, a outra é o que não coube.
		 */
		?>
		<p class="term-header__desc">
			o que eu escrevo quando o assunto não cabe numa nota.
		</p>
	</header>

	<?php
	/*
	 * A lista vai embrulhada em `[data-lista]`, que é por onde o script a acha —
	 * o `post-list` sozinho também aparece na home e nos arquivos, e lá não há
	 * paginação para ligar: são páginas do WordPress, com a fatia já recortada
	 * pelo servidor.
	 */
	?>
	<div data-lista>
		<?php
		$vc_original         = $GLOBALS['wp_query'];
		$GLOBALS['wp_query'] = $vc_textos; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		/*
		 * A lista sai sem resumo, como em todo o site: esta página existe para varrer
		 * títulos e achar o que ler.
		 */
		get_template_part(
			'template-parts/lista-de-textos',
			null,
			array(
				'vazio' => 'nenhum post publicado ainda.',
			)
		);

		$GLOBALS['wp_query'] = $vc_original; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		wp_reset_postdata();
		?>
	</div>

	<?php
	/*
	 * O pager nasce vazio e escondido: quem escreve a marcação é o script, com os
	 * mesmos nomes de classe do `vc_paginacao()` — o desenho não muda, só a conta
	 * passa a ser feita sobre o que sobrou do filtro.
	 */
	?>
	<nav class="paginacao" data-paginacao hidden aria-label="paginação"></nav>
</article>

<?php
get_footer();
