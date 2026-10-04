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
	<div class="sidebar__header">
		<span class="sidebar__title">menu</span>
		<button
			type="button"
			class="sidebar__close"
			data-sidebar-close
			aria-label="fechar menu"
		>
			<?php echo vc_icone( 'x', 20 ); ?>
		</button>
	</div>

	<div class="sidebar__content">
		<div class="sidebar__search">
			<?php echo vc_formulario_de_busca( 'sidebar-search' ); ?>
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
					<span class="navlink__icon" aria-hidden="true"><?php echo vc_icone( $vc_item['icone'], 18 ); ?></span>
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
					<span class="navlink__icon" aria-hidden="true"><?php echo vc_icone( vc_icone_do_tema( $vc_tema->slug ), 18 ); ?></span>
					<span><?php echo esc_html( mb_strtolower( (string) $vc_tema->name, 'UTF-8' ) ); ?></span>
					<span class="navlink__count"><?php echo (int) $vc_tema->count; ?></span>
				</a>
			<?php endforeach; ?>
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
