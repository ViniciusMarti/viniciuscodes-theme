<?php
/**
 * Gerenciamento automático de conexões de ida e volta entre notas (Links Citados e Backlinks).
 *
 * @package ViniciusCodes
 */

/**
 * Extrai todos os links internos do conteúdo de um post apontando para outros posts do site.
 *
 * @param int $post_id ID do post a analisar.
 * @return array<int, array{id: int, title: string, url: string}> Lista sem repetições de notas citadas.
 */
function vc_get_outbound_internal_links( int $post_id ): array {
	$content = get_post_field( 'post_content', $post_id );
	if ( empty( $content ) || ! is_string( $content ) ) {
		return array();
	}

	$pattern = '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>/i';
	if ( ! preg_match_all( $pattern, $content, $matches ) ) {
		return array();
	}

	$home_url   = trailingslashit( home_url() );
	$site_host  = (string) wp_parse_url( $home_url, PHP_URL_HOST );
	$encontrados = array();

	foreach ( $matches[1] as $href ) {
		$href = trim( (string) $href );
		if ( '' === $href || '#' === $href[0] || 0 === strpos( $href, 'mailto:' ) || 0 === strpos( $href, 'tel:' ) ) {
			continue;
		}

		// Garante que é interno
		$href_host = (string) wp_parse_url( $href, PHP_URL_HOST );
		if ( ! empty( $href_host ) && strtolower( $href_host ) !== strtolower( $site_host ) ) {
			continue;
		}

		// Converte relativo para absoluto
		$url_completa = $href;
		if ( empty( $href_host ) ) {
			$url_completa = home_url( '/' . ltrim( $href, '/' ) );
		}

		// Ignora links que apontam para o próprio post
		$destino_id = url_to_postid( $url_completa );
		if ( ! $destino_id || (int) $destino_id === (int) $post_id ) {
			continue;
		}

		// Confirma se é post ou página publicado
		$post_type = get_post_type( $destino_id );
		if ( 'post' !== $post_type ) {
			continue;
		}

		if ( 'publish' !== get_post_status( $destino_id ) ) {
			continue;
		}

		if ( ! isset( $encontrados[ $destino_id ] ) ) {
			$encontrados[ $destino_id ] = array(
				'id'    => (int) $destino_id,
				'title' => (string) get_the_title( $destino_id ),
				'url'   => (string) get_permalink( $destino_id ),
			);
		}
	}

	return array_values( $encontrados );
}

/**
 * Obtém os backlinks gravados para um post específico.
 *
 * @param int $post_id ID do post atual.
 * @return array<int, array{id: int, title: string, url: string}>
 */
function vc_get_backlinks( int $post_id ): array {
	$meta = get_post_meta( $post_id, '_garden_backlinks', true );
	if ( ! is_array( $meta ) ) {
		return array();
	}

	// Valida se os posts de origem ainda estão publicados
	$validos = array();
	foreach ( $meta as $bl ) {
		if ( ! empty( $bl['id'] ) && 'publish' === get_post_status( (int) $bl['id'] ) ) {
			$validos[] = array(
				'id'    => (int) $bl['id'],
				'title' => (string) get_the_title( (int) $bl['id'] ),
				'url'   => (string) get_permalink( (int) $bl['id'] ),
			);
		}
	}

	return $validos;
}

/**
 * Atualiza o índice de backlinks sempre que um post é salvo ou alterado.
 *
 * @param int     $post_id ID do post salvo.
 * @param WP_Post $post    Objeto do post.
 * @param bool    $update  Se é atualização.
 */
function vc_sync_post_backlinks( int $post_id, $post, bool $update ): void {
	// Ignora revisões, autosaves e outros post types
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! $post || 'post' !== $post->post_type ) {
		return;
	}

	// Lista de destinos anteriormente citados por este post
	$destinos_antigos = get_post_meta( $post_id, '_vc_outbound_post_ids', true );
	if ( ! is_array( $destinos_antigos ) ) {
		$destinos_antigos = array();
	}

	// Se o post não está publicado (foi para lixo ou rascunho), remove ele de todos os backlinks de destino
	if ( 'publish' !== $post->post_status ) {
		foreach ( $destinos_antigos as $dest_id ) {
			vc_remover_backlink_de_destino( (int) $dest_id, $post_id );
		}
		delete_post_meta( $post_id, '_vc_outbound_post_ids' );
		return;
	}

	// Extrai novos destinos citados no conteúdo atual
	$links_citados = vc_get_outbound_internal_links( $post_id );
	$novos_destinos = array();

	foreach ( $links_citados as $link ) {
		$novos_destinos[] = (int) $link['id'];
	}
	$novos_destinos = array_unique( $novos_destinos );

	// Destinos que deixaram de ser citados: remover backlink
	$removidos = array_diff( $destinos_antigos, $novos_destinos );
	foreach ( $removidos as $dest_id ) {
		vc_remover_backlink_de_destino( (int) $dest_id, $post_id );
	}

	// Destinos citados (novos ou mantidos): adicionar/atualizar backlink
	foreach ( $novos_destinos as $dest_id ) {
		vc_adicionar_backlink_em_destino( (int) $dest_id, $post_id );
	}

	// Salva o registro dos destinos para futura comparação na próxima edição
	update_post_meta( $post_id, '_vc_outbound_post_ids', $novos_destinos );
}
add_action( 'save_post_post', 'vc_sync_post_backlinks', 10, 3 );

/**
 * Remove o post de origem da lista de backlinks do destino.
 */
function vc_remover_backlink_de_destino( int $destino_id, int $origem_id ): void {
	$backlinks = get_post_meta( $destino_id, '_garden_backlinks', true );
	if ( ! is_array( $backlinks ) ) {
		return;
	}

	$alterado = false;
	foreach ( $backlinks as $k => $item ) {
		if ( isset( $item['id'] ) && (int) $item['id'] === (int) $origem_id ) {
			unset( $backlinks[ $k ] );
			$alterado = true;
		}
	}

	if ( $alterado ) {
		update_post_meta( $destino_id, '_garden_backlinks', array_values( $backlinks ) );
	}
}

/**
 * Adiciona ou atualiza o post de origem na lista de backlinks do destino.
 */
function vc_adicionar_backlink_em_destino( int $destino_id, int $origem_id ): void {
	$backlinks = get_post_meta( $destino_id, '_garden_backlinks', true );
	if ( ! is_array( $backlinks ) ) {
		$backlinks = array();
	}

	$novo_item = array(
		'id'    => (int) $origem_id,
		'title' => (string) get_the_title( $origem_id ),
		'url'   => (string) get_permalink( $origem_id ),
	);

	$encontrado = false;
	foreach ( $backlinks as $k => $item ) {
		if ( isset( $item['id'] ) && (int) $item['id'] === (int) $origem_id ) {
			$backlinks[ $k ] = $novo_item;
			$encontrado     = true;
			break;
		}
	}

	if ( ! $encontrado ) {
		$backlinks[] = $novo_item;
	}

	update_post_meta( $destino_id, '_garden_backlinks', array_values( $backlinks ) );
}
