<?php
/**
 * O convite à conversa. Reproduz o `PostFeedback.astro`.
 *
 * O site não tem comentários, e isso é decisão, não falta: conversa pública vira
 * ruído rápido e envelhece mal. O que existe no lugar é um endereço de e-mail,
 * com o assunto e o link do texto já preenchidos — assim quem escreve não
 * precisa montar a mensagem, e quem recebe sabe de que texto se trata sem
 * procurar.
 *
 * O e-mail abre com o assunto `Re: <título>`, que é o que qualquer cliente de
 * e-mail agrupa na mesma conversa do texto.
 *
 * O `↗` é decoração e está escondido de leitores de tela: o aviso de que abre
 * outra coisa está escrito em texto, no `post-feedback__nota`. Um glifo de seta
 * sozinho não diz nada a quem não enxerga.
 *
 * @package ViniciusCodes
 */

$vc_id     = get_the_ID();
$vc_titulo = get_the_title();
$vc_url    = get_permalink();

$vc_assunto = rawurlencode( 'Re: ' . $vc_titulo );
$vc_corpo   = rawurlencode(
	"olá, Vinicius!\n\n" .
	"li seu texto \"{$vc_titulo}\" e queria falar sobre ele:\n\n" .
	"{$vc_url}\n\n"
);

$vc_mailto = "mailto:contato@viniciuscodes.com.br?subject={$vc_assunto}&body={$vc_corpo}";

// Link do post correspondente no Bluesky (se cadastrado via meta _vc_bluesky_url) ou fallback de busca
$vc_bsky_url = get_post_meta( $vc_id, '_vc_bluesky_url', true );
if ( empty( $vc_bsky_url ) ) {
	$vc_bsky_url = 'https://bsky.app/intent/compose?text=' . rawurlencode( 'sobre "' . $vc_titulo . '" de @viniciuscodes.com.br: ' . $vc_url );
}
?>
<aside class="post-feedback">
	<h2 class="post-feedback__titulo">responder</h2>

	<p class="post-feedback__texto">
		me escreva em particular ou responda na rede:
	</p>

	<div class="post-feedback__acao">
		<a class="post-feedback__botao" href="<?php echo esc_url( $vc_mailto ); ?>">
			<?php echo vc_icone( 'mail', 16 ); ?>
			<span>me escrever sobre isto</span>
		</a>

		<a class="post-feedback__botao" href="<?php echo esc_url( $vc_bsky_url ); ?>" target="_blank" rel="noopener noreferrer">
			<?php echo vc_icone( 'bluesky', 16 ); ?>
			<span>responder no bluesky</span>
		</a>
	</div>

	<p class="post-feedback__nota">
		o botão de e-mail abre seu aplicativo de mensagens com o assunto e link já prontos.
	</p>
</aside>

