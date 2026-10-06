<?php
/**
 * O cartão que fecha uma avaliação de lugar: onde fica, o nome, a nota e o mapa.
 *
 * IRMÃO DO `card-livro.php`, E DE PROPÓSITO COM AS MESMAS CLASSES
 * ---------------------------------------------------------------
 * `book-review__*` é o nome do módulo de avaliação do site, e o cartão do livro
 * é quem o estreou. Repetir as classes aqui é o ponto: quem lê o CSS encontra um
 * módulo só, e uma mudança de desenho vale para as duas avaliações. O que muda
 * entre elas é o conteúdo — o livro tem capa, autor, formato e o aviso de
 * afiliado; o lugar tem onde fica e um mapa, e não tem nada a declarar.
 *
 * SEM CAPA, e por isso o `--sem-capa` no corpo: a foto do lugar já está no
 * texto, logo acima, com a legenda dela. Repetir a mesma foto dentro do cartão
 * seria dizer duas vezes a mesma coisa, e o desenho do livro (capa de 160px à
 * esquerda, texto à direita) não tem o que fazer na primeira coluna sem ela.
 *
 * DESENHADO PELO LAYOUT, NÃO ESCRITO NO CORPO DO TEXTO — pela mesma razão do
 * cartão do livro: o dado vive no campo `_vinicius_local` do texto, e não em
 * parágrafos com marcação à mão, que quebram na primeira edição pelo painel.
 *
 * A nota é escrita em texto ao lado das estrelas (`5 de 5 estrelas`) e as
 * estrelas são `aria-hidden`: "★★★★★" é ruído para quem usa leitor de tela.
 *
 * @package ViniciusCodes
 */

$vc_local = vc_local( (int) get_the_ID() );

if ( ! $vc_local ) {
	return;
}

$vc_link = (string) ( $vc_local['link'] ?? '' );
$vc_nota = isset( $vc_local['nota'] ) ? (int) round( (float) $vc_local['nota'] ) : null;
$vc_nota = ( null === $vc_nota ) ? null : max( 0, min( 5, $vc_nota ) );

$vc_estrelas = ( null === $vc_nota )
	? null
	: str_repeat( '★', $vc_nota ) . str_repeat( '☆', 5 - $vc_nota );

$vc_atributos = sprintf( 'href="%s" rel="noopener noreferrer"', esc_url( $vc_link ) );
?>
<aside class="book-review" aria-labelledby="avaliacao-titulo">
	<p class="book-review__kicker"><?php echo esc_html( (string) ( $vc_local['onde'] ?? 'avaliação do local' ) ); ?></p>

	<div class="book-review__body book-review__body--sem-capa">
		<div class="book-review__text">
			<h2 class="book-review__title" id="avaliacao-titulo">
				<a <?php echo $vc_atributos; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- montado acima com esc_url. ?>><?php echo esc_html( (string) $vc_local['nome'] ); ?></a>
			</h2>

			<?php if ( ! empty( $vc_local['endereco'] ) ) : ?>
				<p class="book-review__author"><?php echo esc_html( (string) $vc_local['endereco'] ); ?></p>
			<?php endif; ?>

			<?php if ( null !== $vc_estrelas ) : ?>
				<p class="book-review__rating">
					<span class="book-review__stars" aria-hidden="true"><?php echo esc_html( $vc_estrelas ); ?></span>
					<span class="book-review__rating-text"><?php echo (int) $vc_nota; ?> de 5 estrelas</span>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $vc_local['linha'] ) ) : ?>
				<p class="book-review__format"><?php echo esc_html( (string) $vc_local['linha'] ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $vc_link ) : ?>
				<p class="book-review__action">
					<a class="book-review__button" <?php echo $vc_atributos; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php echo esc_html( (string) ( $vc_local['botao'] ?? 'ver o endereço no Google Maps' ) ); ?><span class="book-review__sr"> (link externo)</span>
						<span aria-hidden="true"> ↗</span>
					</a>
				</p>
			<?php endif; ?>
		</div>
	</div>
</aside>
