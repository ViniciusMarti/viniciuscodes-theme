<?php
/**
 * O pé de toda página.
 *
 * Fecha o que o `header.php` abriu — o `div.content__inner`, o `main` e o
 * `div.layout` — e imprime o colofão. Ver a explicação no topo do header sobre
 * por que o envoltório do layout atravessa os dois arquivos.
 *
 * @package ViniciusCodes
 */
?>
		</div><!-- .content__inner -->
	</main><!-- .content -->
</div><!-- .layout -->

<?php
/*
 * O colofão: os dois selos das comunidades de que o site participa.
 *
 * ISTO JÁ TEVE MAIS DUAS COISAS, e as duas saíram a pedido em 04/10/2026: a
 * linha de copyright ("© ... feito à mão em Curitiba, com muito café") e a lista
 * de endereços do rodapé — manifesto, changelog e contato.
 *
 * Os endereços não se perderam: foram para a barra lateral, junto das outras
 * páginas do site, que é onde alguém os procura. O changelog não existia lá e
 * passou a existir em `vc_navegacao()`; o manifesto e o contato JÁ estavam, e
 * repetir os três na barra lateral seria criar duplicata — exatamente o que o
 * acervo acabou de tirar de si mesmo.
 *
 * O que sobra é o que o rodapé tem de próprio: os selos. Estrutura e classes do
 * `Footer.astro`.
 */
?>
<footer class="colophon">
	<div class="colophon__inner">
		<div class="colophon__badges">
			<a class="colophon__badge colophon__badge--text" href="https://entreblogs.com.br" target="_blank" rel="noopener noreferrer" title="Entreblogs ? Comunidade de blogagem coletiva" aria-label="Entreblogs ? Comunidade de blogagem coletiva (abre em nova aba)">
				<span>entreblogs</span>
			</a>

			<a class="colophon__badge colophon__badge--not-by-ai" href="https://notbyai.fyi" target="_blank" rel="noopener noreferrer" title="Written by Human, Not By AI" aria-label="Written by Human, Not By AI (abre em nova aba)">
				<picture class="not-by-ai-picture">
					<source class="not-by-ai-source-dark" srcset="<?php echo esc_url( get_theme_file_uri( 'assets/icons/not-by-ai-white.svg' ) ); ?>" media="(prefers-color-scheme: dark)">
					<img class="not-by-ai-img" src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/not-by-ai-black.svg' ) ); ?>" data-dark-src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/not-by-ai-white.svg' ) ); ?>" data-light-src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/not-by-ai-black.svg' ) ); ?>" alt="Written by Human, Not By AI" width="131" height="42" loading="lazy" decoding="async" />
				</picture>
			</a>
		</div>
	</div>
</footer>

<?php
/*
 * O botão de voltar ao topo.
 *
 * Fica escondido pelo CSS e é revelado pelo script depois da primeira tela:
 * sem JavaScript, ninguém vê um controle que não funcionaria. `position: fixed`
 * mantém ele fora do fluxo, então ele nunca empurra o conteúdo.
 */
?>
<button class="to-top" type="button" data-to-top aria-label="voltar ao topo">
	<?php echo vc_icone( 'arrow-up', 18 ); ?>
</button>

<?php wp_footer(); ?>
</body>
</html>
