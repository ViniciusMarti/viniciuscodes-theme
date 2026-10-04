/*
 * Paginação da lista de textos, em `/posts/`.
 *
 * A BARRA DE FILTRO POR TEMA SAIU DESTA PÁGINA, a pedido do dono, e com ela o
 * filtro. Este arquivo ficou com o outro serviço que ele já fazia: esconder e
 * mostrar as fatias de dez linhas. O nome continua o mesmo porque é por ele que
 * o `functions.php` carrega o script — e porque a razão de a conta ser feita
 * aqui não mudou com a saída da barra.
 *
 * POR QUE NO CLIENTE. Antes, o WordPress paginava de dez em dez, e cada página
 * era um endereço novo. Aqui a lista sai inteira do servidor, e trocar de página
 * é esconder e mostrar linhas na mesma lista: o endereço não muda, o pager conta
 * sobre tudo o que existe, e a rolagem volta para o começo da lista, que é onde
 * a linha nova aparece.
 *
 * SEM JAVASCRIPT NADA DISSO APARECE: o pager nasce `hidden` e é este arquivo que
 * o revela. Sem script, a lista inteira fica visível, em ordem, com todas as
 * notas — nenhuma linha deixa de ser lida. É a mesma escolha do site original.
 *
 * A marcação do pager é a mesma do `vc_paginacao()`: os nomes de classe não
 * mudam, só quem faz a conta. O número atual é um `<span>` com `aria-current`,
 * como o WordPress escreve a página corrente — não é alvo clicável porque já é
 * onde se está. As setas das pontas continuam desenhadas, desligadas, para o
 * pager não mudar de largura a cada troca.
 */
( function () {
	'use strict';

	var lista = document.querySelector( '[data-lista] .post-list' );
	var pager = document.querySelector( '[data-paginacao]' );

	/* Não é a página da lista: não há nada para ligar. */
	if ( ! lista || ! pager ) {
		return;
	}

	var itens = Array.prototype.slice.call( lista.querySelectorAll( '.post-item' ) );

	/* Quantas notas por página. Um número só, aqui, para não haver dois lugares
	   de onde ele possa divergir. */
	var POR_PAGINA = 10;

	var pagina = 1;

	/* Um número do pager. O atual é um `<span>` com `aria-current`, como o
	   WordPress escreve a página corrente — não é um alvo clicável porque já é
	   onde se está. */
	function botaoPagina( rotulo, destino ) {
		var li = document.createElement( 'li' );

		if ( destino === pagina ) {
			var atual = document.createElement( 'span' );
			atual.className = 'paginacao__botao';
			atual.setAttribute( 'aria-current', 'page' );
			atual.textContent = rotulo;
			li.appendChild( atual );
			return li;
		}

		var botao = document.createElement( 'button' );
		botao.type = 'button';
		botao.className = 'paginacao__botao';
		botao.textContent = rotulo;
		botao.addEventListener( 'click', function () {
			pagina = destino;
			pintar();
			lista.scrollIntoView( { block: 'start', behavior: 'smooth' } );
		} );
		li.appendChild( botao );

		return li;
	}

	/* As setas das pontas continuam desenhadas, desligadas: sem elas o pager
	   mudaria de largura a cada troca de página. */
	function ponta( rotulo, destino, inerte ) {
		var li = document.createElement( 'li' );

		if ( inerte ) {
			var parado = document.createElement( 'span' );
			parado.className = 'paginacao__botao paginacao__botao--off';
			parado.setAttribute( 'aria-hidden', 'true' );
			parado.textContent = rotulo;
			li.appendChild( parado );
			return li;
		}

		var botao = document.createElement( 'button' );
		botao.type = 'button';
		botao.className = 'paginacao__botao';
		botao.textContent = rotulo;
		botao.setAttribute( 'aria-label', '←' === rotulo ? 'página anterior' : 'próxima página' );
		botao.addEventListener( 'click', function () {
			pagina = destino;
			pintar();
			lista.scrollIntoView( { block: 'start', behavior: 'smooth' } );
		} );
		li.appendChild( botao );

		return li;
	}

	function desenharPager( totalPaginas ) {
		while ( pager.firstChild ) {
			pager.removeChild( pager.firstChild );
		}

		if ( totalPaginas <= 1 ) {
			pager.hidden = true;
			return;
		}

		var ul = document.createElement( 'ul' );
		ul.className = 'paginacao__lista';

		ul.appendChild( ponta( '←', pagina - 1, 1 === pagina ) );

		for ( var n = 1; n <= totalPaginas; n++ ) {
			ul.appendChild( botaoPagina( String( n ), n ) );
		}

		ul.appendChild( ponta( '→', pagina + 1, pagina === totalPaginas ) );

		pager.appendChild( ul );
		pager.hidden = false;
	}

	function pintar() {
		var totalPaginas = Math.max( 1, Math.ceil( itens.length / POR_PAGINA ) );

		pagina = Math.min( Math.max( 1, pagina ), totalPaginas );

		/* Esconde tudo e revela só a fatia da página atual. */
		itens.forEach( function ( item ) {
			item.hidden = true;
		} );

		itens.slice( ( pagina - 1 ) * POR_PAGINA, pagina * POR_PAGINA ).forEach( function ( item ) {
			item.hidden = false;
		} );

		desenharPager( totalPaginas );
	}

	pintar();
}() );
