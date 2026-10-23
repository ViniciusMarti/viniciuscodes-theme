<?php
/**
 * Vídeos: o post type para os vídeos do canal @viniciuscodes.
 *
 * Registra o post type `video` com arquivo em `/videos/` e cada vídeo em
 * `/video/<slug>/`, no mesmo estilo e convenção do site phsantos.com.
 *
 * @package ViniciusCodes
 */

defined( 'ABSPATH' ) || exit;

/**
 * O slug do post type.
 */
const VC_VIDEO = 'video';

/**
 * Registra o post type `video`.
 */
function vc_videos_registrar() {
	register_post_type(
		VC_VIDEO,
		array(
			'labels'                => array(
				'name'               => __( 'Vídeos', 'viniciuscodes' ),
				'singular_name'      => __( 'Vídeo', 'viniciuscodes' ),
				'menu_name'          => __( 'Vídeos', 'viniciuscodes' ),
				'add_new'            => __( 'Novo vídeo', 'viniciuscodes' ),
				'add_new_item'       => __( 'Novo vídeo', 'viniciuscodes' ),
				'edit_item'          => __( 'Editar vídeo', 'viniciuscodes' ),
				'new_item'           => __( 'Novo vídeo', 'viniciuscodes' ),
				'view_item'          => __( 'Ver vídeo', 'viniciuscodes' ),
				'view_items'         => __( 'Ver vídeos', 'viniciuscodes' ),
				'search_items'       => __( 'Buscar vídeos', 'viniciuscodes' ),
				'not_found'          => __( 'Nenhum vídeo encontrado.', 'viniciuscodes' ),
				'not_found_in_trash' => __( 'Nenhum vídeo na lixeira.', 'viniciuscodes' ),
				'all_items'          => __( 'Todos os vídeos', 'viniciuscodes' ),
				'archives'           => __( 'Arquivo de vídeos', 'viniciuscodes' ),
				'item_published'     => __( 'Vídeo publicado.', 'viniciuscodes' ),
				'item_updated'       => __( 'Vídeo atualizado.', 'viniciuscodes' ),
				'items_list'         => __( 'Lista de vídeos', 'viniciuscodes' ),
			),
			'description'           => __( 'Vídeos do canal @viniciuscodes no YouTube.', 'viniciuscodes' ),
			'public'                => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_nav_menus'     => true,
			'show_in_admin_bar'     => true,
			'show_in_rest'          => true,
			'rest_base'             => 'videos',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'menu_position'         => 7,
			'menu_icon'             => 'dashicons-video-alt3',
			'hierarchical'          => false,
			'has_archive'           => 'videos',
			'rewrite'               => array(
				'slug'       => 'video',
				'with_front' => false,
			),
			'query_var'             => true,
			'exclude_from_search'   => false,
			'can_export'            => true,
			'delete_with_user'      => false,
			'capability_type'       => 'post',
			'map_meta_cap'          => true,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions' ),
			'taxonomies'            => array( 'category', 'post_tag' ),
		)
	);
}
add_action( 'init', 'vc_videos_registrar', 9 );

/**
 * Registra meta fields para o vídeo (YouTube ID e URL da thumbnail).
 */
function vc_videos_registrar_meta() {
	register_post_meta(
		VC_VIDEO,
		'_vc_youtube_id',
		array(
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		VC_VIDEO,
		'_vc_youtube_thumb',
		array(
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'vc_videos_registrar_meta' );

/**
 * Retorna o ID do YouTube de um post de vídeo.
 *
 * @param int|null $post_id ID do post.
 * @return string ID do vídeo do YouTube (ex: 'wGHHNYcC4Vw') ou vazio.
 */
function vc_video_youtube_id( ?int $post_id = null ): string {
	$id = $post_id ?: get_the_ID();
	if ( ! $id ) {
		return '';
	}

	$yt_id = (string) get_post_meta( $id, '_vc_youtube_id', true );
	if ( '' !== $yt_id ) {
		return trim( $yt_id );
	}

	$alt_id = (string) get_post_meta( $id, '_vc_video_id', true );
	if ( '' !== $alt_id ) {
		return trim( $alt_id );
	}

	// Tenta extrair do conteúdo se não houver meta
	$conteudo = get_post_field( 'post_content', $id );
	if ( preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $conteudo, $m ) ) {
		return $m[1];
	}

	return '';
}

/**
 * Retorna a thumbnail do vídeo (YouTube ou featured image).
 *
 * @param int|null $post_id ID do post.
 * @param string   $size    Tamanho da imagem do WP se houver.
 * @return string URL da thumbnail.
 */
function vc_video_thumbnail_url( ?int $post_id = null, string $size = 'large' ): string {
	$id = $post_id ?: get_the_ID();
	if ( ! $id ) {
		return '';
	}

	if ( has_post_thumbnail( $id ) ) {
		$thumb = get_the_post_thumbnail_url( $id, $size );
		if ( $thumb ) {
			return $thumb;
		}
	}

	$custom_thumb = (string) get_post_meta( $id, '_vc_youtube_thumb', true );
	if ( '' !== $custom_thumb ) {
		return $custom_thumb;
	}

	$alt_thumb = (string) get_post_meta( $id, '_vc_video_thumb', true );
	if ( '' !== $alt_thumb ) {
		return $alt_thumb;
	}

	$yt_id = vc_video_youtube_id( $id );
	if ( '' !== $yt_id ) {
		return 'https://i.ytimg.com/vi/' . $yt_id . '/hqdefault.jpg';
	}

	return '';
}

/**
 * Normaliza os títulos de vídeos para a estética em minúsculo do site.
 *
 * Transforma o título em caixa baixa preservando acentuação UTF-8, mantendo
 * siglas e termos chave da identidade do autor (como TDAH, AuDHD, LGBT, YouTube, etc.)
 *
 * @param string   $title   Título original.
 * @param int|null $post_id ID do post.
 * @return string Título normalizado em minúsculo.
 */
function vc_video_titulo_minusculo( string $title, ?int $post_id = null ): string {
	if ( ! $post_id || VC_VIDEO !== get_post_type( $post_id ) ) {
		return $title;
	}

	// Não altera títulos dentro do painel administrativo
	if ( is_admin() ) {
		return $title;
	}

	$lower = mb_strtolower( $title, 'UTF-8' );

	// Dicionário de preservação de termos e siglas comuns
	$preservar = array(
		'/\btdah\b/ui'        => 'TDAH',
		'/\baudhd\b/ui'       => 'AuDHD',
		'/\blgbt\b/ui'        => 'LGBT',
		'/\byoutube\b/ui'     => 'YouTube',
		'/\bsp\b/ui'          => 'SP',
		'/\bsp\b/ui'          => 'SP',
		'/\bdecathlon\b/ui'   => 'Decathlon',
		'/\bfgts\b/ui'        => 'FGTS',
		'/\bcif\b/ui'         => 'CIF',
		'/\bsptrans\b/ui'     => 'SPTrans',
		'/\br\$\s*/ui'        => 'R$ ',
	);

	return (string) preg_replace( array_keys( $preservar ), array_values( $preservar ), $lower );
}
add_filter( 'the_title', 'vc_video_titulo_minusculo', 10, 2 );
