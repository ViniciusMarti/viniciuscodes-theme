<?php
/**
 * Quando o endereço não existe.
 *
 * Este arquivo não existia, e o WordPress tem um comportamento próprio para
 * isso: sem `404.php`, ele cai no `index.php`. O `index.php` deste tema é a
 * home — com o retrato, o "oi, tudo bem?" e a chamada "escrevo sobre tecnologia,
 * viagens e AuDHD". Quem errasse um endereço veria a home inteira, sem entender
 * que tinha errado, e o erro de digitação passaria por acerto.
 *
 * Aqui o visitante recebe três coisas, nesta ordem: o que aconteceu, o endereço
 * que ele pediu, e por onde continuar. O caminho principal é `/posts/`, a lista
 * de todos os textos — é a saída mais próxima do que quem chegou aqui procurava.
 *
 * O TÍTULO NÃO TEM TARJA. Havia um `<p class="article__meta">erro 404</p>` em
 * cima do `<h1>`, e ele saiu. "Erro 404" é o nome do erro no código, não o nome
 * do que aconteceu: quem chega perdido não precisa decifrar um número, e o
 * `<h1>` logo abaixo já diz a mesma coisa em português claro ("não encontrei
 * essa página"). Manter os dois era pedir que a pessoa traduzisse o primeiro
 * para entender o segundo.
 *
 * "MUDOU DE CASA" TAMBÉM SAIU. A frase era "ou existiu e mudou de lugar quando
 * o site mudou de casa": duas vezes "mudou" e uma metáfora para explicar uma
 * troca de endereço, numa página onde a pessoa já chegou sem entender o que
 * aconteceu. Agora diz o fato: o site foi refeito.
 *
 * NÃO TEM CAMPO DE BUSCA, e é uma decisão. O site original não tem busca, e o
 * formulário do WordPress sai com as classes dele (`.search-form`), que o CSS do
 * site não desenha: apareceria um campo cru em cima de uma página desenhada. Um
 * campo feio e sem estilo não ajuda mais do que uma lista completa. (A busca por
 * endereço existe e tem página própria — `search.php` —, mas ela não é oferecida
 * aqui: dois caminhos fáceis bastam, e o segundo já é a lista inteira.)
 *
 * O endereço pedido é cortado em 120 caracteres antes de aparecer: `REQUEST_URI`
 * é o que o visitante mandou, e ele pode ser longo.
 *
 * @package ViniciusCodes
 */

get_header();

$vc_endereco = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
?>

<article data-page="404">
	<header class="article__header">
		<h1 class="article__title">não encontrei essa página</h1>
	</header>

	<div class="prose">
		<?php if ( '' !== $vc_endereco ) : ?>
			<p>o endereço <code><?php echo esc_html( wp_html_excerpt( $vc_endereco, 120, '…' ) ); ?></code> não existe aqui — ou existiu e mudou de lugar quando o site foi refeito.</p>
		<?php else : ?>
			<p>esse endereço não existe aqui — ou existiu e mudou de lugar quando o site foi refeito.</p>
		<?php endif; ?>

		<p>o que costuma ajudar:</p>
		<ul>
			<li>a <a href="<?php echo esc_url( home_url( '/posts/' ) ); ?>">lista de todos os textos</a>, do mais recente ao mais antigo;</li>
			<li>o <a href="<?php echo esc_url( home_url( '/' ) ); ?>">início</a>, com os posts recentes e os temas na barra lateral.</li>
		</ul>

		<p>se você chegou aqui por um link meu, me avisa: <a href="mailto:contato@viniciuscodes.com.br">contato@viniciuscodes.com.br</a>.</p>
	</div>
</article>

<?php
get_footer();
