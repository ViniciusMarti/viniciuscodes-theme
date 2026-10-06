<?php
/**
 * A página inicial.
 *
 * A ORDEM É DE QUEM CHEGA, e não de quem mantém. A home listava quatro blocos
 * disputando a primeira tela — retrato com dois parágrafos de bio, grade de doze
 * temas, oito textos e quatro notas — e a barra lateral repetia os MESMOS doze
 * temas. Eram duas navegações e três listas na mesma página, sem hierarquia
 * entre elas.
 *
 * Agora são três blocos, e cada um responde a uma pergunta na ordem em que ela
 * aparece:
 *
 *   1. o que é isto           -> a apresentação (um parágrafo)
 *   2. o que saiu por último  -> os textos, e depois as notas
 *   3. e o resto?             -> os temas, no fim
 *
 * OS TEMAS SAÍRAM DA POSIÇÃO DE DESTAQUE. Eles vinham logo depois do retrato,
 * antes de qualquer coisa para ler, e ocupavam uma grade de doze cartões. A
 * barra lateral já os mostra inteiros em toda página do site, então a grade era
 * a mesma lista duas vezes na mesma tela. Aqui eles voltam menores e no fim,
 * como porta de saída para quem não achou o que queria nas listas.
 *
 * AS DESCRIÇÕES DE SEÇÃO FICAM. Cada lista diz o que é em uma linha, porque a
 * diferença entre texto e nota é a pergunta que a home precisa responder e não
 * deixar para o leitor deduzir.
 *
 * @package ViniciusCodes
 */

get_header();

/*
 * Apresentação de abertura do autor (retrato, frase e um parágrafo).
 */
if ( is_home() ) {
	get_template_part( 'template-parts/apresentacao' );
}

$vc_temas_da_home = is_home() ? vc_temas_ordenados() : array();
?>

<section class="section" id="textos">
	<div class="section__cabeca">
		<h2 class="section-title">textos</h2>
		<p class="section__desc">os longos, com título, escritos com calma. um de cada vez.</p>
	</div>

	<?php if ( have_posts() ) : ?>
		<ul class="post-list post-list--editorial">
			<?php
			while ( have_posts() ) :
				the_post();

				$vc_id    = (int) get_the_ID();
				$vc_temas = vc_temas_do_post( $vc_id );
				?>
				<li class="post-item post-item--editorial">
					<?php if ( ! empty( $vc_temas ) ) : ?>
						<?php $vc_tema_principal = reset( $vc_temas ); ?>
						<span class="post-item__theme">
							<a href="<?php echo esc_url( vc_url_do_tema( $vc_tema_principal['slug'] ) ); ?>">
								<span class="meta-icon" aria-hidden="true"><?php echo vc_icone( $vc_tema_principal['icone'], 15 ); ?></span>
								<span><?php echo esc_html( mb_strtolower( (string) $vc_tema_principal['nome'], 'UTF-8' ) ); ?></span>
							</a>
						</span>
					<?php endif; ?>

					<h2 class="post-item__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
				</li>
				<?php
			endwhile;
			?>
		</ul>
	<?php else : ?>
		<p class="empty">nada por aqui ainda.</p>
	<?php endif; ?>

	<p class="section__foot">
		<a href="<?php echo esc_url( home_url( '/posts/' ) ); ?>">ver todos os textos →</a>
	</p>
</section>

<?php
/*
 * AS NOTAS VÊM DEPOIS DOS TEXTOS, e a ordem não é arbitrária: os textos são o
 * trabalho longo, que é o que alguém que chega de fora deve encontrar primeiro.
 * A nota é o registro do dia, e faz mais sentido como "e tem mais isto" do que
 * como a primeira coisa da página.
 *
 * CONSULTA PRÓPRIA, e não o loop principal: o loop principal foi consumido pela
 * seção de textos. O `no_found_rows` evita a contagem que ninguém usa.
 */
$vc_notas_home = new WP_Query(
	array(
		'post_type'      => defined( 'VC_NOTA' ) ? VC_NOTA : 'nota',
		'posts_per_page' => 3,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	)
);

if ( $vc_notas_home->have_posts() ) :
	?>
	<section class="section" id="notas">
		<div class="section__cabeca">
			<h2 class="section-title">notas</h2>
			<p class="section__desc">curtas, sem título, escritas na hora. é o que eu escreveria numa rede social, só que no meu domínio.</p>
		</div>

		<?php
		/*
		 * O MESMO CARTÃO DO ARQUIVO, e é o ponto: a nota da home e a nota do
		 * `/notas/` são a mesma coisa, e mostrá-las com dois desenhos diferentes
		 * fazia o leitor achar que eram duas coisas. O cartão mora em
		 * `template-parts/card-nota.php`.
		 *
		 * A LISTA MUDA JUNTO, e não é detalhe: o `post-list--editorial` desenha
		 * uma linha entre os itens, e o cartão já traz a própria borda. O
		 * `post-list--notas` troca essa linha por `gap`, que é o respiro que o
		 * cartão espera entre um e outro.
		 */
		?>
		<ul class="post-list post-list--notas">
			<?php
			while ( $vc_notas_home->have_posts() ) :
				$vc_notas_home->the_post();
				?>
				<li id="nota-<?php the_ID(); ?>" <?php post_class( 'post-item post-item--nota h-entry' ); ?>>
					<?php get_template_part( 'template-parts/card', 'nota' ); ?>
				</li>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</ul>

		<p class="section__foot">
			<a href="<?php echo esc_url( home_url( '/notas/' ) ); ?>">ver todas as notas →</a>
		</p>
	</section>
	<?php
endif;

/*
 * OS TEMAS, NO FIM E EM CHIPS.
 *
 * A grade de doze cartões virou uma faixa de chips: o ícone e o nome, sem
 * contagem. O cartão existia para fazer volume numa grade de três colunas; numa
 * faixa que quebra sozinha, o mesmo conteúdo ocupa três linhas em vez de quatro
 * fileiras de cartões.
 *
 * A CONTAGEM SAIU, e é a mesma decisão que tirou os números da barra lateral:
 * "7 textos" é informação de catálogo, não de leitura, e envelhece na tela.
 */
if ( ! empty( $vc_temas_da_home ) ) :
	?>
	<section class="section" id="temas">
		<div class="section__cabeca">
			<h2 class="section-title">temas</h2>
			<p class="section__desc">o que eu escrevo, por assunto.</p>
		</div>

		<ul class="chips">
			<?php foreach ( $vc_temas_da_home as $vc_tema ) : ?>
				<li>
					<a href="<?php echo esc_url( vc_url_do_tema( $vc_tema->slug ) ); ?>">
						<span class="chips__icone" aria-hidden="true"><?php echo vc_icone( vc_icone_do_tema( $vc_tema->slug ), 15 ); ?></span>
						<span><?php echo esc_html( mb_strtolower( (string) $vc_tema->name, 'UTF-8' ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<?php
get_footer();
