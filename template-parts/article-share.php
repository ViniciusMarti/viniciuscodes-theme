<?php
/**
 * Compartilhar. Reproduz o `PostShare.astro`.
 *
 * Fica no pé do texto, depois da leitura e antes do caminho de volta, porque é
 * esse o momento em que alguém decide mandar para outra pessoa.
 *
 * Três caminhos, e a ordem não é arbitrária:
 *
 * 1. `data-share-native` usa a folha de compartilhamento do próprio sistema —
 *    no celular é ela que lista os apps instalados. É o primeiro quando existe.
 * 2. WhatsApp, que é link comum e funciona sem JavaScript nenhum. É o único dos
 *    três que não depende de script, e por isso o único que aparece sozinho.
 * 3. `data-share-copy`, o copiar link.
 *
 * O `gostar` NÃO FAZ PARTE DOS TRÊS. Ele abre a fileira, mas não compartilha
 * nada: é feedback, e o que ele mede é outra coisa. Ele mora em
 * `template-parts/article-like.php` porque é o único pedaço deste bloco que as
 * notas também usam — o `single-nota.php` recusa o bloco inteiro pelo
 * `data-title`, e o porquê está escrito no arquivo do botão.
 *
 * Os dois que dependem de JavaScript começam `hidden` e são revelados por ele:
 * sem script, ninguém vê um botão que não faria nada.
 *
 * HÁ UM ÚNICO RETORNO VISÍVEL, e é o `post-share__status`. A alternativa comum
 * — trocar o texto do próprio botão para "copiado!" e manter uma região ao vivo
 * escondida — faz quem usa leitor de tela ouvir a mesma confirmação duas vezes.
 * Aqui o texto muda só ali: quem enxerga vê, quem ouve ouve, cada um uma vez.
 *
 * O JavaScript fica em `assets/site.js`, enfileirado — não há script solto
 * neste arquivo.
 *
 * @package ViniciusCodes
 */

$vc_url    = get_permalink();
$vc_titulo = get_the_title();
$vc_whats  = 'https://wa.me/?text=' . rawurlencode( $vc_titulo . "\n" . $vc_url );
?>
<aside
	class="post-share"
	aria-label="compartilhar este texto"
	data-post-share
	data-url="<?php echo esc_url( $vc_url ); ?>"
	data-title="<?php echo esc_attr( $vc_titulo ); ?>"
>
	<p class="post-share__label" id="post-share-label">compartilhar</p>

	<div class="post-share__actions">
		<button type="button" class="post-share__btn post-share__btn--primary" data-share-native hidden>
			<?php echo vc_icone( 'share-2', 16 ); ?>
			compartilhar
		</button>

		<button type="button" class="post-share__btn" data-share-copy hidden>
			<?php echo vc_icone( 'link', 16 ); ?>
			copiar link
		</button>
	</div>

	<p class="post-share__status" role="status" aria-live="polite" data-share-status></p>
</aside>
