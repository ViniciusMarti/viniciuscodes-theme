<?php
/**
 * A lista de textos. É o mesmo bloco na página inicial, na listagem e no
 * arquivo de um tema.
 *
 * Reproduz o `PostList.astro` nas opções que o site usa para variar a lista sem
 * escrever outra:
 *
 *   get_template_part( 'template-parts/lista-de-textos', null, array(
 *       'mostrar_temas' => false,   // dentro de um tema, ele se repetiria
 *   ) );
 *
 * NENHUMA LISTA IMPRIME O COMEÇO DO TEXTO. Data e título bastam para decidir se
 * vale entrar, e o parágrafo de abertura é o próprio início do artigo: repetido
 * embaixo do título, ele se lê duas vezes. A home já era assim, o arquivo de um
 * tema passou a ser, e com isso a lista ficou igual em toda parte do site.
 *
 * O `data-temas` em cada linha é o que permite filtrar sem sair da página: traz
 * os slugs dos temas do post, separados por espaço, e o script da listagem
 * compara com o tema do botão apertado. Sai sempre, e não só na listagem, para
 * que a linha não precise saber onde está sendo usada.
 *
 * @package ViniciusCodes
 */

$vc_opcoes = wp_parse_args(
	$args ?? array(),
	array(
		'mostrar_meta'  => true,
		'mostrar_temas' => true,
		'vazio'         => 'nada por aqui ainda.',
	)
);

if ( ! have_posts() ) {
	printf( '<p class="empty">%s</p>', esc_html( $vc_opcoes['vazio'] ) );
	return;
}
?>
<ul class="post-list">
	<?php
	while ( have_posts() ) :
		the_post();

		$vc_id    = (int) get_the_ID();
		$vc_temas = vc_temas_do_post( $vc_id );
		$vc_slugs = array();

		foreach ( $vc_temas as $vc_tema ) {
			$vc_slugs[] = $vc_tema['slug'];
		}
		?>
		<li class="post-item" data-temas="<?php echo esc_attr( implode( ' ', $vc_slugs ) ); ?>">
			<?php if ( $vc_opcoes['mostrar_meta'] ) : ?>
				<p class="post-item__meta">
					<?php
					/*
					 * O TEMA VEM ANTES DA DATA, e a ordem é a mensagem. A data abrindo
					 * a linha puxava o olho para a cronologia: lia-se "publicado em
					 * 25/09" antes de saber sobre o que era o texto. Numa lista, o que
					 * o leitor procura é o assunto; quando foi escrito é orientação, e
					 * orientação vai no fim.
					 *
					 * O tema anda nesta linha porque é metadado sobre o texto: diz aonde
					 * ele pertence. O ponto antes de cada parte é desenhado pelo CSS na
					 * parte que ele introduz, e não como um <span> solto — assim uma
					 * linha quebrada nunca termina num ponto órfão.
					 */
					?>
					<?php if ( $vc_opcoes['mostrar_temas'] ) : ?>
						<?php foreach ( $vc_temas as $vc_tema ) : ?>
							<span class="post-item__meta__part">
								<a href="<?php echo esc_url( '/tema/' . $vc_tema['slug'] . '/' ); ?>">
									<span class="meta-icon" aria-hidden="true">
										<?php echo vc_icone( $vc_tema['icone'], 15 ); ?>
									</span>
									<span><?php echo esc_html( $vc_tema['nome'] ); ?></span>
								</a>
							</span>
						<?php endforeach; ?>
					<?php endif; ?>

					<?php
					/*
					 * A data vai no `datetime` no formato ISO, e não no formato que o
					 * leitor vê: é o que leitores de tela e buscadores entendem sem
					 * depender do idioma da página.
					 */
					?>
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $vc_id ) ); ?>"><?php echo esc_html( vc_data_curta( $vc_id ) ); ?></time>
				</p>
			<?php endif; ?>

			<h2 class="post-item__title">
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h2>
		</li>
		<?php
	endwhile;
	?>
</ul>
