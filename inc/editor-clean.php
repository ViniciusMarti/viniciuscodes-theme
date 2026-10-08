<?php
/**
 * Estilização e limpeza do editor Gutenberg para refletir a leitura real do blog.
 *
 * @package ViniciusCodes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enfileira folhas de estilo administrativas para Gutenberg.
 */
function vc_enqueue_clean_editor_assets(): void {
	wp_enqueue_style(
		'vc-admin-clean-editor',
		get_theme_file_uri( 'assets/admin-clean-editor.css' ),
		array(),
		(string) filemtime( get_theme_file_path( 'assets/admin-clean-editor.css' ) )
	);

	wp_enqueue_style(
		'vc-admin-nota-editor',
		get_theme_file_uri( 'assets/admin-nota-editor.css' ),
		array( 'vc-admin-clean-editor' ),
		(string) filemtime( get_theme_file_path( 'assets/admin-nota-editor.css' ) )
	);
}
add_action( 'enqueue_block_editor_assets', 'vc_enqueue_clean_editor_assets' );

/**
 * Injeta no admin_head para garantir que seletores de nível superior e iframes apliquem o estilo.
 */
function vc_admin_editor_head_styles(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'post' !== $screen->base ) {
		return;
	}

	$is_nota = ( 'nota' === $screen->post_type );
	?>
	<style id="vc-editor-direct-overrides">
		/* Limpa bloco lateral e ruídos */
		.edit-post-visual-editor,
		.editor-styles-wrapper,
		.interface-interface-skeleton__content {
			background-color: #faf9f6 !important;
		}

		/* Caixa de 620px centralizada */
		.edit-post-visual-editor .is-root-container,
		.editor-styles-wrapper .block-editor-block-list__layout {
			max-width: 620px !important;
			width: 100% !important;
			margin-left: auto !important;
			margin-right: auto !important;
		}

		<?php if ( $is_nota ) : ?>
		/* Cartão de Nota idêntico a /notas/ */
		.post-type-nota .edit-post-visual-editor .is-root-container,
		.post-type-nota .editor-styles-wrapper .block-editor-block-list__layout {
			background-color: #ffffff !important;
			border: 1px solid #e8e6df !important;
			border-radius: 10px !important;
			padding: 1.2rem 1.35rem 1.5rem 1.35rem !important;
			box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03) !important;
			margin-top: 2.5rem !important;
			margin-bottom: 2.5rem !important;
			position: relative !important;
		}

		.post-type-nota .edit-post-visual-editor .is-root-container::before,
		.post-type-nota .editor-styles-wrapper .block-editor-block-list__layout::before {
			content: 'viniciuscodes · agora' !important;
			display: flex !important;
			align-items: center !important;
			font-size: 0.9rem !important;
			font-weight: 700 !important;
			color: #1a1a1a !important;
			margin-bottom: 1rem !important;
			padding-bottom: 0.75rem !important;
			border-bottom: 1px solid #f1efe9 !important;
			background-image: url('/wp-content/uploads/2026/09/vinicius-profile-150x150.webp') !important;
			background-size: 34px 34px !important;
			background-repeat: no-repeat !important;
			background-position: left center !important;
			padding-left: 44px !important;
			min-height: 34px !important;
		}

		.post-type-nota .editor-post-title,
		.post-type-nota .wp-block-post-title,
		.post-type-nota .editor-visual-editor__post-title-wrapper {
			display: none !important;
		}
		<?php endif; ?>
	</style>
	<?php
}
add_action( 'admin_head', 'vc_admin_editor_head_styles', 100 );
