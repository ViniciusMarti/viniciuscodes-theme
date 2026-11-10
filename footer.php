<?php
/**
 * O pé de toda página.
 *
 * Fecha o que o `header.php` abriu — o `div.content__inner`, o `main` e o
 * `div.layout` — e imprime o colofão.
 *
 * @package ViniciusCodes
 */
?>
		</div><!-- .content__inner -->
	</main><!-- .content -->
</div><!-- .layout -->

<footer class="colophon">
	<div class="colophon__inner">
		<div class="colophon__info">
			<nav class="colophon__nav" aria-label="Navegação secundária do rodapé">
				<a class="colophon__link" href="<?php echo esc_url( home_url( '/sobre/' ) ); ?>">sobre</a>
				<span class="colophon__sep" aria-hidden="true">·</span>
				<a class="colophon__link" href="<?php echo esc_url( home_url( '/blogroll/' ) ); ?>">blogroll</a>
				<span class="colophon__sep" aria-hidden="true">·</span>
				<a class="colophon__link" href="<?php echo esc_url( home_url( '/contato/' ) ); ?>">contato</a>
				<span class="colophon__sep" aria-hidden="true">·</span>
				<a class="colophon__link" href="<?php echo esc_url( home_url( '/feed.xml' ) ); ?>" title="Feed RSS">feed rss</a>
			</nav>

			<p class="colophon__copy">
				&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> @viniciuscodes &middot; Feito à mão em Curitiba.<br>
				Todos os conteúdos pessoais são escritos inteiramente por mim. Utilizo IA como ferramenta para estruturar conteúdos técnicos, aprofundar ideias e potencializar o que eu já escreveria. O layout deste site também foi desenvolvido com auxílio de IA, sob minha supervisão direta e com foco total na melhor experiência de leitura e acessibilidade para pessoas neurodivergentes.
			</p>
		</div>

		<section class="colophon__selo" aria-label="Selo Entreblogs">
			<a class="colophon__selo-link" href="https://entreblogs.com.br" target="_blank" rel="noopener noreferrer">
				<img class="colophon__selo-img" src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEi0Bbck0NgwO4Lq0vPdvH7Qi8VU72pCgJKgNeqaoBzQBbOGDBAzzkTS0PlRop4L-HhgjGj36W2w0079R704GQabAYigZZmgGf5QexmFN0IqM0uLzmrhyphenhyphenRRMXCFSKJDxDX5ZuRLHtsazL7YKALC3kMAo0t5bQhddiRUrOl_xb6aRojyb8oWIZ-0fU0olmxMJ/s700/GIF%20Selo%202.0.gif" alt="Selo 2.0" width="400" loading="lazy" decoding="async" />
			</a>
		</section>
	</div>
</footer>



<?php wp_footer(); ?>
</body>
</html>
