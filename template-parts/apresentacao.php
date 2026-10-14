<?php
/**
 * A apresentação da home: retrato, frase de abertura e dois parágrafos.
 *
 * Ela morava solta no `index.php`, e isso trazia um problema que apareceu na
 * prática: o `index.php` é o arquivo de último recurso da hierarquia, então a
 * apresentação aparecia também em qualquer endereço sem template próprio. Foi
 * preciso criar um `404.php` para o visitante que errava um endereço parar de
 * ver o retrato no lugar do aviso.
 *
 * Como parte de template, quem decide se ela aparece é o `index.php`
 * (`is_home()`), e o arquivo de fallback fica com uma responsabilidade só: a lista.
 *
 * O `#temas` do último parágrafo aponta para a seção de temas da home, que
 * vive no `index.php` — as duas pontas são a mesma home, e é por isso que a
 * âncora funciona.
 *
 * @package ViniciusCodes
 */

/*
 * O retrato é o mesmo do cabeçalho e vem da biblioteca pelo nome do arquivo
 * original. `wp_get_attachment_image()` no lugar de um <img> escrito à mão
 * porque ele traz largura, altura e srcset: a foto é grande e é a primeira coisa
 * que o leitor vê, então ela não pode empurrar o texto enquanto carrega nem
 * chegar em tamanho maior do que a tela precisa.
 */
$vc_retrato_id = attachment_url_to_postid( vc_midia( 'vinicius-profile.webp' ) );
?>
<section class="hero">
	<div class="hero__intro">
		<?php if ( $vc_retrato_id ) : ?>
			<div class="hero__photo-wrap">
				<?php
				echo wp_get_attachment_image(
					$vc_retrato_id,
					'medium',
					false,
					array(
						'class' => 'hero__photo',
						'alt'   => 'Vinicius Oliveira, sorrindo, ao ar livre',
					)
				);
				?>
			</div>
		<?php endif; ?>

		<div class="hero__text">
			<h1 class="hero__title">escrevo sobre tecnologia, viagens e AuDHD.</h1>

			<p class="hero__lede">
				natural de Guaraíta (interior de Goiás) e hoje em Curitiba, sou formado em marketing
				e atuo como PO de Dados. LGBT e AuDHD (autista e TDAH), sou apaixonado por café,
				pela estrada e pelo meu namorado.
			</p>

			<p class="hero__lede">
				já morei em Manaus e Porto Velho, fiz um mochilão de 6 meses pelo Brasil e conheço
				mais da metade dos estados do país. por aqui, compartilho minha trajetória em dados,
				projetos pessoais e os aprendizados de viver com curiosidade e propósito.
			</p>

			<p class="hero__lede hero__lede--links">
				leia o <a href="<?php echo esc_url( home_url( '/manifesto/' ) ); ?>">manifesto</a>,
				explore os temas na barra lateral ou veja as publicações recentes abaixo:
			</p>
		</div>
	</div>
</section>
