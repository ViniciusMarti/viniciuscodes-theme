<?php
/**
 * Gerador dinamico de cartoes Open Graph (1200x630) para posts e paginas sem imagem destacada.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retorna a URL da imagem OG dinamica para um post ou pagina.
 */
function vc_obter_og_imagem_dinamica( int $post_id ): string {
	if ( ! $post_id ) {
		return vc_midia( 'og-default.jpg' );
	}

	$cache_dir = WP_CONTENT_DIR . '/uploads/og-cache';
	$cache_url = set_url_scheme( content_url( 'uploads/og-cache' ), 'https' );

	if ( ! is_dir( $cache_dir ) ) {
		wp_mkdir_p( $cache_dir );
	}

	$post_modified = get_post_modified_time( 'U', true, $post_id );
	$filename      = 'og-' . $post_id . '-' . $post_modified . '.png';
	$filepath      = $cache_dir . '/' . $filename;

	if ( file_exists( $filepath ) && filesize( $filepath ) > 0 ) {
		return $cache_url . '/' . $filename;
	}

	$gerou = vc_gerar_og_imagem_arquivo( $post_id, $filepath );
	if ( $gerou && file_exists( $filepath ) ) {
		return $cache_url . '/' . $filename;
	}

	return vc_midia( 'og-default.jpg' );
}

/**
 * Cria a imagem PNG usando GD e salva no caminho de destino.
 */
function vc_gerar_og_imagem_arquivo( int $post_id, string $dest_path ): bool {
	if ( ! extension_loaded( 'gd' ) ) {
		return false;
	}

	$largura = 1200;
	$altura  = 630;

	$im = imagecreatetruecolor( $largura, $altura );
	if ( ! $im ) {
		return false;
	}

	imagealphablending( $im, true );
	imagesavealpha( $im, true );

	$bg_color       = imagecolorallocate( $im, 22, 24, 26 );      // #16181a
	$card_bg        = imagecolorallocate( $im, 28, 31, 35 );      // #1c1f23
	$border_color   = imagecolorallocate( $im, 45, 49, 56 );      // #2d3138
	$accent_green   = imagecolorallocate( $im, 34, 197, 94 );     // #22c55e
	$text_white     = imagecolorallocate( $im, 245, 245, 247 );   // #f5f5f7
	$text_gray      = imagecolorallocate( $im, 156, 163, 175 );   // #9ca3af

	imagefilledrectangle( $im, 0, 0, $largura, $altura, $bg_color );

	$padding = 48;
	imagefilledrectangle( $im, $padding, $padding, $largura - $padding, $altura - $padding, $card_bg );
	imagerectangle( $im, $padding, $padding, $largura - $padding, $altura - $padding, $border_color );

	// Faixa decorativa superior verde
	imagefilledrectangle( $im, $padding, $padding, $largura - $padding, $padding + 6, $accent_green );

	$font_bold    = get_theme_file_path( 'assets/fonts-og/OpenSans-Bold.ttf' );
	$font_regular = get_theme_file_path( 'assets/fonts-og/OpenSans-Regular.ttf' );

	// Categoria
	$categorias = get_the_category( $post_id );
	$categoria_nome = ! empty( $categorias ) ? mb_strtolower( $categorias[0]->name, 'UTF-8' ) : 'artigo';
	if ( defined( 'VC_NOTA' ) && VC_NOTA === get_post_type( $post_id ) ) {
		$categoria_nome = 'nota rápida';
	}

	$badge_text = '• ' . $categoria_nome;
	imagettftext( $im, 18, 0, $padding + 48, $padding + 70, $accent_green, $font_bold, $badge_text );

	// Título
	$titulo = get_the_title( $post_id );
	if ( defined( 'VC_NOTA' ) && VC_NOTA === get_post_type( $post_id ) ) {
		$conteudo_cru = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
		$titulo       = wp_html_excerpt( trim( $conteudo_cru ), 140, '…' );
	}
	$titulo = html_entity_decode( $titulo, ENT_QUOTES, 'UTF-8' );

	$linhas = vc_quebrar_linhas_og( $titulo, 36, 4 );
	$pos_y  = $padding + 165;
	$tamanho_fonte_titulo = ( count( $linhas ) > 3 ) ? 36 : 42;
	$espacamento_linha    = ( count( $linhas ) > 3 ) ? 56 : 64;

	foreach ( $linhas as $linha ) {
		imagettftext( $im, $tamanho_fonte_titulo, 0, $padding + 48, $pos_y, $text_white, $font_bold, $linha );
		$pos_y += $espacamento_linha;
	}

	// Avatar do autor
	$avatar_path = ABSPATH . 'wp-content/uploads/2026/09/cropped-Foto-de-Perfil-1.png';
	$avatar_x    = $padding + 48;
	$avatar_y    = $altura - $padding - 105;
	$avatar_tam  = 68;

	if ( file_exists( $avatar_path ) ) {
		$avatar_src = imagecreatefrompng( $avatar_path );
		if ( $avatar_src ) {
			$avatar_redim = imagecreatetruecolor( $avatar_tam, $avatar_tam );
			imagealphablending( $avatar_redim, false );
			imagesavealpha( $avatar_redim, true );
			imagecopyresampled( $avatar_redim, $avatar_src, 0, 0, 0, 0, $avatar_tam, $avatar_tam, imagesx( $avatar_src ), imagesy( $avatar_src ) );
			imagecopy( $im, $avatar_redim, $avatar_x, $avatar_y, 0, 0, $avatar_tam, $avatar_tam );
			imagerectangle( $im, $avatar_x, $avatar_y, $avatar_x + $avatar_tam, $avatar_y + $avatar_tam, $accent_green );
			imagedestroy( $avatar_redim );
			imagedestroy( $avatar_src );
		}
	}

	imagettftext( $im, 20, 0, $avatar_x + $avatar_tam + 20, $avatar_y + 30, $text_white, $font_bold, 'Vinicius Oliveira' );
	imagettftext( $im, 16, 0, $avatar_x + $avatar_tam + 20, $avatar_y + 58, $text_gray, $font_regular, 'viniciuscodes.com.br · @viniciuscodes' );

	$ok = imagepng( $im, $dest_path, 8 );
	imagedestroy( $im );

	return $ok;
}

/**
 * Divide o título em linhas sem quebrar palavras.
 */
function vc_quebrar_linhas_og( string $texto, int $max_chars = 38, int $max_linhas = 4 ): array {
	$palavras = preg_split( '/\s+/u', trim( $texto ) );
	$linhas   = array();
	$linha_atual = '';

	foreach ( $palavras as $palavra ) {
		$candidato = ( '' === $linha_atual ) ? $palavra : $linha_atual . ' ' . $palavra;
		if ( mb_strlen( $candidato, 'UTF-8' ) <= $max_chars ) {
			$linha_atual = $candidato;
		} else {
			if ( '' !== $linha_atual ) {
				$linhas[] = $linha_atual;
			}
			$linha_atual = $palavra;

			if ( count( $linhas ) >= $max_linhas - 1 ) {
				break;
			}
		}
	}

	if ( '' !== $linha_atual && count( $linhas ) < $max_linhas ) {
		$linhas[] = $linha_atual;
	}

	return $linhas;
}
