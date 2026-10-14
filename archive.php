<?php
/**
 * O arquivo de um tema, e o de qualquer outra listagem.
 *
 * Reproduz a página `/tema/<tema>/` do site. O cabeçalho dela é o
 * `term-header`, com o ícone do tema ao lado do nome — e não o cabeçalho de
 * artigo, porque aqui não há data nem tempo de leitura: não é um texto, é uma
 * lista.
 *
 * A lista vai com `mostrar_temas => false`. A razão está no próprio componente
 * original: dentro de um tema, repetir o nome do tema em cada linha não diz
 * nada de novo — o leitor já está naquela página para isso.
 *
 * @package ViniciusCodes
 */

get_header();

/*
 * `get_queried_object()`, e não `is_tax()`: com o tema virando categoria nativa,
 * `is_tax()` responde FALSO num arquivo de categoria — ele só vale para
 * taxonomia própria. O cabeçalho cairia no ramo genérico, sem o ícone do tema.
 * Perguntar pelo objeto resolve para categoria, etiqueta e taxonomia, e continua
 * respondendo null num arquivo de autor ou de data, onde não há termo nenhum.
 */
$vc_termo = get_queried_object();
$vc_termo = ( $vc_termo instanceof WP_Term ) ? $vc_termo : null;

/*
 * O QUE ESTA PÁGINA É, dito antes do título.
 *
 * Um arquivo de tema mostrava só o nome do tema e uma lista embaixo
 * ("marketing" e mais nada). Quem chega por um buscador não tinha como saber
 * se aquilo era um título de texto ou uma lista filtrada — e é justamente essa
 * dúvida que trava quem precisa de previsibilidade: o nome do tema sozinho não
 * diz o critério do agrupamento.
 *
 * A tarja usa a classe que já existia na folha (`.term-header__kind`) e não
 * estava sendo usada por nenhum template: o desenho já previa este rótulo, o
 * tema só não o imprimia.
 *
 * E a DESCRIÇÃO do tema entra logo abaixo do título. Ela existe para os treze
 * temas, é escrita à mão, e não aparecia em página nenhuma do site: era o texto
 * mais útil para dizer o que se vai ler ali, e estava invisível.
 */
$vc_tipo = 'arquivo';

if ( $vc_termo instanceof WP_Term ) {
	$vc_tipo = 'posts neste tema';
} elseif ( is_author() ) {
	$vc_tipo = 'textos deste autor';
} elseif ( is_date() ) {
	$vc_tipo = 'posts deste período';
}
?>
<header class="term-header">
	<p class="term-header__kind"><?php echo esc_html( $vc_tipo ); ?></p>

	<h1 class="term-header__name">
		<?php if ( $vc_termo instanceof WP_Term ) : ?>
			<span class="term-header__icon" aria-hidden="true">
				<?php echo vc_icone( vc_icone_do_tema( $vc_termo->slug ), 22 ); ?>
			</span>
			<?php echo esc_html( $vc_termo->name ); ?>
		<?php else : ?>
			<?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?>
		<?php endif; ?>
	</h1>

	<?php if ( $vc_termo instanceof WP_Term && '' !== trim( (string) $vc_termo->description ) ) : ?>
		<p class="term-header__desc"><?php echo wp_kses_post( $vc_termo->description ); ?></p>
	<?php endif; ?>
</header>

<?php
get_template_part(
	'template-parts/lista-de-textos',
	null,
	array(
		'mostrar_temas'  => ! is_category(),
		'mostrar_resumo' => true,
	)
);

/*
 * A paginação oficial do arquivo. O arquivo usa a consulta principal, e o
 * WordPress já a pagina (dez por página), então aqui só o pager é desenhado —
 * com os endereços que o próprio núcleo calcula (`/tema/x/page/2/`).
 */
vc_paginacao(
	(int) $GLOBALS['wp_query']->max_num_pages,
	max( 1, (int) get_query_var( 'paged' ) )
);

get_footer();
