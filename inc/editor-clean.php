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
	wp_enqueue_style(
		'vc-admin-clean-editor',
		get_theme_file_uri( 'assets/admin-clean-editor.css' ),
		array(),
		filemtime( get_theme_file_path( 'assets/admin-clean-editor.css' ) )
	);
}
add_action( 'enqueue_block_editor_assets', 'vc_enqueue_clean_editor_assets' );
