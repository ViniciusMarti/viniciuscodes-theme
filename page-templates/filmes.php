<?php
/**
 * Template Name: Filmes
 *
 * A página `/filmes/`: o que assisti no cinema e em casa, organizado por ano.
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
		case 'filme':
		case 'filmes':
			return 'filme';
	}

	return $t;
};

/**
 * A chave que junta duas linhas na MESMA obra.
 */
$vc_chave = static function ( string $t ): string {
	$t = preg_replace( '/^assistir\s+/iu', '', trim( $t ) );
	$t = preg_replace( '/^[\s"\x{201C}\x{201D}]+/u', '', (string) $t );
	$t = preg_replace( '/[\s"\x{201C}\x{201D}]+$/u', '', (string) $t );
	$t = preg_replace( '/\s*\(\s*\d*\s*[\x{00AA}\x{00BA}aA]?\s*[Dd]ata\s*\d*\s*\)\s*/u', ' ', (string) $t );
	$t = preg_replace( '/[\x{2190}-\x{21FF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}\x{200D}]/u', '', (string) $t );
	$t = preg_replace( '/[\x{1F000}-\x{1FAFF}]/u', '', (string) $t );
	$t = remove_accents( mb_strtolower( (string) $t, 'UTF-8' ) );
	$t = preg_replace( '/[^\p{L}\p{N} ]+/u', ' ', $t );
	$t = preg_replace( '/\s+/u', ' ', (string) $t );

	return trim( (string) $t );
};

/**
 * Lê as linhas do conteúdo e devolve os filmes, já agrupados e desduplicados por data.
 */
$vc_ler_filmes = static function ( string $conteudo ) use ( $vc_normalizar_tipo, $vc_chave ) : array {
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

		$vc_datas = array();

		if ( $vc_partes && preg_match( '#^\s*\d{1,2}/\d{1,2}/\d{4}#', $vc_partes[0] ) ) {
			foreach ( explode( ',', (string) array_shift( $vc_partes ) ) as $vc_pedaco ) {
				if ( preg_match( '#^\s*(\d{1,2})/(\d{1,2})/(\d{4})\s*$#', $vc_pedaco, $vc_d ) ) {
					$vc_datas[] = sprintf( '%04d-%02d-%02d', (int) $vc_d[3], (int) $vc_d[2], (int) $vc_d[1] );
				}
			}
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
				'tipo'   => $vc_tipo ?: 'filme',
				'datas'  => array(),
				'campos' => $vc_campos,
			);
		}

		foreach ( $vc_datas as $vc_dia ) {
			$obras[ $vc_k ]['datas'][ $vc_dia ] = true;
		}

		foreach ( $vc_campos as $vc_nome => $vc_valor ) {
			if ( ! isset( $obras[ $vc_k ]['campos'][ $vc_nome ] ) ) {
				$obras[ $vc_k ]['campos'][ $vc_nome ] = $vc_valor;
			}
		}
	}

	$lista = array();

	foreach ( $obras as $vc_o ) {
		$vc_o['datas'] = array_keys( $vc_o['datas'] );
		sort( $vc_o['datas'] );
		$lista[] = $vc_o;
	}

	return $lista;
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

	return 0;
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

get_header();

while ( have_posts() ) :
	the_post();

	$vc_conteudo = (string) get_the_content();
	$vc_filmes   = $vc_ler_filmes( $vc_conteudo );
	$vc_abre     = $vc_abertura( $vc_conteudo );

	$vc_por_ano = array();

	foreach ( $vc_filmes as $vc_o ) {
		if ( $vc_o['datas'] ) {
			$vc_por_ano[ substr( max( $vc_o['datas'] ), 0, 4 ) ][] = $vc_o;
		} else {
			$vc_por_ano['vontade'][] = $vc_o;
		}
	}

	krsort( $vc_por_ano );
	?>
	<article class="article article--acervo article--filmes" data-page="filmes">
		<header class="article__header">
			<h1 class="article__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( '' !== $vc_abre ) : ?>
			<div class="prose acervo__abertura"><?php echo wp_kses_post( $vc_abre ); ?></div>
		<?php endif; ?>

		<?php
		foreach ( $vc_por_ano as $vc_ano => $vc_do_ano ) :
			usort(
				$vc_do_ano,
				static function ( $a, $b ) {
					$da = $a['datas'] ? max( $a['datas'] ) : '';
					$db = $b['datas'] ? max( $b['datas'] ) : '';

					return strcmp( $db, $da ) ?: strcmp( $a['titulo'], $b['titulo'] );
				}
			);
			?>
			<section class="acervo__secao" data-ano="<?php echo esc_attr( $vc_ano ); ?>">
				<h2 class="acervo__rotulo">
					<?php if ( 'vontade' === $vc_ano ) : ?>
						<?php esc_html_e( 'quero ver', 'viniciuscodes' ); ?>
					<?php else : ?>
						<?php echo esc_html( $vc_ano ); ?>
					<?php endif; ?>
					<span class="acervo__contagem"><?php echo (int) count( $vc_do_ano ); ?></span>
				</h2>

				<ul class="acervo__grade">
					<?php
					foreach ( $vc_do_ano as $vc_o ) :
						$vc_id_capa = $vc_achar_capa( $vc_o['titulo'], $vc_o['campos']['capa'] ?? '' );
						$vc_resenha = $vc_achar_resenha( $vc_o['titulo'], $vc_o['campos']['resenha'] ?? '' );
						$vc_vezes   = count( $vc_o['datas'] );

						$vc_extenso = mb_strlen( $vc_o['titulo'], 'UTF-8' );
						$vc_tamanho = $vc_extenso > 34
							? ' acervo__placa--miudo'
							: ( $vc_extenso > 18 ? ' acervo__placa--medio' : '' );

						$vc_anos    = array_unique( array_map( static function ( $d ) {
							return substr( $d, 0, 4 );
						}, $vc_o['datas'] ) );
						$vc_com_ano = count( $vc_anos ) > 1;

						$vc_dias = array_map(
							static function ( $d ) use ( $vc_com_ano ) {
								return substr( $d, 8, 2 ) . '/' . substr( $d, 5, 2 ) . ( $vc_com_ano ? '/' . substr( $d, 0, 4 ) : '' );
							},
							$vc_o['datas']
						);

						$vc_tem_meta = $vc_vezes > 1 || $vc_dias;
						?>
						<li class="acervo__carta"<?php echo $vc_vezes > 1 ? ' data-revisto="' . (int) $vc_vezes . '"' : ''; ?>>
							<div class="acervo__capa">
								<?php if ( $vc_id_capa ) : ?>
									<?php
									echo wp_get_attachment_image(
										$vc_id_capa,
										'medium',
										false,
										array(
											'class'   => 'acervo__capa-img',
											'loading' => 'lazy',
											'alt'     => '',
										)
									);
									?>
								<?php else : ?>
									<span class="acervo__placa<?php echo esc_attr( $vc_tamanho ); ?>">
										<span class="acervo__inicial" aria-hidden="true"><?php echo esc_html( mb_substr( $vc_o['titulo'], 0, 1, 'UTF-8' ) ); ?></span>
										<h3 class="acervo__placa-titulo"><?php echo esc_html( $vc_o['titulo'] ); ?></h3>
										<span class="acervo__placa-tipo" aria-hidden="true"><?php echo esc_html( $vc_o['tipo'] ); ?></span>
									</span>
								<?php endif; ?>
							</div>

							<?php if ( $vc_id_capa ) : ?>
								<h3 class="acervo__carta-titulo"><?php echo esc_html( $vc_o['titulo'] ); ?></h3>
							<?php endif; ?>

							<?php if ( $vc_tem_meta ) : ?>
								<p class="acervo__meta">
									<?php if ( $vc_vezes > 1 ) : ?>
										<span class="acervo__vezes">
											<span class="screen-reader-text">
												<?php
												/* translators: %d: quantas vezes. */
												printf( esc_html__( 'assisti %d vezes', 'viniciuscodes' ), $vc_vezes );
												?>
											</span>
											<span aria-hidden="true"><?php echo (int) $vc_vezes; ?>×</span>
										</span>
									<?php endif; ?>

									<span class="acervo__datas"><?php echo esc_html( implode( ' · ', $vc_dias ) ); ?></span>
								</p>
							<?php endif; ?>

							<?php if ( '' !== $vc_resenha ) : ?>
								<a class="acervo__resenha" href="<?php echo esc_url( $vc_resenha ); ?>">
									<?php esc_html_e( 'resenha', 'viniciuscodes' ); ?> <span aria-hidden="true">→</span>
								</a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
			<?php
		endforeach;
		?>

		<?php if ( ! $vc_filmes ) : ?>
			<p class="empty"><?php esc_html_e( 'ainda não há filmes por aqui.', 'viniciuscodes' ); ?></p>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
