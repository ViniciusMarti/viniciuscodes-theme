<?php
/**
 * O cartão de uma nota, usado no arquivo `/notas/` e na home.
 *
 * POR QUE UM PARCIAL. O cartão nasceu no `archive-nota.php`, e a home mostrava a
 * mesma nota com outro desenho: uma linha de lista editorial, sem avatar, sem o
 * número e sem o gostar. Duas notas iguais com duas caras na mesma sessão de
 * leitura é o tipo de diferença que ninguém pede e que ninguém sabe explicar
 * depois. Aqui o desenho mora num lugar só.
 *
 * O `<li>` NÃO ESTÁ AQUI, e é de propósito: ele carrega coisas de cada tela (o
 * `id="nota-N"`, o `data-temas` que o arquivo usa para filtrar, e as classes do
 * `post_class()`). O parcial desenha o CARTÃO, que é o que as duas telas têm em
 * comum.
 *
 * O CABEÇALHO É O QUE ANTES ERA A META, e a ordem mudou de propósito: o avatar
 * abre a linha porque ele é a âncora visual do cartão, e o número, que abria a
 * meta, desceu para o rodapé de ações — lá ele é um link de permalink, que é o
 * que ele sempre foi.
 *
 * O `@` SÓ APARECE QUANDO DIZ ALGUMA COISA. O `display_name` do autor é
 * "viniciuscodes", igual ao `user_login`: com os dois impressos, o cabeçalho
 * leria "viniciuscodes @viniciuscodes", que é ruído. A comparação resolve sozinha
 * no dia em que o nome de exibição virar "Vinicius" — aí o arroba passa a
 * distinguir, e aparece.
 *
 * @package ViniciusCodes
 */

$vc_numero = vc_nota_numero();
$vc_autor  = (string) get_the_author();
$vc_login  = (string) get_the_author_meta( 'user_login' );
$vc_avatar = vc_midia( 'vinicius-profile.webp' );
?>
<div class="nota__cartao">
	<p class="nota__cabecalho">
		<?php if ( '' !== $vc_avatar ) : ?>
			<img
				class="nota__avatar"
				src="<?php echo esc_url( $vc_avatar ); ?>"
				alt=""
				width="34"
				height="34"
				loading="lazy"
				decoding="async"
			/>
		<?php endif; ?>

		<?php
		/*
		 * O AUTOR É OPCIONAL, e a checagem não é paranoia: medido no site, uma
		 * nota criada por `wp post create` sem `--post_author` fica com
		 * `post_author = 0`. Sem o `if`, o `h-card` sai vazio e o separador
		 * seguinte fica pendurado depois do avatar.
		 */
		if ( '' !== $vc_autor ) :
			?>
			<span class="p-author h-card nota__autor">
				<a class="u-url p-name" rel="author" href="<?php echo esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ); ?>"><?php echo esc_html( $vc_autor ); ?></a>
			</span>
		<?php endif; ?>

		<?php if ( '' !== $vc_login && $vc_login !== $vc_autor ) : ?>
			<span class="nota__handle">@<?php echo esc_html( $vc_login ); ?></span>
		<?php endif; ?>

		<?php
		/*
		 * O SEPARADOR É EXPLÍCITO, e não o `::before` do tema.
		 *
		 * O `.post-item__meta > * + *::before` do `global.css` pendura um `·`
		 * entre irmãos, o que só funciona enquanto TODO elemento da linha for
		 * irmão — e o avatar, que precisa vir colado ao nome, não é um deles. Um
		 * separador desenhado por posição aqui viraria um ponto solto depois do
		 * avatar. O `<span>` com `aria-hidden` diz onde o ponto vai.
		 */
		?>
		<span class="nota__sep" aria-hidden="true">·</span>

		<a class="u-url nota__tempo" href="<?php the_permalink(); ?>">
			<time class="dt-published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>" title="<?php echo esc_attr( get_the_date( 'j \d\e F \d\e Y, H:i' ) ); ?>"><?php echo esc_html( vc_nota_tempo_relativo() ); ?></time>
		</a>
	</p>

	<div class="e-content prose nota__conteudo">
		<?php the_content(); ?>
	</div>

	<?php
	/*
	 * O RODAPÉ DE AÇÕES, e o número entra nele.
	 *
	 * O `#N` era o primeiro elemento da meta, acima do texto. Numa linha do
	 * tempo ele é o permalink da nota — a mesma função do "ver publicação" de um
	 * post de rede social —, e o lugar dele é junto das outras ações, no pé do
	 * cartão, onde o olho o procura depois de ler. Acima do texto ele competia
	 * com o avatar pela atenção de abertura, que agora tem um dono só.
	 *
	 * O "GOSTAR" É COMPACTO, e a diferença é de densidade, não de gosto. A lista
	 * mostra dez notas de uma vez, e dez pílulas de 44px embaixo de dez notas
	 * viram uma barra de controles — o oposto do que o próprio tema diz do bloco
	 * de compartilhamento, que se apresenta como um pedaço da informação do
	 * texto e não como um painel parafusado nele. O coração com a contagem diz a
	 * mesma coisa e some no visual.
	 *
	 * O `z-index` DESTE BOTÃO NÃO É ENFEITE: o cartão inteiro é o alvo do clique
	 * (ver "o cartão inteiro é o alvo do clique", no `assets/notas.css`), e essa
	 * área nasce em `z-index: 0`. Sem número maior, o clique no coração seria
	 * engolido e o botão ficaria decorativo. O mesmo vale para o link do `#N`.
	 *
	 * O RÓTULO LEVA O NÚMERO DA NOTA porque aqui o botão não tem rótulo visível:
	 * sem o número, quem usa leitor de tela ouviria "gostar desta nota" três
	 * vezes na mesma página, sem saber qual é qual.
	 */
	?>
	<div class="nota__acoes">
		<?php
		get_template_part(
			'template-parts/article',
			'like',
			array(
				'compacto' => true,
				'rotulo'   => $vc_numero > 0
					/* translators: %d: o número da nota, como ele aparece na linha do tempo. */
					? sprintf( __( 'gostar da nota #%d', 'viniciuscodes' ), $vc_numero )
					: __( 'gostar desta nota', 'viniciuscodes' ),
			)
		);

		if ( $vc_numero > 0 ) :
			?>
			<a class="vc-nota-numero nota__numero" href="<?php the_permalink(); ?>">#<?php echo (int) $vc_numero; ?></a>
			<?php
		endif;
		?>
	</div>
</div>
