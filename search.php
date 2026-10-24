<?php
/**
 * O resultado da busca.
 *
 * POR QUE ESTE ARQUIVO PASSOU A EXISTIR. O tema não tinha `search.php`, e o
 * WordPress caía no `index.php`, que é a home. Medido antes de escrever:
 *
 *   busca COM resultado ..... um `<h2>notas recentes</h2>` sobre os resultados
 *   busca SEM resultado ..... o mesmo `<h2>` e a frase "nada por aqui ainda."
 *
 * As duas coisas estão erradas, e a segunda é a que machuca. O título dizia
 * "notas recentes" quando não eram recentes: eram resultados de uma procura. O
 * termo procurado não aparecia em lugar nenhum no corpo. E "nada por aqui ainda"
 * se lê como "este site não tem isso" — quando a verdade é "a sua busca não
 * achou". A diferença é grande para quem precisa saber se o problema foi a
 * palavra que escolheu ou se o assunto não existe aqui.
 *
 * O QUE A PÁGINA PASSA A DIZER, nesta ordem: o que foi procurado, quantos
 * resultados saíram, e — quando não sai nada — por onde continuar. A tarja e o
 * título seguem o mesmo desenho do arquivo de tema ("busca no site" em cima,
 * o termo grande embaixo), para as duas páginas de listagem se lerem igual.
 *
 * QUANDO NÃO HÁ RESULTADO, a saída são os TEMAS, não a lista inteira. Quem
 * procurou uma coisa e não achou precisa de um caminho mais estreito: treze
 * assuntos são um mapa; duzentas linhas são uma nova busca disfarçada.
 *
 * Os temas SEM texto ficam de fora aqui, ao contrário da home, que os mostra de
 * propósito. Lá a lista anuncia o que o autor cultiva; aqui ela é uma saída, e
 * um assunto sem nada não é saída de nada.
 *
 * @package ViniciusCodes
 */

get_header();

$vc_termo = (string) get_search_query();
$vc_total = (int) $GLOBALS['wp_query']->found_posts;

/*
 * Uma busca só achou algo se havia termo E veio nota.
 *
 * O "havia termo" não é preciosismo: com `?s=` vazio o WordPress não filtra
 * nada e devolve o acervo inteiro — o que pareceria "a busca achou tudo", e não
 * foi o que aconteceu.
 */
$vc_achou = '' !== $vc_termo && $vc_total > 0;

$vc_assuntos = array_values(
	array_filter(
		vc_temas_ordenados(),
		static function ( $vc_tema ): bool {
			return $vc_tema->count > 0;
		}
	)
);
?>
<header class="term-header">
	<h1 class="term-header__name">
		<?php echo '' !== $vc_termo ? esc_html( $vc_termo ) : 'nenhuma palavra procurada'; ?>
	</h1>

	<p class="term-header__desc">
		<?php
		if ( '' === $vc_termo ) {
			echo 'a busca chegou sem termo nenhum.';
		} elseif ( $vc_total > 0 ) {
			printf(
				'%d %s.',
				$vc_total,
				1 === $vc_total ? 'post encontrado' : 'posts encontrados'
			);
		} else {
			echo 'nenhum post encontrado.';
		}
		?>
	</p>
</header>

<?php if ( $vc_achou ) : ?>
	<?php
	/*
	 * A lista entra logo depois do cabeçalho de propósito: a regra
	 * `.term-header + .post-list` existe para tirar a borda de cima da lista
	 * quando ela encosta no cabeçalho, e um `div` no meio desfaria isso.
	 */
	get_template_part( 'template-parts/lista-de-textos' );

	vc_paginacao(
		(int) $GLOBALS['wp_query']->max_num_pages,
		max( 1, (int) get_query_var( 'paged' ) )
	);
	?>
<?php else : ?>
	<div class="prose">
		<?php if ( '' === $vc_termo ) : ?>
			<p>
				não veio palavra nenhuma, então não há o que procurar. para buscar,
				acrescente <code>?s=</code> e a palavra ao endereço — por exemplo,
				<code>?s=autismo</code>.
			</p>
		<?php else : ?>
			<p>
				nenhum texto deste site fala de
				<code><?php echo esc_html( $vc_termo ); ?></code>. pode ser que a
				palavra certa seja outra, ou pode ser que eu ainda não tenha escrito
				sobre isso.
			</p>
		<?php endif; ?>
	</div>

	<section class="section">
		<h2 class="section-title">assuntos que têm texto hoje</h2>

		<ul class="grid">
			<?php foreach ( $vc_assuntos as $vc_tema ) : ?>
				<li>
					<a class="card" href="<?php echo esc_url( '/tema/' . $vc_tema->slug . '/' ); ?>">
						<span class="card__icon" aria-hidden="true"><?php echo vc_icone( vc_icone_do_tema( $vc_tema->slug ), 24 ); ?></span>
						<p class="card__title"><?php echo esc_html( $vc_tema->name ); ?></p>
						<p class="card__meta"><?php echo esc_html( $vc_tema->count . ( 1 === $vc_tema->count ? ' post' : ' posts' ) ); ?></p>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<p class="section__foot">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">ver todos os textos →</a>
		</p>
	</section>
<?php endif; ?>

<?php
get_footer();
