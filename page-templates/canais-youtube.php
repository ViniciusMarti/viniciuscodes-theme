<?php
/**
 * Template Name: Canais do YouTube
 *
 * Página `/canais/` ou `/youtube/`: canais do YouTube recomendados e acompanhados,
 * organizados por categorias e temas macro.
 *
 * @package ViniciusCodes
 */

wp_enqueue_style(
	'vc-canais-youtube',
	get_theme_file_uri( 'assets/canais-youtube.css' ),
	array( 'vc-global' ),
	(string) filemtime( get_theme_file_path( 'assets/canais-youtube.css' ) )
);

$vc_categorias_canais = array(
	'tecnologia e ciência' => array(
		'descricao' => 'programação, hardware, experimentos e divulgação científica.',
		'icone'     => 'terminal',
		'canais'    => array(
			array(
				'nome'   => 'Coisa de Nerd',
				'handle' => '@coisadenerd',
				'url'    => 'https://www.youtube.com/@coisadenerd',
				'foto'   => 'https://yt3.ggpht.com/s8Ye13IN7ZbOi4HrMKAr-iN0JCHI1EJxDYLiRByRT96-K9Tz6S3PYTNjRl7D3BB9C_TTl5vY2A=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Manual do Mundo',
				'handle' => '@manualdomundo',
				'url'    => 'https://www.youtube.com/@manualdomundo',
				'foto'   => 'https://yt3.ggpht.com/eeXFm7_mIItuMYmrE9HRFjtwjXEG6Qvs7yJTZBdHmNyFwbi1v28gDd3tQ9JjzsjE1lSTOtb_=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Tech Isma',
				'handle' => '@techisma',
				'url'    => 'https://www.youtube.com/@techisma',
				'foto'   => 'https://yt3.ggpht.com/gz8mdRSxHx5gHIyj4UdC2OcoFM745mnlURrPf_zh-ySxynPUihhH6ZWhvso23jiB8hJcSFYK0g=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Técnico Sincero',
				'handle' => '@Tecnico.Sincero',
				'url'    => 'https://www.youtube.com/@Tecnico.Sincero',
				'foto'   => 'https://yt3.ggpht.com/po9c5uow5pFXSjj-W_5B3QP0zg4axEOYlFvbXzaNUYHv3w3AjP455u2x2dGrvS4CxS6879zn=s88-c-k-c0x00ffffff-no-rj',
			),
		),
	),
	'cinema e audiovisual' => array(
		'descricao' => 'crítica de filmes, ensaios em vídeo, narrativas e fotografia analógica.',
		'icone'     => 'film',
		'canais'    => array(
			array(
				'nome'   => 'André Miranda',
				'handle' => '@andremiranda.youtube',
				'url'    => 'https://www.youtube.com/@andremiranda.youtube',
				'foto'   => 'https://yt3.ggpht.com/wKzn5gr-x1e2abYlX9Pc0y4V0ndgG2pgYO06y6pD64wenc1Ngu7hRBAXD1NIvzDXihyNvZtn4w=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Isabela Boscov',
				'handle' => '@IsabelaBoscov',
				'url'    => 'https://www.youtube.com/@IsabelaBoscov',
				'foto'   => 'https://yt3.ggpht.com/ytc/AIdro_kLadyntPykzhPUAXJ5MxtZDFIh0wCUPgDRssxNCv4Ekg=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'PH Santos',
				'handle' => '@phsantos',
				'url'    => 'https://www.youtube.com/@phsantos',
				'foto'   => 'https://yt3.ggpht.com/rrVTZkfRPfhfcqC6kqW3sIjRb1Pw6yolldkVL7ptZcr7Ue-6OE6EEm6YZaqjMxkXuQ6IrZthIbg=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'REVELANDO',
				'handle' => '@revelando_br',
				'url'    => 'https://www.youtube.com/@revelando_br',
				'foto'   => 'https://yt3.ggpht.com/TrsYx14wnoycIhFsId3F7DQ2p7ENHTy8fWQ070t8oxhULe0HWhsjprRtgCV0AVRVa0V1PWHJ=s88-c-k-c0x00ffffff-no-rj',
			),
		),
	),
	'viagens e lugares' => array(
		'descricao' => 'expedições, culturas pelo mundo e roteiros pé no chão.',
		'icone'     => 'plane',
		'canais'    => array(
			array(
				'nome'   => 'Japão Nosso De Cada Dia',
				'handle' => '@japaonossodecadadia',
				'url'    => 'https://www.youtube.com/@japaonossodecadadia',
				'foto'   => 'https://yt3.ggpht.com/f4CWXI0wEESiagcqnYXMImoB6bhOo6uNhZqZxt6HsXvXWFbiSADXkiVvdsmwy8hZVOIsGdDH=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Par de Malas',
				'handle' => '@PardeMalas',
				'url'    => 'https://www.youtube.com/@PardeMalas',
				'foto'   => 'https://yt3.ggpht.com/1GrLqN32SGJIHuAW1LjJHSj73tUym7AyhdneF6VgPf6uZss2oVuGyjJbT0DWpUgMED6B0D14vIs=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Vou na Janela',
				'handle' => '@VounaJanela',
				'url'    => 'https://www.youtube.com/@VounaJanela',
				'foto'   => 'https://yt3.ggpht.com/ytc/AIdro_lc6BGkVJNgRBUT483wXjy4WhBRdluT7MCxXPxuKBdP2oo=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Vou sem volta',
				'handle' => '@Vousemvolta',
				'url'    => 'https://www.youtube.com/@Vousemvolta',
				'foto'   => 'https://yt3.ggpht.com/USJvxhcIOXNyBKQSjftcG-xyI898LvsKey_3amWQop7eAD8EmbdrjbA-55Js9RPVW71VNan1zWA=s88-c-k-c0x00ffffff-no-rj',
			),
		),
	),
	'urbanismo e sustentabilidade' => array(
		'descricao' => 'cidades pensadas para pessoas, vida simples e reflexões sociais.',
		'icone'     => 'sprout',
		'canais'    => array(
			array(
				'nome'   => 'Eixo XYZ',
				'handle' => '@eixoxyz',
				'url'    => 'https://www.youtube.com/@eixoxyz',
				'foto'   => 'https://yt3.ggpht.com/_DFN9t1yF0s-o_VGNKQBY2cFnYGYntggW6bJDfZapA-4TmsXKQbPtp1AwjgZIFoN-e__iGpL=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Minha Pequena Revolução',
				'handle' => '@PequenaRevolucao',
				'url'    => 'https://www.youtube.com/@PequenaRevolucao',
				'foto'   => 'https://yt3.ggpht.com/ytc/AIdro_n89CKDn3YjRHopF7d8N1mICf5yqoAZCzaMIj9hyKs9T6M=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Not Just Bikes',
				'handle' => '@NotJustBikes',
				'url'    => 'https://www.youtube.com/@NotJustBikes',
				'foto'   => 'https://yt3.ggpht.com/ZFNLKDUS39d0W0x_0Zfe-8DAg1WCvnic23kQn4mWd-130HcPF0PbYUSAI1BnDkZt73wHEpQ3TA=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Nátaly Neri',
				'handle' => '@NatalyNeri',
				'url'    => 'https://www.youtube.com/@NatalyNeri',
				'foto'   => 'https://yt3.ggpht.com/FuT_g4ii1K-m86_h-4DaJODpFhG1sqbucQN9mSXm5Ld7373D1OdLFBqytvOBYtV1_7IW1QCTYw=s88-c-k-c0x00ffffff-no-rj',
			),
		),
	),
	'cultura, livros e ensaios' => array(
		'descricao' => 'literatura, retrospectivas históricas, cultura pop e música.',
		'icone'     => 'book',
		'canais'    => array(
			array(
				'nome'   => 'Arbóreo Literário',
				'handle' => '@ArboreoLiterario',
				'url'    => 'https://www.youtube.com/@ArboreoLiterario',
				'foto'   => 'https://yt3.ggpht.com/9piFPQ1t23LwPPEMQ7FSg8Bv3U96BqX96LZUhQMc5wAWytnayQelLZ6GoetluUxg3b4xv8Gn=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Canal Nostalgia',
				'handle' => '@nostalgia',
				'url'    => 'https://www.youtube.com/@nostalgia',
				'foto'   => 'https://yt3.ggpht.com/yUEPj5YEPD65hBdFCDkS9a96qKqotTYJTGTjQ35MjryezXQwfGyYHtJ4cMU3Z_X_DwTEfcuwZQ=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Felipe Herrera',
				'handle' => '@felipeherreraof',
				'url'    => 'https://www.youtube.com/@felipeherreraof',
				'foto'   => 'https://yt3.ggpht.com/ytc/AIdro_mE6v_uL3GK8wTT-YGsuXU7MJ-BlyrBMhRDxypG3sM3CnA=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Jão',
				'handle' => '@JAO',
				'url'    => 'https://www.youtube.com/@JAO',
				'foto'   => 'https://yt3.ggpht.com/E8Nlmql72mTH_YFii7MwKZ-od2aDrDtfiv6P48vq3Bxu3wJuLgxK2Eq0KKtTVLaTxNEwLFBoGw=s88-c-k-c0x00ffffff-no-rj',
			),
		),
	),
	'cotidiano e vlogs' => array(
		'descricao' => 'rotina, vida real, conversas e reflexões diárias.',
		'icone'     => 'coffee',
		'canais'    => array(
			array(
				'nome'   => 'Cadê a chave?',
				'handle' => '@cadeachave',
				'url'    => 'https://www.youtube.com/@cadeachave',
				'foto'   => 'https://yt3.ggpht.com/ytc/AIdro_mAInGXZFTrSskkges8ao7XCcqNspGf0R3WjOKw5We5XTo=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Lincoln Lohan',
				'handle' => '@olincolnlohan',
				'url'    => 'https://www.youtube.com/@olincolnlohan',
				'foto'   => 'https://yt3.ggpht.com/ibTfwkGWRE4sdmVfY2L_y5TP0dOe6lQJe4b0U01zm1GTRhgfarBnUCBTYyn5PgB-vuyQQ-rByQ=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Matheus Menucci',
				'handle' => '@menuccimatheus',
				'url'    => 'https://www.youtube.com/@menuccimatheus',
				'foto'   => 'https://yt3.ggpht.com/70ZPupeNBy6oFWokZPzOhL5IZQ3xLaSt6pudXwkRsW9Ovel2JSDN0zeji0FII0fVYALjz3OKQbI=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Tito Melo',
				'handle' => '@oTitoMelo',
				'url'    => 'https://www.youtube.com/@oTitoMelo',
				'foto'   => 'https://yt3.ggpht.com/VLTEqGVYJCE28rii9mEen_-G8_pOwWkCahL41zoJsFIFm-byVDAwTSq-wfuyCB5s4EtYT0e-Uw=s88-c-k-c0x00ffffff-no-rj',
			),
		),
	),
	'humor e entretenimento' => array(
		'descricao' => 'comédia, cortes de conversas e bate-papo descontraído.',
		'icone'     => 'sparkles',
		'canais'    => array(
			array(
				'nome'   => 'CORTES - Leon e Nilce [Oficial]',
				'handle' => '@cortes-leonenilceoficial4101',
				'url'    => 'https://www.youtube.com/@cortes-leonenilceoficial4101',
				'foto'   => 'https://yt3.ggpht.com/AAneRieW_zyFy-84aHaczmh2I0zOAxXLRUB8wJ697lLvaRN7qQo-3AK9MQ7BYMkf3-yRaqOGtQ=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Matando Matheus a Grito',
				'handle' => '@matandomatheus',
				'url'    => 'https://www.youtube.com/@matandomatheus',
				'foto'   => 'https://yt3.ggpht.com/NUw1kN1YzDGFcnI05rialNiIoBDNxXq6n-CYPRCJnchiw61-JRqn6nW2LRBtruxKtYk2I2c_Tg=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Matheus e Guigo Show',
				'handle' => '@matheuseguigoshow',
				'url'    => 'https://www.youtube.com/@matheuseguigoshow',
				'foto'   => 'https://yt3.ggpht.com/q_eXvUAYzf9ZKREII5qgBM1U0Pd7QO2Egc9tmXSc-uq7t3Me0jbrxr7Rno1MlxwNzyII0u6u3d4=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Ramon e Victor',
				'handle' => '@ramonevictor',
				'url'    => 'https://www.youtube.com/@ramonevictor',
				'foto'   => 'https://yt3.ggpht.com/cndvshVkq0xGiLEffpFBtZyPeBDD-liZrg3PzFuCP4ml9ganNuHW67qODmWH4amS9BHqWe-_3A=s88-c-k-c0x00ffffff-no-rj',
			),
		),
	),
	'diy, culinária e casa' => array(
		'descricao' => 'marcenaria, receitas fáceis e dicas para o lar.',
		'icone'     => 'package',
		'canais'    => array(
			array(
				'nome'   => 'Almanaque SOS',
				'handle' => '@AlmanaqueSOS',
				'url'    => 'https://www.youtube.com/@AlmanaqueSOS',
				'foto'   => 'https://yt3.ggpht.com/JXcew5m_xDHARHy5wXcYfD0SiCvo4ZBtQu7WsjPyxJF8tdQ9Ur3tcTt0BQDsmG46S6Qiybu0=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Almanaque SOS Receitinhas',
				'handle' => '@SOSReceitinhas',
				'url'    => 'https://www.youtube.com/@SOSReceitinhas',
				'foto'   => 'https://yt3.ggpht.com/5QIUYhCUzP92sl4ETC_1nQx54Um7Aqu1JMt-SN8sXGT2VhOL46hMQFBvfi7Z2Nnx7StkjdpQfw=s88-c-k-c0x00ffffff-no-rj',
			),
			array(
				'nome'   => 'Que nem marceneiro',
				'handle' => '@quenem_marceneiro',
				'url'    => 'https://www.youtube.com/@quenem_marceneiro',
				'foto'   => 'https://yt3.ggpht.com/-0t-H9cLUrV2j2LqPrI7Dwq0RuxoBw-yRe7qV5DuJfQQYhlJsEu5mNJTNDDlO771FXlxKwFDRg=s88-c-k-c0x00ffffff-no-rj',
			),
		),
	),
);

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="article article--canais" data-page="canais">
		<header class="canais__header">
			<h1 class="article__title"><?php the_title(); ?></h1>
			<p class="canais__intro">
				os canais, criadores e formatos que acompanho com frequência no youtube, organizados por afinidade e temas de interesse.
			</p>
		</header>

		<div class="canais__categorias">
			<?php foreach ( $vc_categorias_canais as $vc_cat_nome => $vc_cat_dados ) : ?>
				<section class="canais__secao" id="<?php echo esc_attr( sanitize_title( $vc_cat_nome ) ); ?>">
					<div class="canais__secao-cabecalho">
						<div class="canais__secao-tit-wrap">
							<h2 class="canais__secao-titulo">
								<span class="canais__secao-icone" aria-hidden="true">
									<?php echo vc_icone( $vc_cat_dados['icone'], 18 ); ?>
								</span>
								<span><?php echo esc_html( $vc_cat_nome ); ?></span>
							</h2>
							<span class="canais__secao-qtd"><?php echo esc_html( count( $vc_cat_dados['canais'] ) ); ?></span>
						</div>
						<?php if ( ! empty( $vc_cat_dados['descricao'] ) ) : ?>
							<p class="canais__secao-desc"><?php echo esc_html( $vc_cat_dados['descricao'] ); ?></p>
						<?php endif; ?>
					</div>

					<ul class="canais__lista">
						<?php foreach ( $vc_cat_dados['canais'] as $vc_canal ) : ?>
							<li class="canal-card">
								<a
									class="canal-card__link"
									href="<?php echo esc_url( $vc_canal['url'] ); ?>"
									target="_blank"
									rel="noopener noreferrer"
								>
									<div class="canal-card__avatar-wrap">
										<img
											class="canal-card__avatar"
											src="<?php echo esc_url( $vc_canal['foto'] ); ?>"
											alt="<?php echo esc_attr( $vc_canal['nome'] ); ?>"
											width="44"
											height="44"
											loading="lazy"
										/>
									</div>

									<div class="canal-card__info">
										<h3 class="canal-card__nome"><?php echo esc_html( $vc_canal['nome'] ); ?></h3>
										<p class="canal-card__handle"><?php echo esc_html( $vc_canal['handle'] ); ?></p>
									</div>

									<span class="canal-card__externo" aria-hidden="true">
										<?php echo vc_icone( 'external-link', 14 ); ?>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>
		</div>

		<?php
		$vc_conteudo = get_the_content();
		if ( ! empty( trim( $vc_conteudo ) ) ) :
			?>
			<div class="prose canais__prose">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
