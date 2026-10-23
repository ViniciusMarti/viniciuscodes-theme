<?php
/**
 * As notas relacionadas. Reproduz o `RelatedNotes.astro`.
 *
 * Vem por último no pé do texto, depois de compartilhar e do convite à conversa.
 * A ordem é intencional: os dois primeiros são sobre ESTE texto — mandar para
 * alguém, responder — e este é sobre o próximo. Juntar as coisas deixaria o
 * leitor decidindo entre falar do que acabou de ler e ler outra coisa antes de
 * ter feito a primeira.
 *
 * A escolha dos três sai de `vc_relacionadas()`, em `functions.php`, e não
 * daqui: a regra mora junto das outras consultas, e este arquivo só sabe
 * desenhar o resultado. É a mesma separação que o site original faz.
 *
 * Quando não há nenhum texto no mesmo tema, o bloco inteiro não é impresso — uma
 * seção vazia com título seria pior do que nenhuma seção.
 *
 * @package ViniciusCodes
 */

$vc_relacionadas = vc_relacionadas( (int) get_the_ID() );

if ( empty( $vc_relacionadas ) ) {
	return;
}
?>
<nav class="related" aria-label="posts relacionados">
	<h2 class="section-title">continue lendo</h2>

	<ul class="related__list">
		<?php foreach ( $vc_relacionadas as $vc_item ) : ?>
			<?php $vc_post = $vc_item['post']; ?>
			<li>
				<a class="related__link" href="<?php echo esc_url( get_permalink( $vc_post ) ); ?>">
					<span class="related__title"><?php echo esc_html( get_the_title( $vc_post ) ); ?></span>
					<span class="related__meta">
						<?php
						/*
						 * O MOTIVO VEM ANTES DA DATA. Aqui a pergunta do leitor é "por
						 * que esta nota está sendo sugerida?", e quem responde é o tema
						 * em comum — a data não responde nada disso. Ela continua na
						 * linha, mas depois, para não abrir o metadado.
						 */
						?>
						<span class="related__motivo">no mesmo tema: <?php echo esc_html( $vc_item['tema'] ); ?></span>
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $vc_post ) ); ?>"><?php echo esc_html( vc_data_curta( $vc_post->ID ) ); ?></time>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
