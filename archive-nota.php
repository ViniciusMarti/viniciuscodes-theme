<?php
/**
 * A linha do tempo: `/notas/`.
 *
 * CADA NOTA É UM CARTÃO, E ISSO É A DIFERENÇA DESTE ARQUIVO.
 *
 * Antes daqui a lista era a do tema — `.post-list` e `.post-item` — e o que
 * mudava era só o miolo: o conteúdo no lugar do `<h2>`. O resultado não se
 * sustentou, e o motivo é estrutural, não de gosto: o `.post-item` do
 * `global.css` foi desenhado para um texto COM título. É o título que faz o
 * olho registrar "começa um item novo"; a meta pequena e apagada acima dele é
 * só legenda. Trocado o `<h2>` por um parágrafo, o item passou a ter a
 * anatomia de um parágrafo — meta miúda, texto, meta miúda, texto — separado
 * do próximo por um fio de 1px. Numa coluna de 620px isso lê como um texto
 * corrido com divisórias, que é exatamente o que uma nota não é.
 *
 * O que este arquivo passa a fazer: cada nota ganha SUPERFÍCIE própria
 * (fundo `--paper-card`, borda, raio) e um VÃO real entre ela e a vizinha,
 * além de uma ÂNCORA no topo — o avatar. As três coisas juntas dizem "isto é
 * uma unidade" antes de qualquer palavra ser lida.
 *
 * A PAGINAÇÃO É A DO SERVIDOR, e não a do `filtros.js`.
 *
 * A lista de `/posts/` pagina no navegador porque o filtro por tema precisa
 * ver a lista inteira para saber quantas sobraram. Aqui não há filtro, então
 * a paginação é a do WordPress — a mesma que o `archive.php` do tema usa, com
 * os endereços que o núcleo calcula (`/notas/page/2/`).
 *
 * MICROFORMATOS (IndieWeb).
 *
 * Cada nota é um `h-entry`, o texto é o `e-content`, a data vai num
 * `dt-published` com ISO no `<time>` e o link do horário carrega o `u-url`.
 *
 * NÃO HÁ `p-name`, e é a decisão mais importante deste arquivo: o nome de uma
 * nota é aquilo que ela diz. Um `p-name` com a data — que é o que sobraria,
 * já que não há título — faria os parsers da IndieWeb lerem "4 de outubro de
 * 2026" como o título da nota, e um leitor federado mostraria a data duas
 * vezes em vez do texto.
 *
 * @package ViniciusCodes
 */

get_header();

$vc_post_type = defined( 'VC_NOTA' ) ? VC_NOTA : 'nota';
$vc_objeto    = get_post_type_object( $vc_post_type );

/*
 * O AVATAR VEM DO PRÓPRIO SITE, e não do Gravatar.
 *
 * O `comments.php` já recusa avatar externo (`'avatar_size' => 0`, "sem
 * avatares externos por privacidade e leveza") e a recusa continua valendo: o
 * que entra aqui é o mesmo arquivo que o cabeçalho já carrega em toda página,
 * achado pelo mesmo `vc_midia()`. Não há requisição para terceiros, não há
 * hash de e-mail saindo do servidor, e a imagem já está no cache do navegador
 * de quem navegou pelo site — o custo de rede é zero.
 *
 * O `vc_midia()` devolve string vazia quando o anexo não existe, e por isso o
 * `<img>` é condicional: sem a guarda, uma instalação sem a imagem mostraria o
 * ícone de imagem quebrada dez vezes na mesma página.
 */
$vc_avatar = vc_midia( 'vinicius-profile.webp' );
?>
<header class="term-header">
	<h1 class="term-header__name">
		<?php echo esc_html( mb_strtolower( (string) ( $vc_objeto instanceof WP_Post_Type ? $vc_objeto->labels->name : __( 'Notas', 'viniciuscodes' ) ), 'UTF-8' ) ); ?>
	</h1>

	<p class="term-header__desc">
		o que eu escreveria numa rede social, mas escrito aqui.
	</p>
</header>

<?php if ( have_posts() ) : ?>
	<ul class="post-list post-list--notas">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<li id="nota-<?php the_ID(); ?>" <?php post_class( 'post-item post-item--nota h-entry' ); ?> data-temas="<?php echo esc_attr( vc_nota_slugs_de_tema() ); ?>">
				<?php
				/*
				 * O CARTÃO MORA EM `template-parts/card-nota.php`, e é o mesmo que a
				 * home usa. Aqui ficam só o `<li>` e o `data-temas`, que são do arquivo.
				 */
				get_template_part( 'template-parts/card', 'nota' );
				?>
			</li>
			<?php
		endwhile;
		?>
	</ul>

	<?php
	/*
	 * A paginação oficial do arquivo, a mesma do `archive.php` do tema: o
	 * arquivo usa a consulta principal e o WordPress já a pagina.
	 */
	vc_paginacao(
		(int) $GLOBALS['wp_query']->max_num_pages,
		max( 1, (int) get_query_var( 'paged' ) )
	);
	?>
<?php else : ?>
	<p class="empty"><?php esc_html_e( 'ainda não há notas por aqui.', 'viniciuscodes' ); ?></p>
<?php endif; ?>

<?php
get_footer();
