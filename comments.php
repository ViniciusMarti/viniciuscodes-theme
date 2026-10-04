<?php
/**
 * O template de comentários zen e minimalista.
 *
 * Princípios:
 * 1. Sem ruído visual ou caixas pesadas.
 * 2. Tipografia alinhada ao restante do site.
 * 3. Campos simples (nome, e-mail, comentário) e honeypot anti-spam.
 * 4. Suporte nativo a comentários locais e webmentions/pingbacks da IndieWeb.
 *
 * @package ViniciusCodes
 */

if ( post_password_required() ) {
	return;
}

$vc_total_comentarios = get_comments_number();
?>

<section id="comentarios" class="post-comments" aria-label="comentários e respostas">
	<div class="post-comments__header">
		<h2 class="post-comments__title">
			<span class="meta-icon" aria-hidden="true"><?php echo vc_icone( 'message-circle', 18 ); ?></span>
			<span>conversa pública</span>
			<?php if ( $vc_total_comentarios > 0 ) : ?>
				<span class="post-comments__count">(<?php echo (int) $vc_total_comentarios; ?>)</span>
			<?php endif; ?>
		</h2>
		<p class="post-comments__subtitle">
			comentários abertos e moderados antes da primeira publicação. sem rastreadores ou captchas.
		</p>
	</div>

	<?php if ( have_comments() ) : ?>
		<ol class="post-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 0, // Sem avatares externos por privacidade e leveza
					'format'      => 'html5',
					'callback'    => function ( $comment, $args, $depth ) {
						$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
						?>
						<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'post-comment', $comment ); ?>>
							<article class="post-comment__body">
								<header class="post-comment__meta">
									<strong class="post-comment__author"><?php comment_author_link(); ?></strong>
									<span class="post-comment__dot" aria-hidden="true">·</span>
									<time class="post-comment__date" datetime="<?php comment_time( 'c' ); ?>">
										<?php comment_date( 'd \d\e F \d\e Y' ); ?>
									</time>
								</header>

								<?php if ( '0' === $comment->comment_approved ) : ?>
									<p class="post-comment__moderation">
										<em>seu comentário foi recebido e aguarda aprovação prévia.</em>
									</p>
								<?php endif; ?>

								<div class="post-comment__content">
									<?php comment_text(); ?>
								</div>

								<div class="post-comment__reply">
									<?php
									comment_reply_link(
										array_merge(
											$args,
											array(
												'depth'     => $depth,
												'max_depth' => $args['max_depth'],
												'reply_text' => 'responder',
											)
										)
									);
									?>
								</div>
							</article>
						<?php
					},
				)
			);
			?>
		</ol>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<nav class="post-comments__nav" aria-label="navegação de comentários">
				<?php paginate_comments_links(); ?>
			</nav>
		<?php endif; ?>

	<?php else : ?>
		<?php if ( comments_open() ) : ?>
			<p class="post-comments__empty">ainda não há comentários por aqui. seja a primeira pessoa a deixar uma resposta!</p>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( comments_open() ) : ?>
		<div class="post-comments__form-wrapper">
			<?php
			comment_form(
				array(
					'title_reply'          => 'deixar um comentário',
					'title_reply_to'       => 'responder a %s',
					'cancel_reply_link'    => 'cancelar resposta',
					'label_submit'         => 'publicar comentário',
					'class_submit'         => 'post-comments__submit',
					'comment_notes_before' => '',
					'comment_notes_after'  => '',
					'comment_field'        => '<div class="post-comments__field">'
						. '<label for="comment">mensagem <span class="required">*</span></label>'
						. '<textarea id="comment" name="comment" cols="45" rows="5" required></textarea>'
						. '</div>',
					'fields'               => array(
						'author' => '<div class="post-comments__field">'
							. '<label for="author">nome ou como prefere ser chamado <span class="required">*</span></label>'
							. '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ?? '' ) . '" size="30" required autocomplete="name" />'
							. '</div>',
						'email'  => '<div class="post-comments__field">'
							. '<label for="email">e-mail <span class="post-comments__hint">(opcional, não será publicado)</span></label>'
							. '<input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ?? '' ) . '" size="30" autocomplete="email" />'
							. '</div>',
						'url'    => '<div class="post-comments__field">'
							. '<label for="url">seu blog ou site pessoal <span class="post-comments__hint">(opcional)</span></label>'
							. '<input id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ?? '' ) . '" size="30" placeholder="ex.: viniciuscodes.com.br" autocomplete="url" />'
							. '</div>',
					),
				)
			);
			?>
		</div>
	<?php else : ?>
		<p class="post-comments__closed">os comentários para este texto estão encerrados.</p>
	<?php endif; ?>
</section>
