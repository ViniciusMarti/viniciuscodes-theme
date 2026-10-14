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
		<?php
		$vc_avatar_id = attachment_url_to_postid( vc_midia( 'vinicius-profile.webp' ) );
		?>
		<section class="sidebar__group sidebar__profile" aria-label="Sobre o autor">
			<div class="sidebar__profile-header">
				<?php if ( $vc_avatar_id ) : ?>
					<?php
					echo wp_get_attachment_image(
						$vc_avatar_id,
						'thumbnail',
						false,
						array(
							'class' => 'sidebar__avatar',
							'alt'   => 'Foto de perfil de Vinicius Oliveira',
						)
					);
					?>
				<?php else : ?>
					<img
						class="sidebar__avatar"
						src="<?php echo esc_url( vc_midia( 'vinicius-profile.webp' ) ); ?>"
						alt="Foto de perfil de Vinicius Oliveira"
						width="48"
						height="48"
						loading="lazy"
					/>
				<?php endif; ?>
				<div class="sidebar__profile-info">
					<p class="sidebar__profile-name">vinicius oliveira</p>
					<p class="sidebar__profile-handle">@viniciuscodes</p>
				</div>
			</div>

			<p class="sidebar__profile-bio">
				escrevo sobre tecnologia, dados, viagens e AuDHD.
			</p>

			<div class="sidebar__social">
				<a
					class="sidebar__social-link"
					href="https://linkedin.com/in/viniciuscodes/"
					target="_blank"
					rel="noopener noreferrer"
					title="LinkedIn"
					aria-label="LinkedIn (@viniciuscodes)"
				>
					<?php echo vc_icone( 'linkedin', 17 ); ?>
				</a>
				<a
					class="sidebar__social-link"
					href="https://github.com/ViniciusMarti"
					target="_blank"
					rel="noopener noreferrer"
					title="GitHub"
					aria-label="GitHub (@ViniciusMarti)"
				>
					<?php echo vc_icone( 'github', 17 ); ?>
				</a>
				<a
					class="sidebar__social-link"
					href="https://www.youtube.com/@viniciuscodes"
					target="_blank"
					rel="noopener noreferrer"
					title="YouTube"
					aria-label="Canal no YouTube (@viniciuscodes)"
				>
					<?php echo vc_icone( 'youtube', 17 ); ?>
				</a>
				<a
					class="sidebar__social-link"
					href="mailto:contato@viniciuscodes.com.br"
					title="E-mail"
					aria-label="Enviar e-mail para contato@viniciuscodes.com.br"
				>
					<?php echo vc_icone( 'mail', 17 ); ?>
				</a>
				<a
					class="sidebar__social-link"
					href="<?php echo esc_url( home_url( '/feed.xml' ) ); ?>"
					title="Feed RSS"
					aria-label="Feed RSS do blog"
				>
					<?php echo vc_icone( 'rss', 17 ); ?>
				</a>
			</div>
		</section>

		<div class="sidebar__group sidebar__search-wrap">
			<form class="site-search-form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
				<span class="site-search-form__icon" aria-hidden="true">
					<?php echo vc_icone( 'search', 16 ); ?>
				</span>
				<label class="screen-reader-text" for="sidebar-search-input">buscar no site</label>
				<input
					id="sidebar-search-input"
					type="search"
					name="s"
					value="<?php echo esc_attr( get_search_query() ); ?>"
					placeholder="buscar no site..."
					autocomplete="off"
				/>
			</form>
		</div>

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
					<span><?php echo esc_html( mb_strtolower( (string) $vc_item['label'], 'UTF-8' ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</nav>

		<nav class="sidebar__group" aria-labelledby="barra-grupo-temas">
			<p class="sidebar__label" id="barra-grupo-temas">temas</p>
			<?php
			$vc_temas_principais = array_slice( $vc_temas, 0, 5 );
			$vc_temas_extras     = array_slice( $vc_temas, 5 );
			?>
			<?php foreach ( $vc_temas_principais as $vc_tema ) : ?>
				<?php $vc_href = '/tema/' . $vc_tema->slug . '/'; ?>
				<a
					class="navlink navlink--term"
					href="<?php echo esc_url( $vc_href ); ?>"
					<?php echo $vc_esta( $vc_href ) ? 'aria-current="page"' : ''; ?>
				>
					<span><?php echo esc_html( mb_strtolower( (string) $vc_tema->name, 'UTF-8' ) ); ?></span>
				</a>
			<?php endforeach; ?>

			<?php if ( ! empty( $vc_temas_extras ) ) : ?>
				<?php
				$vc_tem_extra_ativo = false;
				foreach ( $vc_temas_extras as $vc_extra ) {
					if ( $vc_esta( '/tema/' . $vc_extra->slug . '/' ) ) {
						$vc_tem_extra_ativo = true;
						break;
					}
				}
				?>
				<details class="sidebar__temas-extras" <?php echo $vc_tem_extra_ativo ? 'open' : ''; ?>>
					<summary class="sidebar__temas-trigger">
						<span class="sidebar__temas-trigger-text">mais temas</span>
						<span class="sidebar__temas-trigger-arrow" aria-hidden="true">▾</span>
					</summary>
					<div class="sidebar__temas-extras-list">
						<?php foreach ( $vc_temas_extras as $vc_tema ) : ?>
							<?php $vc_href = '/tema/' . $vc_tema->slug . '/'; ?>
							<a
								class="navlink navlink--term"
								href="<?php echo esc_url( $vc_href ); ?>"
								<?php echo $vc_esta( $vc_href ) ? 'aria-current="page"' : ''; ?>
							>
								<span><?php echo esc_html( mb_strtolower( (string) $vc_tema->name, 'UTF-8' ) ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</details>
			<?php endif; ?>
		</nav>
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
