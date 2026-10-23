<?php
/**
 * A barra lateral: as páginas fixas do site e os temas.
 *
 * Reproduz o `Sidebar.astro`. Duas seções só, e a razão está lá também: as
 * tags e a maturidade saíram do site, e o peso delas aqui custava mais para
 * ler do que devolvia para navegar.
 *
 * Em tela estreita toda a navegação se recolhe num `<details>` — que é nativo,
 * sem JavaScript nenhum. Em tela larga o CSS esconde o `<summary>` (o rótulo
 * que o próprio elemento desenha) e força o painel aberto.
 *
 * @package ViniciusCodes
 */

$vc_atual = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
$vc_esta  = static function ( string $href ) use ( $vc_atual ): bool {
	return '/' === $href ? '/' === $vc_atual : 0 === strpos( $vc_atual, $href );
};

$vc_temas = vc_temas_ordenados( true );
?>
<aside class="sidebar" id="barra-lateral" aria-label="Menu principal">
	<div class="sidebar__content">

		<nav class="sidebar__group" aria-labelledby="barra-grupo-paginas">
			<p class="sidebar__label" id="barra-grupo-paginas">páginas</p>
			<?php foreach ( vc_itens_do_menu( 'vc-principal' ) as $vc_item ) : ?>
				<a
					class="navlink navlink--page"
					href="<?php echo esc_url( $vc_item['href'] ); ?>"
					<?php echo $vc_item['contexto'] ? 'title="' . esc_attr( $vc_item['contexto'] ) . '"' : ''; ?>
					<?php echo $vc_item['contexto'] ? 'aria-label="' . esc_attr( $vc_item['label'] . ' — ' . $vc_item['contexto'] ) . '"' : ''; ?>
					<?php echo $vc_item['atual'] ? 'aria-current="page"' : ''; ?>
				>
					<span class="navlink__icon" aria-hidden="true"><?php echo vc_icone( $vc_item['icone'], 17 ); ?></span>
					<span><?php echo esc_html( mb_strtolower( (string) $vc_item['label'], 'UTF-8' ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</nav>

		<nav class="sidebar__group" aria-labelledby="barra-grupo-temas">
			<p class="sidebar__label" id="barra-grupo-temas">temas</p>
			<?php foreach ( $vc_temas as $vc_tema ) : ?>
				<?php $vc_href = '/tema/' . $vc_tema->slug . '/'; ?>
				<a
					class="navlink navlink--term"
					href="<?php echo esc_url( $vc_href ); ?>"
					<?php echo $vc_esta( $vc_href ) ? 'aria-current="page"' : ''; ?>
				>
					<span class="navlink__icon" aria-hidden="true"><?php echo vc_icone( vc_icone_do_tema( $vc_tema->slug ), 17 ); ?></span>
					<span><?php echo esc_html( mb_strtolower( (string) $vc_tema->name, 'UTF-8' ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</nav>

		<?php
		$vc_blogs_indicados = vc_blogroll_aleatorios( 4 );
		if ( ! empty( $vc_blogs_indicados ) ) :
		?>
		<section class="sidebar__group" aria-labelledby="barra-grupo-leia-tambem">
			<p class="sidebar__label" id="barra-grupo-leia-tambem">leia também</p>
			<div class="sidebar__blogroll-list">
				<?php foreach ( $vc_blogs_indicados as $vc_blog ) : ?>
					<?php
					$vc_fav_arquivo = $vc_blog['favicon'] ?? ( $vc_blog['dominio'] . '.png' );
					$vc_favicon_src = get_theme_file_uri( "assets/favicons/{$vc_fav_arquivo}" );
					?>
					<a
						class="navlink navlink--blogroll"
						href="<?php echo esc_url( $vc_blog['url'] ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						title="<?php echo esc_attr( $vc_blog['nome'] . ' — ' . $vc_blog['dominio'] ); ?>"
					>
						<img class="navlink__favicon" src="<?php echo esc_url( $vc_favicon_src ); ?>" alt="" width="16" height="16" loading="lazy" decoding="async">
						<span class="navlink__blogroll-nome"><?php echo esc_html( $vc_blog['nome'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
			<a class="navlink navlink--more" href="<?php echo esc_url( home_url( '/blogroll/' ) ); ?>" <?php echo $vc_esta( '/blogroll/' ) ? 'aria-current="page"' : ''; ?>>
				<span>veja mais</span>
				<span class="navlink__more-icon" aria-hidden="true"><?php echo vc_icone( 'arrow-up-right', 14 ); ?></span>
			</a>
		</section>
		<?php endif; ?>

		<?php
		$vc_arquivo_posts = vc_arquivo_agrupado();
		if ( ! empty( $vc_arquivo_posts ) ) :
		?>
		<section class="sidebar__group sidebar__archive" aria-labelledby="barra-grupo-arquivo">
			<p class="sidebar__label" id="barra-grupo-arquivo">arquivo de posts</p>
			<div class="sidebar__archive-tree">
				<?php foreach ( $vc_arquivo_posts as $vc_ano => $vc_dados_ano ) : ?>
					<details class="archive-tree__year">
						<summary class="archive-tree__summary">
							<span class="archive-tree__zippy" aria-hidden="true">►</span>
							<a href="<?php echo esc_url( get_year_link( (int) $vc_ano ) ); ?>" class="archive-tree__link archive-tree__link--year" onclick="event.stopPropagation();">
								<?php echo esc_html( $vc_ano ); ?>
							</a>
							<span class="archive-tree__count">(<?php echo (int) $vc_dados_ano['total']; ?>)</span>
						</summary>
						<div class="archive-tree__months">
							<?php foreach ( $vc_dados_ano['meses'] as $vc_mes => $vc_dados_mes ) : ?>
								<details class="archive-tree__month">
									<summary class="archive-tree__summary archive-tree__summary--month">
										<span class="archive-tree__zippy" aria-hidden="true">►</span>
										<a href="<?php echo esc_url( $vc_dados_mes['link'] ); ?>" class="archive-tree__link archive-tree__link--month" onclick="event.stopPropagation();">
											<?php echo esc_html( $vc_dados_mes['nome'] ); ?>
										</a>
										<span class="archive-tree__count">(<?php echo (int) $vc_dados_mes['total']; ?>)</span>
									</summary>
									<ul class="archive-tree__posts">
										<?php foreach ( $vc_dados_mes['posts'] as $vc_post_item ) : ?>
											<li class="archive-tree__post-item">
												<a href="<?php echo esc_url( $vc_post_item['url'] ); ?>" class="archive-tree__post-link" <?php echo $vc_esta( (string) wp_parse_url( $vc_post_item['url'], PHP_URL_PATH ) ) ? 'aria-current="page"' : ''; ?>>
													<?php echo esc_html( $vc_post_item['titulo'] ); ?>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								</details>
							<?php endforeach; ?>
						</div>
					</details>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>
	</div>

	<?php
	/*
	 * AQUI HAVIA o aviso "todos os textos desse site foram escritos por mim". Ele
	 * saiu a pedido.
	 *
	 * O assunto não se perdeu, e é por isso que a remoção é segura: o colofão
	 * (/colofao/, alcançável pelo rodapé) tem uma seção inteira sobre autoria, e
	 * mais precisa que o aviso era — lá está escrito que o código foi feito em
	 * parceria com IA e que a autoria do que está publicado é dele. O aviso era a
	 * versão comprimida disso, num lugar onde ninguém o procurava.
	 *
	 * Consequência que veio junto: no celular a barra lateral agora fica VAZIA,
	 * porque o menu já era escondido abaixo de 860px e o aviso era a única coisa
	 * que restava. O CSS esconde a coluna inteira nessa largura.
	 */
	?>
</aside>
