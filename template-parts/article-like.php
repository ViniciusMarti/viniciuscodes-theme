<?php
/**
 * Gostar. Um botão só, usado pelos textos e pelas notas.
 *
 * POR QUE ELE SAIU DO `article-share.php`.
 *
 * Era lá que este botão morava, junto com o WhatsApp e o copiar link. O
 * `single-nota.php` recusa aquele bloco inteiro, e o motivo está escrito nele:
 * o compartilhamento publica o TÍTULO do post no texto que vai ser enviado
 * (`data-title`), e o título de uma nota é um carimbo de data — mandar
 * "Nota — 4 de outubro de 2026, 14:30" para alguém não é compartilhar, é vazar
 * o mecanismo. O `gostar` não tem esse defeito: ele não usa título, não usa
 * endereço, não usa nada além do ID. Era a única parte do bloco que servia às
 * notas, então ela saiu para cá e os dois passaram a usar a mesma.
 *
 * O DESENHO NÃO MUDOU: `post-share__btn` é a pílula (a borda, o respiro, os
 * 44px de alvo) e `post-like__btn` é o coração e o estado de "gostei". As duas
 * vêm de `global.css` e continuam sendo o único lugar onde isso é escrito.
 *
 * AS DUAS FORMAS, E POR QUE EXISTEM.
 *
 * `compacto` é a forma da LINHA DO TEMPO: o coração e a contagem, sem pílula e
 * sem rótulo. `.notas.css` desenha essa diferença. A razão é a mesma que o
 * `global.css` dá para o respiro do bloco de compartilhamento — dez pílulas de
 * 44px embaixo de dez notas viram uma barra de controles, e uma nota não é uma
 * página de artigo: na lista o texto é o que manda.
 *
 * Sem `compacto` é a forma do texto e da PÁGINA DA NOTA: a pílula inteira, com
 * o rótulo, igual à que já existe nos textos.
 *
 * O RÓTULO ACESSÍVEL VEM DE FORA, e não é preciosismo. Na lista, o botão não
 * tem rótulo visível, então o `aria-label` é a única coisa que diz o que ele
 * faz — e três botões com o mesmo "gostar desta nota" na mesma página deixam
 * quem navega por botões sem saber qual nota está curtindo. Quem chama passa o
 * rótulo com o número da nota; sem argumento, o padrão é o dos textos, palavra
 * por palavra, para o HTML dos posts não mudar.
 *
 * @package ViniciusCodes
 */

$vc_args     = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$vc_compacto = ! empty( $vc_args['compacto'] );
$vc_rotulo   = isset( $vc_args['rotulo'] )
	? (string) $vc_args['rotulo']
	: __( 'gostar deste texto', 'viniciuscodes' );

$vc_id       = (int) get_the_ID();
$vc_curtidas = (int) get_post_meta( $vc_id, '_vc_curtidas', true );
?>
<button
	type="button"
	class="post-share__btn post-like__btn<?php echo $vc_compacto ? ' post-like__btn--compacto' : ''; ?>"
	data-post-like="<?php echo (int) $vc_id; ?>"
	aria-label="<?php echo esc_attr( $vc_rotulo ); ?>"
>
	<?php echo vc_icone( 'heart', 16 ); ?>
	<?php if ( ! $vc_compacto ) : ?>
		<span class="post-like__label"><?php esc_html_e( 'gostar', 'viniciuscodes' ); ?></span>
	<?php endif; ?>
	<?php if ( $vc_curtidas > 0 ) : ?>
		<span class="post-like__count">(<?php echo $vc_curtidas; ?>)</span>
	<?php endif; ?>
</button>
