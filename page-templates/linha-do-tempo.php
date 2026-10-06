<?php
/**
 * Template Name: Linha do tempo (viagens e moradias)
 *
 * A página `/viagens/`: onde eu morei, por onde eu passei, o que eu escrevi e o
 * que eu fotografei em cada lugar.
 *
 * UMA LINHA POR LUGAR, e o formato é o mesmo do acervo, de propósito: quem já
 * mantém 224 linhas em `/biblioteca/` não precisa aprender uma segunda gramática
 * para escrever sete cidades.
 *
 *     1999 | Guaraíta, Goiás | moradia
 *     2022-02 | Sorocaba, São Paulo | viagem | fotos: frutuba-sorocaba.jpg
 *     2022-04 | Vitória, Espírito Santo | viagem | fui a trabalho
 *     Recife
 *
 * A DATA É O QUE DECIDE ONDE A LINHA CAI, e é a única regra deste arquivo:
 *
 *   COM data, a linha entra na LINHA DO TEMPO, em ordem cronológica.
 *   SEM data, a linha entra no bloco de baixo, em ordem alfabética.
 *
 * Não há uma terceira possibilidade, e não há campo dizendo qual é qual. A lista
 * do Google Fotos chegou quase toda sem data, e inventar um ano para cada lugar
 * seria pior do que dizer que ele não tem: uma data errada parece informação.
 *
 * AS TRÊS PRIMEIRAS POSIÇÕES DA LINHA COM DATA SÃO DATA, LUGAR E TIPO. O mês é
 * opcional: `2015` é o ano inteiro, e `2015-01` é janeiro dele. Só o ano serve
 * para uma mudança de casa, que não tem dia; o mês serve para uma viagem, que tem.
 *
 * O TIPO É UMA PALAVRA, e ela muda o desenho: `moradia` é o ponto cheio, e
 * `viagem` é o ponto vazado. Valem `morei`, `moro`, `casa` e `morar` para a
 * primeira, e `viajei`, `passeio` e `viagens` para a segunda. Uma palavra que não
 * esteja na lista vira texto, e a linha vira viagem.
 *
 * ---------------------------------------------------------------------------
 * AS FOTOS DE CADA LUGAR
 * ---------------------------------------------------------------------------
 *
 * O CAMPO `fotos:` RECEBE O NOME DO ARQUIVO, e aceita mais de um separado por
 * vírgula. Aceita também o número do anexo, para quem preferir copiar o
 * endereço da biblioteca a lembrar o nome do arquivo.
 *
 *     fotos: frutuba-sorocaba.jpg
 *     fotos: praia.jpg, morro.jpg, cafe.jpg
 *     fotos: 884
 *
 * A PROCURA É PELO NOME DO ARQUIVO e não pelo endereço inteiro: quem escreve a
 * linha escreve `frutuba-sorocaba.jpg`, e não
 * `https://viniciuscodes.com.br/wp-content/uploads/2026/10/frutuba-sorocaba.jpg`.
 * O endereço mente no dia em que a pasta do ano muda; o nome do arquivo não.
 * Escrever sem a extensão também funciona, e é o que salva quem esqueceu como o
 * arquivo foi salvo.
 *
 * A LEGENDA DA POLAROIDE É A LEGENDA DA MÍDIA, e não um campo desta linha. Ela
 * se escreve uma vez, na biblioteca de mídia, ao lado do `alt` — que é onde o
 * WordPress já guarda as duas —, e vale em todo lugar em que a foto aparecer.
 * Sem legenda escrita, a tarja branca de baixo fica vazia, que é como uma
 * polaroide sem frase é de verdade.
 *
 * UMA FOTO QUE NÃO EXISTE NÃO VIRA QUADRO VAZIO: o nome que não casa com nenhum
 * anexo é descartado em silêncio, e a linha segue sem foto. Um retângulo
 * quebrado no meio da linha do tempo é pior do que a falta dele.
 *
 * ---------------------------------------------------------------------------
 * O QUE AS DUAS COISAS TÊM EM COMUM
 * ---------------------------------------------------------------------------
 *
 * UM LUGAR SEM TEXTO E SEM FOTO NÃO MOSTRA NADA, e é o estado de quase todos
 * hoje: nada de "nenhum texto ainda", nada de espaço reservado. A linha do tempo
 * e a lista continuam com o tamanho que têm, e crescem quando houver o que
 * crescer.
 *
 * A BUSCA DAS FOTOS É FEITA UMA VEZ SÓ para todos os lugares da página: os anexos
 * são lidos numa consulta e guardados em memória. Quarenta lugares com uma
 * consulta cada seriam quarenta consultas para achar o mesmo punhado de imagens.
 *
 * A ORDEM NO EDITOR NÃO IMPORTA EM NENHUM DOS DOIS BLOCOS: a linha do tempo é
 * reordenada pela data, DO MAIS RECENTE PARA O MAIS ANTIGO, e a lista de lugares
 * por ordem alfabética, ignorando acento. Quem escreve não precisa decidir onde
 * a linha vai.
 *
 * O "ATUAL" É DEDUZIDO, e não escrito. A moradia MAIS RECENTE ganha a marca
 * sozinha, então no dia em que a próxima mudança for cadastrada a marca anda
 * para ela sem ninguém precisar apagar a antiga. A palavra do tipo também muda:
 * a moradia atual diz "moro", e as outras dizem "morei".
 *
 * A LINHA DO TEMPO É UMA LISTA ORDENADA (`<ol>`), e não uma pilha de `<div>`: a
 * ordem é o conteúdo desta página, e é ela que o navegador e o leitor de tela
 * precisam anunciar. O bloco de baixo é uma `<ul>` comum, porque ali a ordem não
 * significa nada. O desenho da linha, do ponto e da polaroide é do
 * `assets/tempo.css`.
 *
 * @package ViniciusCodes
 */

wp_enqueue_style(
	'vc-tempo',
	get_theme_file_uri( 'assets/tempo.css' ),
	array( 'vc-global' ),
	(string) filemtime( get_theme_file_path( 'assets/tempo.css' ) )
);

/*
 * O SCRIPT VEM DEPOIS DO CONTEÚDO (`true` no último argumento), e não no
 * `<head>`: ele só faz sentido depois que existem as polaroides na página, e
 * um script no `<head>` que espera o DOM é um script que precisa de um
 * tratador a mais para não quebrar.
 */
wp_enqueue_script(
	'vc-tempo',
	get_theme_file_uri( 'assets/tempo.js' ),
	array(),
	(string) filemtime( get_theme_file_path( 'assets/tempo.js' ) ),
	true
);

/**
 * As palavras de cada tipo, escritas como eu escrevo.
 *
 * O `moradia` e o `viagem` são os nomes canônicos, e o resto são apelidos: quem
 * escreve `morei` está dizendo a mesma coisa, e obrigá-la a lembrar da forma
 * exata é criar um erro que só aparece na tela.
 */
$vc_tipos_tempo = array(
	'moradia' => array( 'moradia', 'morei', 'moro', 'morar', 'casa' ),
	'viagem'  => array( 'viagem', 'viajei', 'viajar', 'viagens', 'passeio', 'trip' ),
);

/**
 * Separa os campos com nome do texto solto.
 *
 * `fotos: praia.jpg` é campo. `fui a trabalho` é texto. A regra é a mesma do
 * acervo, e o dois-pontos é o que decide: um pedaço que tem `nome:` na frente vira
 * campo, e o resto vira o texto que aparece embaixo do lugar.
 */
$vc_separar_campos = static function ( array $partes ): array {
	$campos = array();
	$livres = array();

	foreach ( $partes as $parte ) {
		if ( preg_match( '#^([A-Za-zÀ-ÿ]+)\s*:\s*(.+)$#u', $parte, $m ) ) {
			$campos[ mb_strtolower( remove_accents( $m[1] ), 'UTF-8' ) ] = trim( $m[2] );
			continue;
		}

		$livres[] = $parte;
	}

	return array( 'campos' => $campos, 'livres' => $livres );
};

/**
 * Um anexo, pelo nome do arquivo ou pelo número.
 *
 * O MAPA SAI DE `_wp_attached_file` porque é lá que está o caminho real do
 * arquivo: o nome sozinho não diz em que pasta de ano e mês ele foi guardado, e
 * montar o endereço à mão quebraria na virada do ano. O mapa guarda as duas
 * formas, com e sem extensão, e é o que faz `fotos: praia.jpg` e `fotos: praia`
 * acharem o mesmo anexo.
 */
$vc_imagem_do_lugar = static function ( string $nome ): array {
	static $vc_mapa = null;

	if ( null === $vc_mapa ) {
		global $wpdb;

		$vc_mapa = array();

		$vc_linhas = $wpdb->get_results(
			"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file'"
		);

		foreach ( (array) $vc_linhas as $vc_l ) {
			$vc_arquivo = strtolower( basename( (string) $vc_l->meta_value ) );

			$vc_mapa[ $vc_arquivo ] = (int) $vc_l->post_id;

			$vc_sem = strtolower( (string) pathinfo( $vc_arquivo, PATHINFO_FILENAME ) );

			if ( ! isset( $vc_mapa[ $vc_sem ] ) ) {
				$vc_mapa[ $vc_sem ] = (int) $vc_l->post_id;
			}
		}
	}

	$vc_nome = trim( $nome );

	if ( '' === $vc_nome ) {
		return array();
	}

	$vc_id = 0;

	if ( ctype_digit( $vc_nome ) ) {
		$vc_id = (int) $vc_nome;
	} else {
		$vc_chave = strtolower( basename( $vc_nome ) );

		if ( isset( $vc_mapa[ $vc_chave ] ) ) {
			$vc_id = (int) $vc_mapa[ $vc_chave ];
		} else {
			$vc_sem = strtolower( (string) pathinfo( $vc_chave, PATHINFO_FILENAME ) );

			if ( isset( $vc_mapa[ $vc_sem ] ) ) {
				$vc_id = (int) $vc_mapa[ $vc_sem ];
			}
		}
	}

	if ( $vc_id < 1 ) {
		return array();
	}

	$vc_grande = wp_get_attachment_image_url( $vc_id, 'full' );

	if ( ! $vc_grande ) {
		return array();
	}

	return array(
		'id'      => $vc_id,
		'grande'  => (string) $vc_grande,
		'legenda' => (string) wp_get_attachment_caption( $vc_id ),
	);
};

/** As fotos de um lugar, a partir do campo `fotos:`. */
$vc_fotos_do_campo = static function ( string $campo ) use ( $vc_imagem_do_lugar ): array {
	$fotos = array();

	foreach ( array_map( 'trim', explode( ',', $campo ) ) as $vc_nome ) {
		if ( '' === $vc_nome ) {
			continue;
		}

		$vc_img = $vc_imagem_do_lugar( $vc_nome );

		if ( $vc_img ) {
			$fotos[] = $vc_img;
		}
	}

	return $fotos;
};

/**
 * Lê as linhas e devolve os dois blocos.
 *
 * Devolve `array( 'tempo' => array(), 'lugares' => array() )`. O primeiro é a
 * linha do tempo, já ordenada por data; o segundo é a lista de nomes, já em
 * ordem alfabética. Cada item carrega as `fotos` que são dele.
 */
$vc_ler_tempo = static function ( string $conteudo ) use ( $vc_tipos_tempo, $vc_separar_campos, $vc_fotos_do_campo ): array {
	$vc_tempo   = array();
	$vc_lugares = array();

	if ( ! preg_match_all( '#<li\b[^>]*>(.*?)</li>#is', $conteudo, $vc_achados ) ) {
		return array( 'tempo' => $vc_tempo, 'lugares' => $vc_lugares );
	}

	/** O campo de fotos aceita os dois nomes, no singular e no plural. */
	$vc_campo_fotos = static fn( array $campos ): string => (string) ( $campos['fotos'] ?? $campos['foto'] ?? '' );

	foreach ( $vc_achados[1] as $vc_bruto ) {
		/*
		 * A decodificação vem antes do `esc_html` da saída: o editor grava `&`
		 * como `&amp;`, e sem desfazer isso um lugar com `&` apareceria escrito
		 * `&amp;` na tela.
		 */
		$vc_texto = html_entity_decode( wp_strip_all_tags( $vc_bruto ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$vc_texto = trim( preg_replace( '/\s+/u', ' ', $vc_texto ) );

		if ( '' === $vc_texto ) {
			continue;
		}

		/*
		 * A DATA ABRE A LINHA, e é ela que separa os dois blocos. O teste pede o
		 * ano de quatro dígitos no começo, seguido de barra ou fim de linha: sem
		 * essa âncora, um lugar como `2000 Ilhas` entraria na linha do tempo.
		 */
		$vc_tem_data = (bool) preg_match( '#^(\d{4})(?:\D+(\d{1,2}))?\s*(?:\||$)#', $vc_texto, $vc_d );
		$vc_partes   = array_map( 'trim', explode( '|', $vc_texto ) );

		if ( ! $vc_tem_data ) {
			/*
			 * SEM DATA, A LINHA É O NOME DO LUGAR, e tudo o que não for campo. É
			 * o que permite escrever `São Francisco do Sul` sem escapar de nada.
			 */
			$vc_sep  = $vc_separar_campos( $vc_partes );
			$vc_nome = trim( (string) ( $vc_sep['livres'][0] ?? '' ) );

			if ( '' === $vc_nome ) {
				continue;
			}

			$vc_lugares[] = array(
				'nome'   => $vc_nome,
				'fotos'  => $vc_fotos_do_campo( $vc_campo_fotos( $vc_sep['campos'] ) ),
			);

			continue;
		}

		$vc_ano = (int) $vc_d[1];
		$vc_mes = isset( $vc_d[2] ) ? (int) $vc_d[2] : 0;

		if ( $vc_ano < 1900 || $vc_ano > 2200 ) {
			continue;
		}

		$vc_lugar = trim( (string) ( $vc_partes[1] ?? '' ) );

		if ( '' === $vc_lugar ) {
			continue;
		}

		/*
		 * O tipo é a posição seguinte, e só vale quando ela for uma das palavras
		 * conhecidas: escrita qualquer outra coisa ali, o pedaço volta para o
		 * texto, e a linha vira viagem.
		 */
		$vc_resto = array_slice( $vc_partes, 2 );
		$vc_tipo  = 'viagem';
		$vc_marca = mb_strtolower( remove_accents( (string) ( $vc_resto[0] ?? '' ) ), 'UTF-8' );

		foreach ( $vc_tipos_tempo as $vc_slug_tipo => $vc_apelidos ) {
			if ( in_array( $vc_marca, $vc_apelidos, true ) ) {
				$vc_tipo = $vc_slug_tipo;
				array_shift( $vc_resto );
				break;
			}
		}

		$vc_campos = $vc_separar_campos( $vc_resto );

		$vc_tempo[] = array(
			'ano'    => $vc_ano,
			'mes'    => $vc_mes,
			'lugar'  => $vc_lugar,
			'tipo'   => $vc_tipo,
			'texto'  => trim( implode( ' | ', $vc_campos['livres'] ) ),
			'fotos'  => $vc_fotos_do_campo( $vc_campo_fotos( $vc_campos['campos'] ) ),
		);
	}

	/*
	 * DO MAIS RECENTE PARA O MAIS ANTIGO, e a ordem de escrita decide o empate
	 * dentro do mesmo mês. O `usort` do PHP 8 é ESTÁVEL: duas viagens de agosto de
	 * 2024 saem na ordem em que foram escritas, e não numa ordem sorteada a cada
	 * carregamento da página.
	 */
	usort(
		$vc_tempo,
		static fn( array $a, array $b ): int => ( $b['ano'] * 100 + $b['mes'] ) <=> ( $a['ano'] * 100 + $a['mes'] )
	);

	/*
	 * A ORDEM ALFABÉTICA IGNORA ACENTO, e é por isso que a comparação passa pelo
	 * `remove_accents()`: sem ele, `Iporá` cairia depois de `Itajaí`, porque o
	 * acento tem código maior que qualquer letra, e a lista pareceria ordenada
	 * por outro critério que não o que o olho usa.
	 */
	usort(
		$vc_lugares,
		static fn( array $a, array $b ): int => strcmp(
			strtoupper( remove_accents( $a['nome'] ) ),
			strtoupper( remove_accents( $b['nome'] ) )
		)
	);

	return array( 'tempo' => $vc_tempo, 'lugares' => $vc_lugares );
};

/**
 * Desenha as fotos de um lugar, em polaroide.
 *
 * O QUADRO BRANCO É DO CSS, e o `<figure>` é da marcação: uma foto com legenda é
 * exatamente o que a dupla `figure` e `figcaption` existe para dizer, e usar
 * `<div>` aqui perderia a ligação entre a imagem e a frase que a explica — que é
 * a única coisa que separa uma polaroide de um retângulo com moldura.
 *
 * O CLIQUE ABRE A FOTO NO TAMANHO ORIGINAL, em outra aba. Não há ampliação na
 * própria página, e a escolha é de peso: uma caixa de luz custaria um script a
 * mais e um estado a mais, e o navegador já sabe mostrar uma imagem sozinho.
 *
 * `loading="lazy"` em todas, inclusive na primeira: numa página com quarenta
 * lugares, a primeira foto pode estar muito abaixo da dobra, e o navegador
 * decide melhor do que eu quando ela entra.
 */
$vc_desenhar_fotos = static function ( array $fotos ): void {
	if ( ! $fotos ) {
		return;
	}
	?>
	<div class="tempo__fotos">
		<?php foreach ( $fotos as $vc_f ) : ?>
			<figure class="tempo__polaroide">
				<a
					class="tempo__polaroide-link"
					href="<?php echo esc_url( $vc_f['grande'] ); ?>"
					data-vc-foto="<?php echo esc_url( $vc_f['grande'] ); ?>"
					data-vc-legenda="<?php echo esc_attr( $vc_f['legenda'] ); ?>"
					target="_blank"
					rel="noopener"
				>
					<?php
					echo wp_get_attachment_image(
						(int) $vc_f['id'],
						'polaroide',
						false,
						array(
							'class'    => 'tempo__foto',
							'loading'  => 'lazy',
							'decoding' => 'async',
						)
					);
					?>
				</a>
				<figcaption class="tempo__polaroide-legenda"><?php echo esc_html( $vc_f['legenda'] ); ?></figcaption>
			</figure>
		<?php endforeach; ?>
	</div>
	<?php
};

get_header();

while ( have_posts() ) :
	the_post();

	$vc_conteudo = (string) get_the_content();
	$vc_lido     = $vc_ler_tempo( $vc_conteudo );
	$vc_itens    = $vc_lido['tempo'];
	$vc_lugares  = $vc_lido['lugares'];

	/*
	 * A ABERTURA É O CONTEÚDO SEM A LISTA. O acervo faz a mesma coisa, e pelo
	 * mesmo motivo: a abertura é prosa, a lista é dado, e misturar as duas na
	 * mesma saída daria dois ritmos na mesma caixa.
	 */
	$vc_abertura = trim( (string) preg_replace( '#<ul\b[^>]*>.*?</ul>#is', '', $vc_conteudo ) );

	/*
	 * A MORADIA MAIS RECENTE É ONDE EU ESTOU, e a conta é pelo MAIOR ANO, não
	 * pela última da lista.
	 *
	 * A DISTINÇÃO NÃO É TEÓRICA: desde que a linha do tempo passou a vir do mais
	 * novo para o mais antigo, "a última moradia da lista" é a MAIS VELHA, e a
	 * marca "atual" iria parar em Guaraíta, 1999.
	 */
	$vc_moradia_atual = -1;
	$vc_maior_data    = -1;

	foreach ( $vc_itens as $vc_i => $vc_item ) {
		if ( 'moradia' !== $vc_item['tipo'] ) {
			continue;
		}

		$vc_data_item = $vc_item['ano'] * 100 + $vc_item['mes'];

		if ( $vc_data_item > $vc_maior_data ) {
			$vc_maior_data    = $vc_data_item;
			$vc_moradia_atual = $vc_i;
		}
	}

	/*
	 * A CAIXA DE LUZ SÓ ENTRA NA PÁGINA QUE TEM FOTO. Sem esta conta, uma página sem
	 * polaroide carregaria o script e o `<dialog>` para nada.
	 */
	$vc_tem_fotos = false;

	foreach ( array_merge( $vc_itens, $vc_lugares ) as $vc_conta ) {
		if ( $vc_conta['fotos'] ) {
			$vc_tem_fotos = true;
			break;
		}
	}

	$vc_meses = array(
		1 => 'jan', 2 => 'fev', 3 => 'mar', 4 => 'abr', 5 => 'mai', 6 => 'jun',
		7 => 'jul', 8 => 'ago', 9 => 'set', 10 => 'out', 11 => 'nov', 12 => 'dez',
	);
	?>
	<article class="article article--tempo" data-page="tempo">
		<header class="article__header">
			<h1 class="article__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( '' !== $vc_abertura ) : ?>
			<div class="prose tempo__abertura"><?php echo wp_kses_post( $vc_abertura ); ?></div>
		<?php endif; ?>

		<?php if ( ! $vc_itens ) : ?>
			<p class="empty">ainda não há lugares com data nesta linha do tempo.</p>
		<?php else : ?>
			<ol class="tempo">
				<?php foreach ( $vc_itens as $vc_i => $vc_item ) : ?>
					<?php
					$vc_e_atual  = ( $vc_i === $vc_moradia_atual );
					$vc_data_iso = sprintf( '%04d', $vc_item['ano'] );

					if ( $vc_item['mes'] > 0 ) {
						$vc_data_iso .= sprintf( '-%02d', $vc_item['mes'] );
					}

					$vc_rotulo = (string) $vc_item['ano'];

					if ( $vc_item['mes'] > 0 && isset( $vc_meses[ $vc_item['mes'] ] ) ) {
						$vc_rotulo = $vc_meses[ $vc_item['mes'] ] . ' ' . $vc_item['ano'];
					}

					$vc_verbo = 'moradia' === $vc_item['tipo']
						? ( $vc_e_atual ? 'moro' : 'morei' )
						: 'viajei';
					?>
					<li class="tempo__item" data-tipo="<?php echo esc_attr( $vc_item['tipo'] ); ?>">
						<p class="tempo__quando">
							<time datetime="<?php echo esc_attr( $vc_data_iso ); ?>"><?php echo esc_html( $vc_rotulo ); ?></time>
							<span class="tempo__verbo"><?php echo esc_html( $vc_verbo ); ?></span>
						</p>

						<h2 class="tempo__lugar">
							<?php echo esc_html( $vc_item['lugar'] ); ?>
							<?php if ( $vc_e_atual && 'moradia' === $vc_item['tipo'] ) : ?>
								<span class="tempo__atual">atual</span>
							<?php endif; ?>
						</h2>

						<?php if ( '' !== $vc_item['texto'] ) : ?>
							<p class="tempo__texto"><?php echo esc_html( $vc_item['texto'] ); ?></p>
						<?php endif; ?>

						<?php $vc_desenhar_fotos( $vc_item['fotos'] ); ?>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<?php if ( $vc_lugares ) : ?>
			<?php
			/*
			 * O BLOCO DOS LUGARES SEM DATA. O título dele é do template, e não do
			 * conteúdo: ele descreve a REGRA que separa os dois blocos, e uma regra
			 * que mora no texto editável sai da página no primeiro dia em que
			 * alguém reescrever a frase.
			 */
			?>
			<section class="tempo__outros">
				<h2 class="section-title">outros lugares onde eu já estive</h2>
				<ul class="tempo__lugares">
					<?php foreach ( $vc_lugares as $vc_lugar ) : ?>
						<li class="tempo__lugar-item">
							<span class="tempo__lugar-nome"><?php echo esc_html( $vc_lugar['nome'] ); ?></span>
							<?php $vc_desenhar_fotos( $vc_lugar['fotos'] ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>
		<?php if ( $vc_tem_fotos ) : ?>
			<?php
			/*
			 * A CAIXA DE LUZ, UMA SÓ PARA A PÁGINA INTEIRA, e vazia até alguém clicar.
			 * O `<img>` sai sem `src` de propósito: com o endereço fixo aqui, as seis
			 * fotos grandes seriam baixadas por quem nunca clicasse em nenhuma.
			 */
			?>
			<dialog class="tempo__caixa" id="tempo-caixa" aria-label="<?php esc_attr_e( 'foto ampliada', 'viniciuscodes' ); ?>">
				<figure class="tempo__caixa-figura">
					<img class="tempo__caixa-foto" alt="" />
					<figcaption class="tempo__caixa-legenda"></figcaption>
				</figure>
				<button type="button" class="tempo__caixa-fechar" aria-label="<?php esc_attr_e( 'fechar', 'viniciuscodes' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			</dialog>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
