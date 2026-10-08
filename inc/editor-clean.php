<?php
/**
 * Estilização e limpeza do editor Gutenberg para refletir a leitura real do blog.
 *
 * @package ViniciusCodes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enfileira folhas de estilo administrativas adicionais para limpar o Gutenberg.
 */
function vc_enqueue_clean_editor_assets(): void {
	$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	wp_enqueue_style(
		'vc-admin-clean-editor',
		get_theme_file_uri( 'assets/admin-clean-editor.css' ),
		array(),
		filemtime( get_theme_file_path( 'assets/admin-clean-editor.css' ) )
	);

	// Se for tela de edição de nota, aplica o estilo de cartão de timeline
	if ( $current_screen && 'nota' === $current_screen->post_type ) {
		wp_enqueue_style(
			'vc-admin-nota-editor',
			get_theme_file_uri( 'assets/admin-nota-editor.css' ),
			array( 'vc-admin-clean-editor' ),
			filemtime( get_theme_file_path( 'assets/admin-nota-editor.css' ) )
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'vc_enqueue_clean_editor_assets' );
