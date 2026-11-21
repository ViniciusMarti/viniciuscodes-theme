<?php
/**
 * Visualização hierárquica e agrupamento de comentários em conversas (threads) no wp-admin.
 *
 * @package ViniciusCodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Organiza a listagem de comentários em árvore cronológica na tela edit-comments.php.
 * As réplicas e tréplicas aparecem agrupadas logo abaixo do comentário ao qual respondem,
 * respeitando a ordem natural de evolução de cada conversa.
 *
 * @param WP_Comment[]     $comments Array de comentários retornados pela query.
 * @param WP_Comment_Query $query    Instância atual da query de comentários.
 * @return WP_Comment[] Comentários reorganizados em formato de conversa quando aplicável.
 */
function vc_reorganizar_comentarios_admin_threads( array $comments, WP_Comment_Query $query ): array {
	if ( ! is_admin() || empty( $comments ) ) {
		return $comments;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'edit-comments' !== $screen->base ) {
		return $comments;
	}

	// Não interfere se o usuário ordenou manualmente por autor ou outra coluna
	if ( ! empty( $_GET['orderby'] ) && 'comment_date_gmt' !== $_GET['orderby'] && 'comment_date' !== $_GET['orderby'] ) {
		return $comments;
	}

	$por_id = array();
	$filhos = array();
	$raizes = array();

	foreach ( $comments as $c ) {
		$por_id[ (int) $c->comment_ID ] = $c;
	}

	foreach ( $comments as $c ) {
		$parent_id = (int) $c->comment_parent;
		if ( $parent_id > 0 && isset( $por_id[ $parent_id ] ) ) {
			if ( ! isset( $filhos[ $parent_id ] ) ) {
				$filhos[ $parent_id ] = array();
			}
			$filhos[ $parent_id ][] = (int) $c->comment_ID;
		} else {
			$raizes[] = (int) $c->comment_ID;
		}
	}

	// Se não há nenhuma resposta entre os comentários listados, mantém a ordem original intacta
	if ( empty( $filhos ) ) {
		return $comments;
	}

	$ordenados = array();
	$visitar   = static function ( int $cid ) use ( &$visitar, &$ordenados, &$filhos, $por_id ): void {
		$ordenados[] = $por_id[ $cid ];

		if ( ! empty( $filhos[ $cid ] ) ) {
			// Sub-respostas em ordem cronológica (evolução da conversa)
			usort(
				$filhos[ $cid ],
				static function ( int $a, int $b ) use ( $por_id ): int {
					return strcmp( (string) $por_id[ $a ]->comment_date, (string) $por_id[ $b ]->comment_date );
				}
			);

			foreach ( $filhos[ $cid ] as $child_id ) {
				$visitar( $child_id );
			}
		}
	};

	foreach ( $raizes as $root_id ) {
		$visitar( $root_id );
	}

	return $ordenados;
}
add_filter( 'the_comments', 'vc_reorganizar_comentarios_admin_threads', 10, 2 );

/**
 * Adiciona classes de profundidade e réplica na tag <tr> do comentário no admin.
 *
 * @param string[]   $classes    Classes CSS originais.
 * @param string     $class      Classes adicionais fornecidas.
 * @param string     $comment_id ID numérico do comentário.
 * @return string[] Classes atualizadas.
 */
function vc_classes_comentarios_admin_threads( array $classes, $class, $comment_id ): array {
	if ( ! is_admin() ) {
		return $classes;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'edit-comments' !== $screen->base ) {
		return $classes;
	}

	$comment = get_comment( (int) $comment_id );
	if ( ! $comment ) {
		return $classes;
	}

	// Remove as classes depth-1 estáticas do WordPress para podermos injetar a profundidade real
	$classes = array_diff( $classes, array( 'depth-1', 'depth-2', 'depth-3', 'depth-4', 'depth-5' ) );

	$profundidade = 1;
	$atual        = $comment;
	while ( $atual && (int) $atual->comment_parent > 0 && $profundidade < 10 ) {
		$profundidade++;
		$atual = get_comment( (int) $atual->comment_parent );
	}

	$classes[] = 'vc-depth-' . min( $profundidade, 5 );

	if ( $profundidade > 1 ) {
		$classes[] = 'vc-is-reply';
		if ( 2 === $profundidade ) {
			$classes[] = 'vc-reply-level-1';
		} elseif ( $profundidade >= 3 ) {
			$classes[] = 'vc-reply-level-deep';
		}
	} else {
		$classes[] = 'vc-is-root';
	}

	return $classes;
}
add_filter( 'comment_class', 'vc_classes_comentarios_admin_threads', 10, 3 );

/**
 * Suprime o texto nativo e cru do WordPress ("Em resposta a Autor.") para não duplicar com nosso badge.
 *
 * @param string $translated Texto traduzido.
 * @param string $text       Texto original.
 * @param string $domain     Domínio de tradução.
 * @return string Texto filtrado.
 */
function vc_suprimir_em_resposta_nativo( string $translated, string $text, string $domain ): string {
	if ( 'default' === $domain && 'In reply to %s.' === $text && is_admin() ) {
		$screen = get_current_screen();
		if ( $screen && 'edit-comments' === $screen->base ) {
			return '';
		}
	}
	return $translated;
}
add_filter( 'gettext', 'vc_suprimir_em_resposta_nativo', 10, 3 );

/**
 * Insere um contexto limpo, compacto e elegante do comentário-pai quando o comentário for réplica.
 *
 * @param string          $comment_text Texto original do comentário.
 * @param WP_Comment|null $comment      Objeto do comentário.
 * @return string Texto com linha de contexto limpa e elegante.
 */
function vc_adicionar_contexto_replica_admin( string $comment_text, $comment ): string {
	if ( ! is_admin() || ! ( $comment instanceof WP_Comment ) ) {
		return $comment_text;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'edit-comments' !== $screen->base ) {
		return $comment_text;
	}

	$parent_id = (int) $comment->comment_parent;
	if ( $parent_id <= 0 ) {
		return $comment_text;
	}

	$pai = get_comment( $parent_id );
	if ( ! $pai ) {
		return $comment_text;
	}

	$nome_pai     = (string) get_comment_author( $pai );
	$trecho_cru   = wp_strip_all_tags( (string) $pai->comment_content );
	$trecho_limpo = wp_trim_words( $trecho_cru, 14, '&hellip;' );

	$profundidade = 1;
	$atual        = $comment;
	while ( $atual && (int) $atual->comment_parent > 0 && $profundidade < 10 ) {
		$profundidade++;
		$atual = get_comment( (int) $atual->comment_parent );
	}

	$rotulo = ( $profundidade >= 3 ) ? 'Tréplica para' : 'Em resposta a';

	$snippet  = '<div class="vc-reply-context">';
	$snippet .= '<span class="vc-reply-arrow" aria-hidden="true">&#x21B3;</span> ';
	$snippet .= '<span class="vc-reply-label">' . esc_html( $rotulo ) . '</span> ';
	$snippet .= '<strong class="vc-reply-author">@' . esc_html( $nome_pai ) . '</strong>';
	if ( ! empty( $trecho_limpo ) ) {
		$snippet .= '<span class="vc-reply-quote">&ldquo;' . esc_html( $trecho_limpo ) . '&rdquo;</span>';
	}
	$snippet .= '</div>';

	return $snippet . $comment_text;
}
add_filter( 'get_comment_text', 'vc_adicionar_contexto_replica_admin', 20, 2 );

/**
 * Enfileira a folha de estilos exclusiva para os comentários no wp-admin com cache-busting dinâmico.
 *
 * @param string $hook Gancho da página atual do painel.
 */
function vc_enfileirar_estilos_comentarios_admin( string $hook ): void {
	if ( ! in_array( $hook, array( 'edit-comments.php', 'index.php' ), true ) ) {
		return;
	}

	$arquivo_css = 'assets/admin-comments.css';
	$caminho     = get_theme_file_path( $arquivo_css );

	if ( file_exists( $caminho ) ) {
		wp_enqueue_style(
			'vc-admin-comments',
			get_theme_file_uri( $arquivo_css ),
			array(),
			(string) filemtime( $caminho )
		);
	}
}
add_action( 'admin_enqueue_scripts', 'vc_enfileirar_estilos_comentarios_admin' );
