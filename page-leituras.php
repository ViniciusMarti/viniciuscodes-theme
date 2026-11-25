<?php
/**
 * Template Name: Leituras
 *
 * A página `/leituras/`: estante de livros sincronizada com o Notion,
 * exibindo leitura atual, concluídos, abandonados e fila de leitura.
 *
 * @package ViniciusCodes
 */

get_header();

$vc_json_path   = get_theme_file_path( 'data/leituras_notion.json' );
$vc_livros_raw  = file_exists( $vc_json_path ) ? json_decode( (string) file_get_contents( $vc_json_path ), true ) : array();
$vc_livros_raw  = is_array( $vc_livros_raw ) ? $vc_livros_raw : array();

// Mapa de fallback automático para resenhas publicadas no blog
$vc_resenhas_fallback = array(
	'a cabeca do santo' => home_url( '/a-cabeca-do-santo-resenha/' ),
	'como eu me encaixo' => home_url( '/o-que-achei-de-como-eu-me-encaixo-livro-sobre-tdah-e-autismo/' ),
);

$vc_normalizar_chave = static function ( string $texto ): string {
	$texto = mb_strtolower( trim( $texto ), 'UTF-8' );
	$texto = remove_accents( $texto );
	return (string) preg_replace( '/[^a-z0-9\s]/', '', $texto );
};

$vc_formatar_data_curta = static function ( ?string $iso ): string {
	if ( empty( $iso ) || strlen( $iso ) < 10 ) {
		return '';
	}
	$partes = explode( '-', substr( $iso, 0, 10 ) );
	if ( count( $partes ) !== 3 ) {
		return $iso;
	}
	return "{$partes[2]}/{$partes[1]}/{$partes[0]}";
};

$vc_render_estrelas = static function ( int $nota ): string {
	if ( $nota <= 0 ) {
		return '';
	}
	$nota  = min( 5, max( 1, $nota ) );
	$cheia = str_repeat( '★', $nota );
	$vazia = str_repeat( '☆', 5 - $nota );
	return sprintf(
		'<span class="reading-stars" aria-label="Avaliação: %1$d de 5 estrelas" title="%1$d de 5 estrelas"><span class="reading-stars__filled" aria-hidden="true">%2$s</span><span class="reading-stars__empty" aria-hidden="true">%3$s</span><span class="reading-stars__score">%1$d/5</span></span>',
		$nota,
		$cheia,
		$vazia
	);
};

// Separa os livros por status
$vc_lendo        = array();
$vc_concluidos   = array();
$vc_abandonados  = array();
$vc_nao_iniciado = array();

foreach ( $vc_livros_raw as $livro ) {
	if ( ! is_array( $livro ) || empty( $livro['titulo'] ) ) {
		continue;
	}

	$titulo_chave = $vc_normalizar_chave( (string) $livro['titulo'] );
	if ( empty( $livro['link_resenha'] ) ) {
		foreach ( $vc_resenhas_fallback as $trecho => $url_resenha ) {
			if ( false !== strpos( $titulo_chave, $trecho ) ) {
				$livro['link_resenha'] = $url_resenha;
				break;
			}
		}
	}

	$status = mb_strtolower( trim( (string) ( $livro['status'] ?? 'não iniciado' ) ), 'UTF-8' );

	if ( 'lendo' === $status ) {
		$vc_lendo[] = $livro;
	} elseif ( 'concluído' === $status || 'concluido' === $status || 'lido' === $status ) {
		$vc_concluidos[] = $livro;
	} elseif ( 'abandonado' === $status ) {
		$vc_abandonados[] = $livro;
	} else {
		$vc_nao_iniciado[] = $livro;
	}
}

// Ordena concluídos pela data de conclusão decrescente (ou início decrescente)
usort(
	$vc_concluidos,
	static function ( array $a, array $b ): int {
		$data_a = (string) ( $a['conclusao'] ?: $a['inicio'] ?: '0000-00-00' );
		$data_b = (string) ( $b['conclusao'] ?: $b['inicio'] ?: '0000-00-00' );
		if ( $data_a !== $data_b ) {
			return strcmp( $data_b, $data_a );
		}
		return strcmp( (string) $a['titulo'], (string) $b['titulo'] );
	}
);

// Ordena abandonados pela data de início decrescente e maior percentual
usort(
	$vc_abandonados,
	static function ( array $a, array $b ): int {
		$data_a = (string) ( $a['inicio'] ?: '0000-00-00' );
		$data_b = (string) ( $b['inicio'] ?: '0000-00-00' );
		if ( $data_a !== $data_b ) {
			return strcmp( $data_b, $data_a );
		}
		return ( (int) ( $b['percentual'] ?? 0 ) ) <=> ( (int) ( $a['percentual'] ?? 0 ) );
	}
);

// Ordena estante (não iniciados) alfabeticamente por título
usort(
	$vc_nao_iniciado,
	static function ( array $a, array $b ): int {
		return strcasecmp( remove_accents( (string) $a['titulo'] ), remove_accents( (string) $b['titulo'] ) );
	}
);

$vc_total_livros      = count( $vc_lendo ) + count( $vc_concluidos ) + count( $vc_abandonados ) + count( $vc_nao_iniciado );
$vc_total_lendo       = count( $vc_lendo );
$vc_total_concluidos  = count( $vc_concluidos );
$vc_total_abandonados = count( $vc_abandonados );
$vc_total_estante     = count( $vc_nao_iniciado );
?>

<article data-page="leituras" class="article-leituras" data-readings-root>
	<header class="term-header">
		<h1 class="term-header__name">leituras</h1>
		<div class="term-header__desc">
			<p>o que estou lendo agora, os livros que já concluí, os que abandonei pelo caminho e a minha estante completa com fila de leitura. todo este catálogo é sincronizado com a base onde <a href="<?php echo esc_url( home_url( '/como-organizo-minhas-leituras-no-notion/' ) ); ?>">organizo minhas leituras no Notion</a>.</p>
		</div>
	</header>

	<div class="readings-controls" aria-label="Filtros e busca da estante">
		<div class="readings-tabs" role="tablist" aria-label="Filtrar livros por status">
			<button type="button" class="readings-tab is-active" role="tab" aria-selected="true" data-reading-filter="todos">
				<span>todos</span>
				<span class="readings-tab__count"><?php echo (int) $vc_total_livros; ?></span>
			</button>
			<button type="button" class="readings-tab" role="tab" aria-selected="false" data-reading-filter="lendo">
				<span>lendo agora</span>
				<span class="readings-tab__count"><?php echo (int) $vc_total_lendo; ?></span>
			</button>
			<button type="button" class="readings-tab" role="tab" aria-selected="false" data-reading-filter="concluido">
				<span>concluídos</span>
				<span class="readings-tab__count"><?php echo (int) $vc_total_concluidos; ?></span>
			</button>
			<button type="button" class="readings-tab" role="tab" aria-selected="false" data-reading-filter="abandonado">
				<span>abandonados</span>
				<span class="readings-tab__count"><?php echo (int) $vc_total_abandonados; ?></span>
			</button>
			<button type="button" class="readings-tab" role="tab" aria-selected="false" data-reading-filter="estante">
				<span>na estante</span>
				<span class="readings-tab__count"><?php echo (int) $vc_total_estante; ?></span>
			</button>
		</div>

		<div class="readings-search-wrap">
			<span class="readings-search__icon" aria-hidden="true"><?php echo vc_icone( 'search', 15 ); ?></span>
			<input type="search" class="readings-search__input" placeholder="buscar por título ou autor…" aria-label="Buscar livro por título ou autor" data-reading-search>
		</div>
	</div>

	<?php if ( ! empty( $vc_lendo ) ) : ?>
		<section class="readings-section" data-reading-section="lendo" aria-labelledby="heading-lendo">
			<header class="readings-section__header">
				<h2 id="heading-lendo" class="readings-section__title">lendo no momento</h2>
				<span class="readings-section__count"><?php echo (int) $vc_total_lendo; ?> <?php echo 1 === $vc_total_lendo ? 'livro' : 'livros'; ?></span>
			</header>

			<div class="readings-featured-list">
				<?php foreach ( $vc_lendo as $livro ) :
					$titulo     = (string) $livro['titulo'];
					$autor      = (string) ( $livro['autor'] ?? '' );
					$capa       = (string) ( $livro['capa'] ?? '' );
					$percentual = max( 0, min( 100, (int) ( $livro['percentual'] ?? 0 ) ) );
					$inicio_fmt = $vc_formatar_data_curta( $livro['inicio'] ?? null );
					$sinopse    = trim( (string) ( $livro['sinopse'] ?? '' ) );
					$resenha    = (string) ( $livro['link_resenha'] ?? '' );
					$busca_attr = mb_strtolower( $titulo . ' ' . $autor, 'UTF-8' );
				?>
					<article class="reading-card reading-card--featured" data-reading-item data-reading-query="<?php echo esc_attr( $busca_attr ); ?>">
						<div class="reading-card__cover-wrap">
							<?php if ( '' !== $capa ) : ?>
								<img class="reading-card__cover" src="<?php echo esc_url( $capa ); ?>" alt="Capa do livro <?php echo esc_attr( $titulo ); ?>" width="140" height="210" loading="eager" decoding="async">
							<?php else : ?>
								<div class="reading-card__placeholder" aria-hidden="true"><?php echo esc_html( mb_substr( $titulo, 0, 1, 'UTF-8' ) ); ?></div>
							<?php endif; ?>
						</div>

						<div class="reading-card__body">
							<div class="reading-card__badges">
								<span class="reading-badge reading-badge--reading">lendo agora</span>
								<?php if ( '' !== $inicio_fmt ) : ?>
									<span class="reading-card__date">iniciado em <?php echo esc_html( $inicio_fmt ); ?></span>
								<?php endif; ?>
							</div>

							<h3 class="reading-card__title"><?php echo esc_html( $titulo ); ?></h3>
							<?php if ( '' !== $autor ) : ?>
								<p class="reading-card__author"><?php echo esc_html( $autor ); ?></p>
							<?php endif; ?>

							<div class="reading-progress-box">
								<div class="reading-progress-box__meta">
									<span class="reading-progress-box__label">progresso da leitura</span>
									<span class="reading-progress-box__pct"><?php echo (int) $percentual; ?>%</span>
								</div>
								<progress class="reading-progress-bar" value="<?php echo (int) $percentual; ?>" max="100"><?php echo (int) $percentual; ?>%</progress>
							</div>

							<?php if ( '' !== $sinopse ) : ?>
								<p class="reading-card__synopsis"><?php echo esc_html( wp_trim_words( $sinopse, 48, '…' ) ); ?></p>
							<?php endif; ?>

							<?php if ( '' !== $resenha ) : ?>
								<div class="reading-card__actions">
									<a href="<?php echo esc_url( $resenha ); ?>" class="reading-card__review-link">
										<span>ler resenha no blog</span>
										<span aria-hidden="true">→</span>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $vc_concluidos ) ) : ?>
		<section class="readings-section" data-reading-section="concluido" aria-labelledby="heading-concluidos">
			<header class="readings-section__header">
				<h2 id="heading-concluidos" class="readings-section__title">livros concluídos</h2>
				<span class="readings-section__count"><?php echo (int) $vc_total_concluidos; ?> livros</span>
			</header>

			<div class="readings-list">
				<?php foreach ( $vc_concluidos as $livro ) :
					$titulo        = (string) $livro['titulo'];
					$autor         = (string) ( $livro['autor'] ?? '' );
					$capa          = (string) ( $livro['capa'] ?? '' );
					$nota          = (int) ( $livro['avaliacao_estrelas'] ?? 0 );
					$inicio_fmt    = $vc_formatar_data_curta( $livro['inicio'] ?? null );
					$conclusao_fmt = $vc_formatar_data_curta( $livro['conclusao'] ?? null );
					$sinopse       = trim( (string) ( $livro['sinopse'] ?? '' ) );
					$resenha       = (string) ( $livro['link_resenha'] ?? '' );
					$busca_attr    = mb_strtolower( $titulo . ' ' . $autor, 'UTF-8' );

					$periodo_label = '';
					if ( '' !== $inicio_fmt && '' !== $conclusao_fmt ) {
						$periodo_label = ( $inicio_fmt === $conclusao_fmt ) ? "lido em {$conclusao_fmt}" : "{$inicio_fmt} a {$conclusao_fmt}";
					} elseif ( '' !== $conclusao_fmt ) {
						$periodo_label = "concluído em {$conclusao_fmt}";
					} elseif ( '' !== $inicio_fmt ) {
						$periodo_label = "iniciado em {$inicio_fmt}";
					}
				?>
					<article class="reading-card" data-reading-item data-reading-query="<?php echo esc_attr( $busca_attr ); ?>">
						<div class="reading-card__cover-wrap">
							<?php if ( '' !== $capa ) : ?>
								<img class="reading-card__cover" src="<?php echo esc_url( $capa ); ?>" alt="Capa do livro <?php echo esc_attr( $titulo ); ?>" width="110" height="165" loading="lazy" decoding="async">
							<?php else : ?>
								<div class="reading-card__placeholder" aria-hidden="true"><?php echo esc_html( mb_substr( $titulo, 0, 1, 'UTF-8' ) ); ?></div>
							<?php endif; ?>
						</div>

						<div class="reading-card__body">
							<div class="reading-card__badges">
								<?php echo $vc_render_estrelas( $nota ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php if ( '' !== $periodo_label ) : ?>
									<span class="reading-card__date"><?php echo esc_html( $periodo_label ); ?></span>
								<?php endif; ?>
							</div>

							<h3 class="reading-card__title"><?php echo esc_html( $titulo ); ?></h3>
							<?php if ( '' !== $autor ) : ?>
								<p class="reading-card__author"><?php echo esc_html( $autor ); ?></p>
							<?php endif; ?>

							<?php if ( '' !== $sinopse ) : ?>
								<p class="reading-card__synopsis"><?php echo esc_html( wp_trim_words( $sinopse, 36, '…' ) ); ?></p>
							<?php endif; ?>

							<?php if ( '' !== $resenha ) : ?>
								<div class="reading-card__actions">
									<a href="<?php echo esc_url( $resenha ); ?>" class="reading-card__review-link">
										<span>ler resenha completa</span>
										<span aria-hidden="true">→</span>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $vc_abandonados ) ) : ?>
		<section class="readings-section" data-reading-section="abandonado" aria-labelledby="heading-abandonados">
			<header class="readings-section__header">
				<h2 id="heading-abandonados" class="readings-section__title">leituras abandonadas</h2>
				<span class="readings-section__count"><?php echo (int) $vc_total_abandonados; ?> livros</span>
			</header>

			<div class="readings-list">
				<?php foreach ( $vc_abandonados as $livro ) :
					$titulo     = (string) $livro['titulo'];
					$autor      = (string) ( $livro['autor'] ?? '' );
					$capa       = (string) ( $livro['capa'] ?? '' );
					$nota       = (int) ( $livro['avaliacao_estrelas'] ?? 0 );
					$percentual = max( 0, min( 100, (int) ( $livro['percentual'] ?? 0 ) ) );
					$inicio_fmt = $vc_formatar_data_curta( $livro['inicio'] ?? null );
					$sinopse    = trim( (string) ( $livro['sinopse'] ?? '' ) );
					$resenha    = (string) ( $livro['link_resenha'] ?? '' );
					$busca_attr = mb_strtolower( $titulo . ' ' . $autor, 'UTF-8' );
				?>
					<article class="reading-card reading-card--abandoned" data-reading-item data-reading-query="<?php echo esc_attr( $busca_attr ); ?>">
						<div class="reading-card__cover-wrap">
							<?php if ( '' !== $capa ) : ?>
								<img class="reading-card__cover" src="<?php echo esc_url( $capa ); ?>" alt="Capa do livro <?php echo esc_attr( $titulo ); ?>" width="110" height="165" loading="lazy" decoding="async">
							<?php else : ?>
								<div class="reading-card__placeholder" aria-hidden="true"><?php echo esc_html( mb_substr( $titulo, 0, 1, 'UTF-8' ) ); ?></div>
							<?php endif; ?>
						</div>

						<div class="reading-card__body">
							<div class="reading-card__badges">
								<span class="reading-badge reading-badge--abandoned">
									<?php echo $percentual > 0 ? esc_html( "abandonado em {$percentual}%" ) : 'abandonado'; ?>
								</span>
								<?php echo $vc_render_estrelas( $nota ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php if ( '' !== $inicio_fmt ) : ?>
									<span class="reading-card__date">iniciado em <?php echo esc_html( $inicio_fmt ); ?></span>
								<?php endif; ?>
							</div>

							<h3 class="reading-card__title"><?php echo esc_html( $titulo ); ?></h3>
							<?php if ( '' !== $autor ) : ?>
								<p class="reading-card__author"><?php echo esc_html( $autor ); ?></p>
							<?php endif; ?>

							<?php if ( '' !== $sinopse ) : ?>
								<p class="reading-card__synopsis"><?php echo esc_html( wp_trim_words( $sinopse, 32, '…' ) ); ?></p>
							<?php endif; ?>

							<?php if ( '' !== $resenha ) : ?>
								<div class="reading-card__actions">
									<a href="<?php echo esc_url( $resenha ); ?>" class="reading-card__review-link">
										<span>ler o que achei no blog</span>
										<span aria-hidden="true">→</span>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $vc_nao_iniciado ) ) : ?>
		<section class="readings-section" data-reading-section="estante" aria-labelledby="heading-estante">
			<header class="readings-section__header">
				<h2 id="heading-estante" class="readings-section__title">na estante (quero ler)</h2>
				<span class="readings-section__count"><?php echo (int) $vc_total_estante; ?> livros</span>
			</header>

			<div class="readings-shelf-grid">
				<?php foreach ( $vc_nao_iniciado as $livro ) :
					$titulo     = (string) $livro['titulo'];
					$autor      = (string) ( $livro['autor'] ?? '' );
					$capa       = (string) ( $livro['capa'] ?? '' );
					$busca_attr = mb_strtolower( $titulo . ' ' . $autor, 'UTF-8' );
				?>
					<article class="reading-shelf-item" data-reading-item data-reading-query="<?php echo esc_attr( $busca_attr ); ?>">
						<div class="reading-shelf-item__cover-wrap">
							<?php if ( '' !== $capa ) : ?>
								<img class="reading-shelf-item__cover" src="<?php echo esc_url( $capa ); ?>" alt="Capa do livro <?php echo esc_attr( $titulo ); ?>" width="140" height="210" loading="lazy" decoding="async">
							<?php else : ?>
								<div class="reading-card__placeholder" aria-hidden="true"><?php echo esc_html( mb_substr( $titulo, 0, 1, 'UTF-8' ) ); ?></div>
							<?php endif; ?>
						</div>
						<div class="reading-shelf-item__info">
							<h3 class="reading-shelf-item__title"><?php echo esc_html( $titulo ); ?></h3>
							<?php if ( '' !== $autor ) : ?>
								<p class="reading-shelf-item__author"><?php echo esc_html( $autor ); ?></p>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<p class="readings-empty-state" data-reading-empty hidden>nenhum livro encontrado com esse filtro ou termo de busca.</p>
</article>

<?php
get_footer();
