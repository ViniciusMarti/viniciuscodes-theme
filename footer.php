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
			&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> &middot; Feito à mão em Curitiba.
		</p>

		<div class="colophon__badges">
			<a class="colophon__badge colophon__badge--entreblogs" href="https://entreblogs.com.br" target="_blank" rel="noopener noreferrer" title="Entreblogs — Comunidade de blogagem coletiva" aria-label="Entreblogs — Comunidade de blogagem coletiva (abre em nova aba)">
				<span>entreblogs</span>
			</a>

			<a class="colophon__badge colophon__badge--not-by-ai" href="https://notbyai.fyi" target="_blank" rel="noopener noreferrer" title="Written by Human, Not By AI" aria-label="Written by Human, Not By AI (abre em nova aba)">
				<picture class="not-by-ai-picture">
					<source class="not-by-ai-source-dark" srcset="<?php echo esc_url( get_theme_file_uri( 'assets/icons/not-by-ai-white.svg' ) ); ?>" media="(prefers-color-scheme: dark)">
					<img class="not-by-ai-img" src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/not-by-ai-black.svg' ) ); ?>" data-dark-src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/not-by-ai-white.svg' ) ); ?>" data-light-src="<?php echo esc_url( get_theme_file_uri( 'assets/icons/not-by-ai-black.svg' ) ); ?>" alt="Written by Human, Not By AI" width="105" height="34" loading="lazy" decoding="async" />
				</picture>
			</a>
		</div>
	</div>
</footer>



<?php wp_footer(); ?>
</body>
</html>
