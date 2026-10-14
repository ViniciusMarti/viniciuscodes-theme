<?php
/**
 * Template Name: Livros
 *
 * A página `/livros/`: o que estou lendo, o que já li e a minha estante.
 *
 * @package ViniciusCodes
 */

wp_enqueue_style(
	'vc-acervo',
	get_theme_file_uri( 'assets/acervo.css' ),
	array( 'vc-global' ),
	(string) filemtime( get_theme_file_path( 'assets/acervo.css' ) )
);

$vc_normalizar_tipo = static function ( string $t ): string {
	$t = trim( remove_accents( mb_strtolower( $t, 'UTF-8' ) ) );

	switch ( $t ) {
		case 'livro':
		case 'livros':
			return 'livro';
	}

	return $t;
};

/**
 * A chave que junta duas linhas na MESMA obra.
 */
$vc_chave = static function ( string $t ): string {
	$t = preg_replace( '/^[\s"\x{201C}\x{201D}]+/u', '', (string) $t );
	$t = preg_replace( '/[\s"\x{201C}\x{201D}]+$/u', '', (string) $t );
	$t = preg_replace( '/[\x{2190}-\x{21FF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}\x{200D}]/u', '', (string) $t );
	$t = preg_replace( '/[\x{1F000}-\x{1FAFF}]/u', '', (string) $t );
	$t = remove_accents( mb_strtolower( (string) $t, 'UTF-8' ) );
	$t = preg_replace( '/[^\p{L}\p{N} ]+/u', ' ', $t );
	$t = preg_replace( '/\s+/u', ' ', (string) $t );

	return trim( (string) $t );
};

/**
 * Lê as linhas do conteúdo e devolve os livros, já agrupados.
 */
$vc_ler_livros = static function ( string $conteudo ) use ( $vc_normalizar_tipo, $vc_chave ) : array {
	if ( ! preg_match_all( '#<li\b[^>]*>(.*?)</li>#is', $conteudo, $vc_achados ) ) {
		return array();
	}

	$obras = array();

	foreach ( $vc_achados[1] as $vc_bruto ) {
		$vc_texto = html_entity_decode( wp_strip_all_tags( $vc_bruto ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$vc_texto = trim( preg_replace( '/\s+/u', ' ', $vc_texto ) );

		if ( '' === $vc_texto ) {
			continue;
		}

		$vc_partes = array_map( 'trim', explode( '|', $vc_texto ) );
		$vc_titulo = (string) array_shift( $vc_partes );

		if ( '' === $vc_titulo ) {
			continue;
		}

		$vc_tipo = '';
		if ( $vc_partes && false === strpos( $vc_partes[0], ':' ) ) {
			$vc_tipo = $vc_normalizar_tipo( (string) array_shift( $vc_partes ) );
		}

		$vc_campos = array();

		foreach ( $vc_partes as $vc_parte ) {
			if ( false === strpos( $vc_parte, ':' ) ) {
				continue;
			}

			list( $vc_nome, $vc_valor ) = explode( ':', $vc_parte, 2 );

			$vc_nome  = trim( mb_strtolower( $vc_nome, 'UTF-8' ) );
			$vc_valor = trim( $vc_valor );

			if ( '' !== $vc_nome && '' !== $vc_valor && ! isset( $vc_campos[ $vc_nome ] ) ) {
				$vc_campos[ $vc_nome ] = $vc_valor;
			}
		}

		$vc_k = $vc_chave( $vc_titulo );

		if ( ! isset( $obras[ $vc_k ] ) ) {
			$obras[ $vc_k ] = array(
				'titulo' => $vc_titulo,
				'tipo'   => $vc_tipo ?: 'livro',
				'campos' => $vc_campos,
			);
		}

		foreach ( $vc_campos as $vc_nome => $vc_valor ) {
			if ( ! isset( $obras[ $vc_k ]['campos'][ $vc_nome ] ) ) {
				$obras[ $vc_k ]['campos'][ $vc_nome ] = $vc_valor;
			}
		}
	}

	return array_values( $obras );
};

$vc_mapa_midia = static function (): array {
	static $vc_mapa = null;

	if ( null !== $vc_mapa ) {
		return $vc_mapa;
	}

	global $wpdb;

	$vc_mapa = array();

	$vc_linhas = $wpdb->get_results(
		"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file'"
	);

	foreach ( (array) $vc_linhas as $vc_l ) {
		$vc_base = strtolower( basename( (string) $vc_l->meta_value ) );

		$vc_mapa[ $vc_base ] = (int) $vc_l->post_id;

		$vc_sem_extensao = strtolower( (string) pathinfo( $vc_base, PATHINFO_FILENAME ) );

		if ( ! isset( $vc_mapa[ $vc_sem_extensao ] ) ) {
			$vc_mapa[ $vc_sem_extensao ] = (int) $vc_l->post_id;
		}
	}

	return $vc_mapa;
};

$vc_mapa_posts = static function (): array {
	static $vc_mapa = null;

	if ( null !== $vc_mapa ) {
		return $vc_mapa;
	}

	$vc_mapa = array();

	$vc_ids = get_posts(
		array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	foreach ( $vc_ids as $vc_id ) {
		$vc_mapa[ get_post_field( 'post_name', $vc_id ) ] = (int) $vc_id;
	}

	return $vc_mapa;
};

$vc_achar_capa = static function ( string $vc_titulo, string $vc_campo ) use ( $vc_mapa_midia ) : int {
	$vc_mapa = $vc_mapa_midia();

	if ( '' !== $vc_campo ) {
		if ( ctype_digit( $vc_campo ) ) {
			return (int) $vc_campo;
		}

		$vc_nome = strtolower( basename( $vc_campo ) );
		$vc_sem  = strtolower( (string) pathinfo( $vc_nome, PATHINFO_FILENAME ) );

		foreach ( array( $vc_nome, $vc_sem ) as $vc_tentativa ) {
			if ( isset( $vc_mapa[ $vc_tentativa ] ) ) {
				return $vc_mapa[ $vc_tentativa ];
			}
		}

		return 0;
	}

	$vc_slug = strtolower( sanitize_title( $vc_titulo ) );

	return $vc_mapa[ $vc_slug ] ?? 0;
};

$vc_achar_resenha = static function ( string $vc_titulo, string $vc_campo ) use ( $vc_mapa_posts ) : string {
	if ( '' !== $vc_campo ) {
		return $vc_campo;
	}

	$vc_mapa = $vc_mapa_posts();
	$vc_slug = sanitize_title( $vc_titulo );

	foreach ( array( $vc_slug, $vc_slug . '-resenha' ) as $vc_tentativa ) {
		if ( isset( $vc_mapa[ $vc_tentativa ] ) ) {
			return (string) get_permalink( $vc_mapa[ $vc_tentativa ] );
		}
	}

	return '';
};

$vc_abertura = static function ( string $conteudo ): string {
	$vc_sem_listas = preg_replace( '#<ul\b[^>]*>.*?</ul>#is', '', $conteudo );
	$vc_sem_listas = preg_replace( '#<!--\s*/?wp:[^>]*-->#i', '', (string) $vc_sem_listas );

	return trim( (string) $vc_sem_listas );
};

$vc_estrelas = static function ( int $vc_n ): string {
	$vc_n = max( 0, min( 5, $vc_n ) );

	return str_repeat( "\xE2\x98\x85", $vc_n ) . str_repeat( "\xE2\x98\x86", 5 - $vc_n );
};

$vc_link_livro = static function ( string $vc_resenha, string $vc_amazon ): array {
	if ( '' !== $vc_resenha ) {
		return array( $vc_resenha, '' );
	}

	if ( '' !== $vc_amazon ) {
		return array( $vc_amazon, ' rel="sponsored nofollow noopener" target="_blank"' );
	}

	return array( '', '' );
};

get_header();

while ( have_posts() ) :
	the_post();

	$vc_conteudo = (string) get_the_content();
	$vc_livros   = $vc_ler_livros( $vc_conteudo );
	$vc_abre     = $vc_abertura( $vc_conteudo );

	$vc_por_status = array(
		'lendo'      => array(),
		'lido'       => array(),
		'abandonado' => array(),
	);

	foreach ( $vc_livros as $vc_o ) {
		$vc_st = $vc_o['campos']['status'] ?? 'lido';
		if ( 'lendo' === $vc_st ) {
			$vc_por_status['lendo'][] = $vc_o;
		} elseif ( 'abandonado' === $vc_st ) {
			$vc_por_status['abandonado'][] = $vc_o;
		} else {
			$vc_por_status['lido'][] = $vc_o;
		}
	}

	$vc_rotulos_status = array(
		'lendo'      => 'lendo no momento',
		'lido'       => 'lidos',
		'abandonado' => 'abandonados',
	);
	?>
	<article class="article article--acervo article--livros" data-page="livros">
		<header class="article__header">
			<h1 class="article__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( '' !== $vc_abre ) : ?>
			<div class="prose acervo__abertura"><?php echo wp_kses_post( $vc_abre ); ?></div>
		<?php endif; ?>

		<?php
		foreach ( $vc_rotulos_status as $vc_chave_status => $vc_rotulo_status ) :
			if ( empty( $vc_por_status[ $vc_chave_status ] ) ) {
				continue;
			}
			?>
			<section class="acervo__secao" data-status="<?php echo esc_attr( $vc_chave_status ); ?>">
				<h2 class="section-title">
					<?php echo esc_html( $vc_rotulo_status ); ?>
					<span class="acervo__contagem"><?php echo (int) count( $vc_por_status[ $vc_chave_status ] ); ?></span>
				</h2>

				<ul class="acervo__livros">
					<?php
					foreach ( $vc_por_status[ $vc_chave_status ] as $vc_o ) :
						$vc_id_capa  = $vc_achar_capa( $vc_o['titulo'], $vc_o['campos']['capa'] ?? '' );
						$vc_resenha  = $vc_achar_resenha( $vc_o['titulo'], $vc_o['campos']['resenha'] ?? '' );
						$vc_amazon   = (string) ( $vc_o['campos']['amazon'] ?? '' );
						$vc_nota     = (int) ( $vc_o['campos']['nota'] ?? 0 );
						$vc_autor    = (string) ( $vc_o['campos']['autor'] ?? '' );
						$vc_resumo   = (string) ( $vc_o['campos']['resumo'] ?? '' );
						list( $vc_href, $vc_rel ) = $vc_link_livro( $vc_resenha, $vc_amazon );
						?>
						<li class="acervo__livro">
							<?php if ( $vc_id_capa ) : ?>
								<figure class="acervo__livro-capa">
									<?php if ( '' !== $vc_href ) : ?>
										<a href="<?php echo esc_url( $vc_href ); ?>"<?php echo $vc_rel; // phpcs:ignore WordPress.Security.EscapeOutput -- atributos fixos. ?>>
									<?php endif; ?>
									<?php
									echo wp_get_attachment_image(
										$vc_id_capa,
										'medium',
										false,
										array(
											'class'   => 'acervo__livro-img',
											'loading' => 'lazy',
											'alt'     => '',
										)
									);
									?>
									<?php if ( '' !== $vc_href ) : ?>
										</a>
									<?php endif; ?>
								</figure>
							<?php endif; ?>

							<div class="acervo__livro-texto">
								<h3 class="acervo__livro-titulo">
									<?php if ( '' !== $vc_href ) : ?>
										<a href="<?php echo esc_url( $vc_href ); ?>"<?php echo $vc_rel; // phpcs:ignore WordPress.Security.EscapeOutput -- atributos fixos. ?>>
									<?php endif; ?>
									<?php echo esc_html( $vc_o['titulo'] ); ?>
									<?php if ( '' !== $vc_href ) : ?>
										</a>
									<?php endif; ?>
								</h3>

								<?php if ( '' !== $vc_autor ) : ?>
									<p class="acervo__livro-autor"><?php echo esc_html( $vc_autor ); ?></p>
								<?php endif; ?>

								<?php if ( $vc_nota > 0 ) : ?>
									<p class="acervo__livro-nota">
										<span class="screen-reader-text">
											<?php
											/* translators: %d: nota de 1 a 5. */
											printf( esc_html__( 'nota %d de 5', 'viniciuscodes' ), $vc_nota );
											?>
										</span>
										<span aria-hidden="true"><?php echo esc_html( $vc_estrelas( $vc_nota ) ); ?></span>
									</p>
								<?php endif; ?>

								<?php if ( '' !== $vc_resumo ) : ?>
									<p class="acervo__livro-resumo"><?php echo esc_html( $vc_resumo ); ?></p>
								<?php endif; ?>

								<?php
								$vc_pagina  = (int) ( $vc_o['campos']['pagina'] ?? ( $vc_o['campos']['página'] ?? 0 ) );
								$vc_paginas = (int) ( $vc_o['campos']['paginas'] ?? ( $vc_o['campos']['páginas'] ?? ( $vc_o['campos']['total'] ?? 0 ) ) );
								if ( $vc_pagina > 0 && $vc_paginas > 0 ) :
									$vc_pct = min( 100, max( 0, (int) round( ( $vc_pagina / $vc_paginas ) * 100 ) ) );
									?>
									<div class="acervo__livro-progresso" role="progressbar" aria-valuenow="<?php echo esc_attr( (string) $vc_pct ); ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php echo esc_attr( sprintf( __( 'progresso de leitura: %d%%', 'viniciuscodes' ), $vc_pct ) ); ?>">
										<div class="acervo__livro-barra">
											<div class="acervo__livro-barra-preenchimento" style="width: <?php echo esc_attr( (string) $vc_pct ); ?>%;"></div>
										</div>
										<span class="acervo__livro-progresso-rotulo">
											<?php printf( esc_html__( 'página %1$d de %2$d (%3$d%%)', 'viniciuscodes' ), $vc_pagina, $vc_paginas, $vc_pct ); ?>
										</span>
									</div>
								<?php endif; ?>

								<?php if ( '' !== $vc_amazon || '' !== $vc_resenha ) : ?>
									<p class="acervo__livro-acoes">
										<?php if ( '' !== $vc_amazon ) : ?>
											<a class="acervo__botao" href="<?php echo esc_url( $vc_amazon ); ?>" rel="sponsored nofollow noopener" target="_blank">
												<?php esc_html_e( 'ver na Amazon', 'viniciuscodes' ); ?> <span aria-hidden="true">↗</span>
											</a>
										<?php endif; ?>

										<?php if ( '' !== $vc_resenha ) : ?>
											<a class="acervo__botao" href="<?php echo esc_url( $vc_resenha ); ?>">
												<?php esc_html_e( 'ler a resenha', 'viniciuscodes' ); ?> <span aria-hidden="true">→</span>
											</a>
										<?php endif; ?>
									</p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
			<?php
		endforeach;
		?>

		<?php if ( ! $vc_livros ) : ?>
			<p class="empty"><?php esc_html_e( 'ainda não há livros por aqui.', 'viniciuscodes' ); ?></p>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
