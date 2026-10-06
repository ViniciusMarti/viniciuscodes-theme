<?php
/**
 * O cartão que fecha uma resenha: capa, título, autor, nota e o link.
 *
 * Reproduz o `BookReview.astro` — chamado com
 * `get_template_part( 'template-parts/card', 'livro' )`, que é a forma nativa de
 * apontar para este arquivo.
 *
 * No site original ele é desenhado pelo layout, e não escrito no corpo do texto,
 * por um motivo que vale repetir: o corpo é Markdown, e Markdown não expressa
 * este bloco. Um `<div>` cru no meio do texto seria descartado pelo parser — foi
 * exatamente assim que o cartão do WordPress antigo se perdeu na migração para
 * Markdown. Aqui o dado vive no campo `_vinicius_livro` do texto.
 *
 * Acessibilidade, já que o cartão junta quatro coisas em pouco espaço:
 * as estrelas são `aria-hidden`, porque "★★★☆☆" é ruído para quem usa leitor de
 * tela — a nota vai escrita em texto ao lado; a capa e o título são dois links
 * separados para o mesmo lugar, cada um com nome acessível próprio; e os dois
 * levam `rel="sponsored"`, que é a marcação correta para link de afiliado.
 *
 * `target="_blank"` NÃO está aqui, de propósito. Forçar outra aba tira do leitor
 * a decisão de onde abrir o link, e nesta página há uma capa e um botão para o
 * mesmo destino — três abas para quem clicasse nos três. Quem quiser outra aba
 * usa Ctrl/Cmd, ou o menu do botão direito.
 *
 * @package ViniciusCodes
 */

$vc_livro = vc_livro( (int) get_the_ID() );

if ( ! $vc_livro ) {
	return;
}

$vc_link = (string) ( $vc_livro['link'] ?? '' );
$vc_nota = isset( $vc_livro['nota'] ) ? (int) round( (float) $vc_livro['nota'] ) : null;
$vc_nota = ( null === $vc_nota ) ? null : max( 0, min( 5, $vc_nota ) );

$vc_estrelas = ( null === $vc_nota )
	? null
	: str_repeat( '★', $vc_nota ) . str_repeat( '☆', 5 - $vc_nota );

/*
 * A capa veio do site antigo como `/wp-media/algum-livro.jpg`. Ela está na
 * biblioteca de mídia agora, e o nome do arquivo é o mesmo: procurar por ele
 * resolve o endereço novo sem guardar caminho nenhum no banco.
 */
$vc_capa = (string) ( $vc_livro['capa'] ?? '' );
$vc_capa = vc_midia( basename( $vc_capa ) ) ?: $vc_capa;

/* O tamanho real das capas no acervo, para o cartão não pular ao carregar. */
$vc_largura = 160;
$vc_altura  = 240;

$vc_atributos = sprintf(
	'href="%s" rel="sponsored noopener noreferrer"',
	esc_url( $vc_link )
);
?>
<aside class="book-review" aria-labelledby="book-review-title">
	<p class="book-review__kicker">livro resenhado</p>

	<div class="book-review__body">
		<a class="book-review__cover" <?php echo $vc_atributos; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- montado acima com esc_url. ?>>
			<img
				src="<?php echo esc_url( $vc_capa ); ?>"
				alt="Capa de <?php echo esc_attr( (string) $vc_livro['titulo'] ); ?>"
				width="<?php echo (int) $vc_largura; ?>"
				height="<?php echo (int) $vc_altura; ?>"
				loading="lazy"
				decoding="async"
			/>
		</a>

		<div class="book-review__text">
			<h2 class="book-review__title" id="book-review-title">
				<a <?php echo $vc_atributos; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( (string) $vc_livro['titulo'] ); ?></a>
			</h2>

			<p class="book-review__author"><?php echo esc_html( (string) ( $vc_livro['autor'] ?? '' ) ); ?></p>

			<?php if ( null !== $vc_estrelas ) : ?>
				<p class="book-review__rating">
					<span class="book-review__stars" aria-hidden="true"><?php echo esc_html( $vc_estrelas ); ?></span>
					<span class="book-review__rating-text"><?php echo (int) $vc_nota; ?> de 5 estrelas</span>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $vc_livro['formato'] ) ) : ?>
				<p class="book-review__format"><?php echo esc_html( (string) $vc_livro['formato'] ); ?></p>
			<?php endif; ?>

			<p class="book-review__action">
				<a class="book-review__button" <?php echo $vc_atributos; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					ver na Amazon<span class="book-review__sr"> (link externo)</span>
					<span aria-hidden="true"> ↗</span>
				</a>
			</p>

			<p class="book-review__disclaimer">
				link de afiliado: ao comprar por ele eu recebo uma comissão da Amazon, sem custo a mais para
				você.
			</p>
		</div>
	</div>
</aside>
