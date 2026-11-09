<?php
/**
 * Componente de Curtida com Coração.
 * Usado em artigos e notas.
 *
 * @package ViniciusCodes
 */

$vc_id       = (int) ( $args['id'] ?? get_the_ID() );
$vc_curtidas = (int) get_post_meta( $vc_id, '_vc_curtidas', true );
$vc_compacto = ! empty( $args['compacto'] );
?>
<div class="upvote-wrap<?php echo $vc_compacto ? ' upvote-wrap--compact' : ''; ?>">
	<button
		type="button"
		class="upvote-button"
		data-upvote-id="<?php echo (int) $vc_id; ?>"
		aria-label="<?php echo esc_attr( sprintf( __( 'Curtir esta publicação (%d)', 'viniciuscodes' ), $vc_curtidas ) ); ?>"
		title="<?php esc_attr_e( 'Curtir esta publicação', 'viniciuscodes' ); ?>"
	>
		<svg class="upvote-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
			<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
		</svg>
		<span class="upvote-label">curtir</span>
		<span class="upvote-count" aria-hidden="true"><?php echo (int) $vc_curtidas; ?></span>
	</button>
</div>
